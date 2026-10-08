<?php
namespace App\Services;

use App\Core\Logger;
use App\Database\Database;
use App\Models\BlogArticle;
use App\Models\BlogComment;
use App\Support\Avatar;
use App\Support\FileCache;
use PDO;

/**
 * Comments and replies. One table, one-level threads (parent_id) with the exact
 * reply target kept in reply_to_id.
 *
 * Cache: one namespace per article ("blog-comments-{id}"), so a comment mutation
 * only drops that article's entries. Only the first page and the total are cached;
 * cursor pages always hit the database, so a client-supplied cursor can never
 * create cache files. Cached payloads hold no per-member data (no user ids, no
 * "is mine" flag): the page compares usernames at render time.
 */
final class BlogCommentService {
    public const MAX_LENGTH = 500;
    public const PAGE_SIZE = 20;
    public const INITIAL_REPLIES = 2;
    public const REPLY_BATCH = 10;

    private const TTL = 120;
    private const FLOOD_LIMIT = 10;       // comments per member per minute
    private const MAX_SUBTREE = 5000;

    public function __construct(
        private PDO $db,
        private BlogComment $comments,
        private BlogArticle $articles,
        private BlogBannedWordService $banned,
        private FileCache $cache
    ) {}

    public static function make(): self {
        $db = Database::connection();

        return new self($db, new BlogComment($db), new BlogArticle($db), BlogBannedWordService::make(),
            new FileCache(STORAGE_PATH . '/cache'));
    }

    public static function cacheNamespace(int $articleId): string {
        return 'blog-comments-' . $articleId;
    }

    // =========================================================================
    // READ
    // =========================================================================
    /** One page of top-level comments (newest first), each with its first replies and a reply count. */
    public function listForArticle(string $publicId, mixed $before): array {
        $articleId = $this->resolveArticle($publicId);
        $cursor = self::id($before);

        if ($articleId === null) {
            return $this->fail('That article is not available.', 404);
        }

        if ($cursor === false) {
            return $this->fail('Invalid cursor.');
        }

        return ['success' => true] + $this->page($articleId, $cursor)
            + ['total' => $this->total($articleId)];
    }

    /** The next batch of a thread's replies (oldest first), after the last reply the client holds. */
    public function replies(int $parentId, mixed $after): array {
        $cursor = self::id($after);

        if ($cursor === false) {
            return $this->fail('Invalid cursor.');
        }

        $parent = $this->comments->findById($parentId);

        if ($parent === false || $parent['parent_id'] !== null
            || !$this->articles->isPublic((int) $parent['article_id'])
        ) {
            return $this->fail('That comment no longer exists.', 404);
        }

        $rows = $this->comments->replies($parentId, $cursor, self::REPLY_BATCH + 1);
        $hasMore = count($rows) > self::REPLY_BATCH;
        $rows = array_slice($rows, 0, self::REPLY_BATCH);
        $last = $rows ? $rows[count($rows) - 1] : null;

        return [
            'success' => true,
            'items' => array_map([$this, 'present'], $rows),
            'has_more' => $hasMore,
            'next_cursor' => $hasMore && $last !== null ? (int) $last['id'] : null,
            'reply_count' => $this->comments->replyCounts([$parentId])[$parentId] ?? 0,
        ];
    }

    public function total(int $articleId): int {
        return (int) $this->cache->remember(self::cacheNamespace($articleId), 'total', self::TTL,
            fn (): int => $this->comments->totalForArticle($articleId));
    }

    private function page(int $articleId, ?int $before): array {
        $load = function () use ($articleId, $before): array {
            $rows = $this->comments->topLevel($articleId, $before, self::PAGE_SIZE + 1);
            $hasMore = count($rows) > self::PAGE_SIZE;
            $rows = array_slice($rows, 0, self::PAGE_SIZE);
            $ids = array_map(static fn (array $r): int => (int) $r['id'], $rows);
            $counts = $this->comments->replyCounts($ids);
            $byParent = [];

            foreach ($this->comments->firstReplies($ids, self::INITIAL_REPLIES) as $reply) {
                $byParent[(int) $reply['parent_id']][] = $this->present($reply);
            }

            $items = [];

            foreach ($rows as $row) {
                $id = (int) $row['id'];
                $items[] = $this->present($row) + [
                    'reply_count' => $counts[$id] ?? 0,
                    'replies' => $byParent[$id] ?? [],
                ];
            }

            $last = $rows ? $rows[count($rows) - 1] : null;

            return [
                'items' => $items,
                'has_more' => $hasMore,
                'next_cursor' => $hasMore && $last !== null ? (int) $last['id'] : null,
            ];
        };

        if ($before !== null) {
            return $load();
        }

        return $this->cache->remember(self::cacheNamespace($articleId), 'top:first', self::TTL, $load);
    }

    // =========================================================================
    // CREATE
    // =========================================================================
    /**
     * $targetId is the comment being answered. The server derives the thread and the
     * reply target from it, so a client cannot attach a reply to a mismatched thread.
     */
    public function create(array $user, string $publicId, mixed $content, mixed $targetId): array {
        $text = self::normalize((string) $content);
        $length = mb_strlen($text);

        if ($length === 0) {
            return $this->invalid(['content' => 'Write something before posting.']);
        }

        if ($length > self::MAX_LENGTH) {
            return $this->invalid(['content' => 'Comments are limited to ' . self::MAX_LENGTH . ' characters.']);
        }

        $articleId = $this->resolveArticle($publicId);

        if ($articleId === null) {
            return $this->fail('That article is not available.', 404);
        }

        $target = self::id($targetId);

        if ($target === false) {
            return $this->fail('Invalid reply target.');
        }

        $parentId = null;
        $replyToId = null;

        if ($target !== null) {
            $row = $this->comments->findById($target);

            if ($row === false || (int) $row['article_id'] !== $articleId) {
                return $this->fail('That comment no longer exists.', 404);
            }

            if ($row['parent_id'] === null) {
                $parentId = $target;
            } else {
                $parentId = (int) $row['parent_id'];
                $replyToId = $target;
            }
        }

        $userId = (int) $user['id'];

        if ($this->comments->countRecentByUser($userId, 60) >= self::FLOOD_LIMIT) {
            return $this->fail('You are commenting too quickly. Please wait a moment.', 429);
        }

        $stored = $this->banned->redact($text);

        if ($stored === null) {
            Logger::error(new \RuntimeException('Blog comment redaction failed.'));
            return $this->fail('Could not post your comment. Please try again.', 500);
        }

        $stored = mb_substr($stored, 0, 2000);

        try {
            $id = $this->comments->insert(['article_id' => $articleId, 'user_id' => $userId,
                'parent_id' => $parentId, 'reply_to_id' => $replyToId, 'content' => $stored]);
        } catch (\PDOException $e) {
            // The target was deleted between the lookup and the insert (foreign key).
            if (($e->errorInfo[0] ?? '') === '23000') {
                return $this->fail('That comment was just removed.', 409);
            }

            throw $e;
        }

        $this->invalidate($articleId);

        return [
            'success' => true,
            'message' => 'Comment posted.',
            'redacted' => $stored !== $text,
            'comment' => $this->present($this->comments->findById($id))
                + ['reply_count' => 0, 'replies' => []],
            'total' => $this->total($articleId),
        ];
    }

    // =========================================================================
    // DELETE (own comments only; removes the comment and everything beneath it)
    // =========================================================================
    public function delete(array $user, int $commentId): array {
        $this->db->beginTransaction();

        try {
            $row = $this->comments->lockById($commentId);

            if ($row === false) {
                $this->db->rollBack();
                return $this->fail('That comment no longer exists.', 404);
            }

            if ((int) $row['user_id'] !== (int) $user['id']) {
                $this->db->rollBack();
                return $this->fail('You can only delete your own comments.', 403);
            }

            // Top-level: the whole thread (every reply carries its parent_id).
            // Reply: that reply plus the replies-to-replies beneath it, never its ancestors or siblings.
            $ids = $row['parent_id'] === null
                ? array_merge([$commentId], $this->comments->lockIdsByParent($commentId))
                : $this->subtreeIds($commentId);

            $this->comments->deleteMany($ids);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }

        $articleId = (int) $row['article_id'];
        $this->invalidate($articleId);

        return ['success' => true, 'deleted_ids' => $ids, 'deleted' => count($ids),
            'total' => $this->total($articleId)];
    }

    /** @return int[] the comment and every descendant reached through reply_to_id */
    private function subtreeIds(int $rootId): array {
        $all = [$rootId => true];
        $frontier = [$rootId];

        while ($frontier) {
            $next = [];

            foreach (array_chunk($frontier, 500) as $chunk) {
                foreach ($this->comments->lockIdsByReplyTo($chunk) as $childId) {
                    if (!isset($all[$childId])) {
                        $all[$childId] = true;
                        $next[] = $childId;
                    }
                }
            }

            if (count($all) > self::MAX_SUBTREE) {
                throw new \RuntimeException('Comment subtree is too large to delete safely.');
            }

            $frontier = $next;
        }

        return array_keys($all);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================
    private function resolveArticle(string $publicId): ?int {
        return preg_match('/^[a-z0-9]{8}$/', $publicId) ? $this->articles->publicIdFor($publicId) : null;
    }

    private function invalidate(int $articleId): void {
        try {
            $this->cache->invalidate(self::cacheNamespace($articleId));
        } catch (\Throwable $e) {
            Logger::error($e);
        }
    }

    /** Public shape. The author's user id is deliberately not exposed. */
    private function present(array $r): array {
        $fullname = (string) ($r['fullname'] ?? '');
        $username = (string) ($r['username'] ?? '');
        $replyTo = $r['reply_to_id'] !== null ? (string) ($r['reply_to_username'] ?? '') : '';

        return [
            'id' => (int) $r['id'],
            'parent_id' => $r['parent_id'] !== null ? (int) $r['parent_id'] : null,
            'reply_to_id' => $r['reply_to_id'] !== null ? (int) $r['reply_to_id'] : null,
            'reply_to_username' => $replyTo !== '' ? $replyTo : null,
            'username' => $username,
            'fullname' => $fullname,
            'initials' => self::initials($fullname),
            'avatar_color' => self::avatarColor($username !== '' ? $username : $fullname),
            'content' => (string) $r['content'],
            'created_at' => (new \DateTimeImmutable((string) $r['created_at'], new \DateTimeZone('UTC')))
                ->format('Y-m-d\TH:i:s\Z'),
        ];
    }

    /** Keep this seed identical to what the member header passes for the current user's avatar. */
    private static function avatarColor(string $seed): string {
        return Avatar::color($seed);
    }

    private static function initials(string $name): string {
        $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $first = isset($parts[0]) ? mb_substr($parts[0], 0, 1) : '';
        $second = isset($parts[1]) ? mb_substr($parts[1], 0, 1) : '';

        return mb_strtoupper($first . $second);
    }

    private static function normalize(string $s): string {
        $s = str_replace(["\r\n", "\r"], "\n", mb_scrub($s));
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $s) ?? '';
        $s = preg_replace('/[ \t]+/', ' ', $s) ?? '';
        $s = preg_replace('/\n{3,}/', "\n\n", $s) ?? '';

        return trim($s);
    }

    /** @return int|false|null null = absent, false = malformed */
    private static function id(mixed $value): int|false|null {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    }

    private function invalid(array $errors): array {
        return ['success' => false, 'status' => 422, 'errors' => $errors];
    }

    private function fail(string $message, int $status = 422): array {
        return ['success' => false, 'status' => $status, 'message' => $message];
    }
}