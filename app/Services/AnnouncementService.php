<?php
namespace App\Services;

use App\Core\Logger;
use App\Database\Database;
use App\Models\Announcement;
use App\Models\AnnouncementCategory;
use App\Support\FileCache;
use App\Support\ImageUpload;
use App\Models\BlogArticle;
use App\Support\BlogTitle;
use PDO;

final class AnnouncementService {
    public const EXCERPT_MAX = 150;
    public const MEMBER_PAGE_SIZE = 20;
    public const MAX_FEATURED = 5;
    public const CACHE_NAMESPACE = 'announcements';

    private const IMAGE_MAX_BYTES = 5 * 1024 * 1024;
    private const MAX_BULK = 500;
    private const CATEGORY_NAME_PATTERN = '/^[\p{L}\p{N}][\p{L}\p{N} &\'’().,\/-]*$/u';
    private const CACHE_TTL = 300;
    private static ?array $accents = null;

    public function __construct(
        private PDO $db,
        private Announcement $announcements,
        private AnnouncementCategory $categories,
        private ImageUpload $images,
        private FileCache $cache
    ) {}

    public static function make(): self {
        $db = Database::connection();

        return new self($db, new Announcement($db), new AnnouncementCategory($db),
            new ImageUpload('announcements', self::IMAGE_MAX_BYTES),
            new FileCache(STORAGE_PATH . '/cache')
        );
    }

    /** @return array<string, array{label: string, hex: string}> */
    public static function accents(): array {
        return self::$accents ??= require __DIR__ . '/../../config/announcement_accents.php';
    }

    // =========================================================================
    // READ
    // =========================================================================
    public function listAnnouncements(int $offset, int $limit): array {
        $offset = max(0, $offset);
        $limit = min(100, max(1, $limit));
        $rows = $this->announcements->list($offset, $limit);
        $total = $this->announcements->count();

        return [
            'items' => array_map([$this, 'presentAnnouncement'], $rows),
            'total' => $total,
            'has_more' => $offset + count($rows) < $total,
        ];
    }

    public function listCategories(): array {
        return array_map([$this, 'presentCategory'], $this->categories->all());
    }

    // =========================================================================
    // MEMBER READ (cached)
    // =========================================================================
    /**
     * Categories that currently have at least one announcement, for the member
     * filter tabs. One grouped query; cached.
     *
     * @return array<int, array{id:int,name:string,accent:string,count:int}>
     */
    public function memberCategories(): array {
        return $this->cached('categories', fn (): array => array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'accent' => $row['accent'],
                'count' => (int) $row['announcement_count'],
            ],
            $this->categories->withAnnouncements()
        ));
    }

    /**
     * One batch of announcements for the member page, filtered in SQL.
     * Fetches limit + 1 rows so has_more is exact (no wasted request when the
     * total is a multiple of the batch size).
     *
     * Only the first batch is cached (see notes): the cache key space stays
     * bounded by the number of real categories, and a client-supplied cursor
     * can never create cache files.
     *
     * @return array{items: array, has_more: bool, next_cursor: ?string}
     */
    public function memberBatch(?int $categoryId, ?string $cursor = null,
        int $limit = self::MEMBER_PAGE_SIZE
    ): array {
        $empty = ['items' => [], 'has_more' => false, 'next_cursor' => null];
        $limit = min(self::MEMBER_PAGE_SIZE, max(1, $limit));
        $after = self::parseCursor($cursor);

        if ($after === false) {
            return $empty;
        }

        // Only categories that have announcements are queried or cached.
        if ($categoryId !== null
            && !in_array($categoryId, array_column($this->memberCategories(), 'id'), true)
        ) {
            return $empty;
        }

        $load = function () use ($categoryId, $after, $limit): array {
            $rows = $this->announcements->keyset($categoryId, $after, $limit + 1);
            $hasMore = count($rows) > $limit;
            $rows = array_slice($rows, 0, $limit);
            $last = $rows ? $rows[count($rows) - 1] : null;

            return [
                'items' => array_map([$this, 'memberShape'], $rows),
                'has_more' => $hasMore,
                'next_cursor' => $hasMore && $last !== null
                    ? ((int) $last['featured']) . '_' . $last['date'] . '_' . $last['id']
                    : null,
            ];
        };

        if ($after !== null) {
            // Deeper pages are never cached (see notes): always the database.
            $this->cache->note(sprintf('batch:%s:cursor', $categoryId ?? 'all'), 'bypass');
            return $load();
        }

        return $this->cached(sprintf('batch:%s:first:%d', $categoryId ?? 'all', $limit), $load);
    }

    /**
     * Cursor format: "{featured}_{Y-m-d}_{id}" (the last row of the previous batch).
     * For backward compatibility, the legacy "{Y-m-d}_{id}" format is also accepted.
     *
     * @return array{featured: bool, date: string, id: int}|false|null  null = no cursor, false = malformed
     */
    public static function parseCursor(?string $cursor): array|false|null {
        if ($cursor === null || $cursor === '') {
            return null;
        }

        if (preg_match('/^(0|1)_(\d{4})-(\d{2})-(\d{2})_(\d{1,18})$/', $cursor, $m)
            && checkdate((int) $m[3], (int) $m[4], (int) $m[2])
        ) {
            return ['featured' => (bool) (int) $m[1], 'date' => "{$m[2]}-{$m[3]}-{$m[4]}", 'id' => (int) $m[5]];
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})_(\d{1,18})$/', $cursor, $m)
            && checkdate((int) $m[2], (int) $m[3], (int) $m[1])
        ) {
            return ['featured' => false, 'date' => "{$m[1]}-{$m[2]}-{$m[3]}", 'id' => (int) $m[4]];
        }

        return false;
    }

    /** The row shape the member partials already consume (plus category_id). */
    private function memberShape(array $row): array {
        return [
            'id' => (string) $row['id'],
            'category_id' => (int) $row['category_id'],
            'category' => $row['category_name'],
            'accent' => $row['accent'],
            'date' => $row['date'],
            'title' => $row['title'],
            'excerpt' => $row['excerpt'],
            'cta' => $row['cta'],
            'url' => $this->destinationUrl($row),
            'featured' => (bool) $row['featured'],
            'image' => $row['image'] ?: null,
            'image_alt' => $row['image_alt'] ?: null,
        ];
    }

    /** What the cache did during this request: hit, miss (DB queried then stored), bypass, off. */
    public function cacheTrace(): array {
        return $this->cache->trace();
    }

    private function cached(string $key, callable $compute): mixed {
        return $this->cache->remember(self::CACHE_NAMESPACE, $key, self::CACHE_TTL, $compute);
    }

    /** Drops every cached member read. Never throws: a failed purge must not fail the admin action. */
    private function invalidateMemberCache(): void {
        try {
            $this->cache->invalidate(self::CACHE_NAMESPACE);
        } catch (\Throwable $e) {
            Logger::error($e);
        }
    }

    // =========================================================================
    // ANNOUNCEMENTS
    // =========================================================================
    public function createAnnouncement(array $input, array $files): array {
        [$data, $errors] = $this->validateAnnouncement($input, $files);

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $upload = null;

        if ($this->images->hasUpload($files, 'image')) {
            $upload = $this->images->store($files['image']);

            if ($upload === null) {
                return ['success' => false,
                    'errors' => ['image' => 'Unable to save the image. Please try again.'],
                ];
            }
        }

        $data['image'] = $upload['publicPath'] ?? null;
        $data['image_alt'] = $data['image'] === null ? null : ($data['image_alt'] ?: null);

        try {
            $id = $this->announcements->create($data);
        } catch (\Throwable $e) {
            $this->images->delete($upload['publicPath'] ?? null);
            throw $e;
        }

        $this->invalidateMemberCache();

        return [
            'success' => true,
            'message' => 'Announcement created.',
            'announcement' => $this->presentAnnouncement($this->announcements->findById($id)),
        ];
    }

    public function updateAnnouncement(int $id, array $input, array $files): array {
        $existing = $this->announcements->findById($id);

        if ($existing === false) {
            return $this->fail('That announcement no longer exists.', 404);
        }

        [$data, $errors] = $this->validateAnnouncement($input, $files, $id);

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $imagePath = $existing['image'] ?: null;
        $upload = null;

        if ($this->images->hasUpload($files, 'image')) {
            $upload = $this->images->store($files['image']);

            if ($upload === null) {
                return ['success' => false,
                    'errors' => ['image' => 'Unable to save the image. Please try again.'],
                ];
            }

            $imagePath = $upload['publicPath'];
        } elseif (($input['remove_image'] ?? '0') === '1') {
            $imagePath = null;
        }

        $data['image'] = $imagePath;
        $data['image_alt'] = $imagePath === null ? null : ($data['image_alt'] ?: null);

        try {
            $this->announcements->update($id, $data);
        } catch (\Throwable $e) {
            $this->images->delete($upload['publicPath'] ?? null);
            throw $e;
        }

        if (($existing['image'] ?: null) !== $imagePath) {
            $this->images->delete($existing['image'] ?: null);
        }

        $this->invalidateMemberCache();

        return [
            'success' => true,
            'message' => 'Announcement updated.',
            'announcement' => $this->presentAnnouncement($this->announcements->findById($id)),
        ];
    }

    public function deleteAnnouncements(mixed $ids): array {
        $ids = $this->normalizeIds($ids);

        if (!$ids) {
            return $this->fail('No announcements were selected.');
        }

        $rows = $this->announcements->findByIds($ids);
        $deleted = $this->announcements->deleteMany($ids);

        foreach ($rows as $row) {
            $this->images->delete($row['image'] ?: null);
        }

        $this->invalidateMemberCache();

        return ['success' => true, 'deleted' => $deleted, 'message' => 'Announcements deleted.'];
    }

    // =========================================================================
    // CATEGORIES
    // =========================================================================
    public function createCategory(array $input): array {
        [$data, $errors] = $this->validateCategory($input, null);

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            $id = $this->categories->create($data['name'], $data['accent']);
        } catch (\PDOException $e) {
            if ($duplicate = $this->duplicateKeyErrors($e)) {
                return ['success' => false, 'errors' => $duplicate];
            }

            throw $e;
        }

        $this->invalidateMemberCache();

        return [
            'success' => true,
            'message' => 'Category created.',
            'category' => $this->presentCategory($this->categories->findById($id)),
        ];
    }

    public function updateCategory(int $id, array $input): array {
        if ($this->categories->findById($id) === false) {
            return $this->fail('That category no longer exists.', 404);
        }

        [$data, $errors] = $this->validateCategory($input, $id);

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            $this->categories->update($id, $data['name'], $data['accent']);
        } catch (\PDOException $e) {
            if ($duplicate = $this->duplicateKeyErrors($e)) {
                return ['success' => false, 'errors' => $duplicate];
            }

            throw $e;
        }

        $this->invalidateMemberCache();

        return [
            'success' => true,
            'message' => 'Category updated.',
            'category' => $this->presentCategory($this->categories->findById($id)),
        ];
    }

    /**
     * Deletion policy: a category that still has announcements can only be
     * deleted together with an explicit destination for them. Announcements
     * are moved and the categories removed in one transaction, so nothing is
     * ever orphaned. The FK is ON DELETE RESTRICT as a last line of defence.
     */
    public function deleteCategories(mixed $ids, mixed $reassignTo): array {
        $ids = $this->normalizeIds($ids);

        if (!$ids) {
            return $this->fail('No categories were selected.');
        }

        $target = null;

        if ($reassignTo !== null && $reassignTo !== '') {
            $target = filter_var($reassignTo, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($target === false) {
                return $this->fail('Choose a valid category to move announcements to.');
            }

            if (in_array($target, $ids, true)) {
                return $this->fail('Announcements cannot be moved into a category that is being deleted.');
            }
        }

        $this->db->beginTransaction();

        try {
            $existing = $this->categories->lockByIds($ids);

            if (count($existing) !== count($ids)) {
                $this->db->rollBack();
                return $this->fail('One or more categories no longer exist. Refresh and try again.', 409);
            }

            $inUse = $this->announcements->countByCategories($ids);
            $moved = 0;

            if ($inUse > 0) {
                if ($target === null) {
                    $this->db->rollBack();
                    return $this->fail(
                        'These categories still have announcements. Choose where to move them first.',
                        422, ['code' => 'reassign_required']
                    );
                }

                if (count($this->categories->lockByIds([$target])) !== 1) {
                    $this->db->rollBack();
                    return $this->fail('The category you chose no longer exists.');
                }

                $moved = $this->announcements->reassign($ids, $target);
            }

            $deleted = $this->categories->deleteMany($ids);
            $this->db->commit();
            $this->invalidateMemberCache(); // after commit, so no reader can cache pre-commit rows
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            // FK violation: an announcement was filed under the category mid-request.
            if (($e->errorInfo[0] ?? '') === '23000') {
                return $this->fail('A category is still in use. Refresh and try again.', 409);
            }

            throw $e;
        }

        return ['success' => true, 'deleted' => $deleted, 'moved' => $moved,
            'message' => 'Categories deleted.',
        ];
    }

    // =========================================================================
    // PRESENTATION
    // =========================================================================
    public function presentAnnouncement(array $row): array {
        $image = $row['image'] ?: null;

        return [
            'id' => (int) $row['id'],
            'category_id' => (int) $row['category_id'],
            'category_name' => $row['category_name'],
            'accent' => $row['accent'],
            'date' => $row['date'],
            'title' => $row['title'],
            'excerpt' => $row['excerpt'],
            'cta' => $row['cta'],
            'url' => $this->destinationUrl($row),
            'blog_article' => !empty($row['blog_article_id']) ? [
                'id' => (int) $row['blog_article_id'],
                'title' => $row['blog_title'] !== null ? BlogTitle::plain((string) $row['blog_title']) : null,
                'is_public' => (bool) $row['blog_is_public'],
            ] : null,
            'featured' => (bool) $row['featured'],
            'image' => $image,
            'image_url' => $this->resolveImageUrl($image),
            'image_alt' => $row['image_alt'] ?: null,
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }

    public function presentCategory(array $row): array {
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'accent' => $row['accent'],
            'usage_count' => (int) $row['usage_count'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }

    /**
     * Same rules as the member page: absolute http(s) URLs pass through,
     * root-relative paths must exist locally, anything else renders the
     * neutral placeholder (null).
     */
    public static function resolveImageUrl(?string $image): ?string {
        $image = trim((string) $image);

        if ($image === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $image)) {
            return filter_var($image, FILTER_VALIDATE_URL) ? $image : null;
        }

        if (!str_starts_with($image, '/') || str_starts_with($image, '//')
            || str_contains($image, '..') || str_contains($image, '\\')
            || str_contains($image, "\0")
        ) {
            return null;
        }

        $file = str_starts_with($image, '/storage/')
            ? STORAGE_PATH . substr($image, strlen('/storage'))
            : PUBLIC_PATH . $image;

        return is_file($file) ? $image : null;
    }

    // =========================================================================
    // VALIDATION
    // =========================================================================
    private function validateAnnouncement(array $input, array $files, ?int $existingId = null): array {
        $errors = [];
        $title = $this->clean($input['title'] ?? '');
        $excerpt = $this->clean($input['excerpt'] ?? '');
        $cta = $this->clean($input['cta'] ?? '');
        $imageAlt = $this->clean($input['image_alt'] ?? '');
        $url = trim((string) ($input['url'] ?? ''));
        $date = trim((string) ($input['date'] ?? ''));
        $featured = in_array((string) ($input['featured'] ?? '0'), ['1', 'true', 'on'], true);
        $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($featured) {
            $currentFeatured = (int) $this->announcements->countFeatured();
            $alreadyFeatured = false;

            if ($existingId !== null) {
                $current = $this->announcements->findById($existingId);
                $alreadyFeatured = $current !== false && !empty($current['featured']);
            }

            if ($currentFeatured >= self::MAX_FEATURED && !$alreadyFeatured) {
                $errors['featured'] = 'Only ' . self::MAX_FEATURED . ' announcements can be featured at a time.';
            }
        }

        $blogArticleId = null;
        $rawArticle = $input['blog_article_id'] ?? '';

        if ($rawArticle !== '' && $rawArticle !== null) {
            $linkId = filter_var($rawArticle, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($linkId === false) {
                $errors['blog_article_id'] = 'Choose a valid article.';
            } else {
                $link = (new BlogArticle($this->db))->findPublicLink($linkId);
                $current = $existingId !== null ? $this->announcements->findById($existingId) : false;
                $unchanged = $current !== false && (int) $current['blog_article_id'] === $linkId;

                if ($link !== false) {
                    $blogArticleId = $linkId;
                    $url = BlogService::articleUrl($link); // snapshot: the fallback if the article goes away
                } elseif ($unchanged) {
                    $blogArticleId = $linkId;             // already linked, now unpublished: keep as is
                    $url = (string) $current['url'];
                } else {
                    $errors['blog_article_id'] = 'Choose a published article.';
                }
            }
        }

        if ($categoryId === false) {
            $errors['category_id'] = 'Choose a category.';
        } elseif ($this->categories->findById($categoryId) === false) {
            $errors['category_id'] = 'That category no longer exists.';
        }

        if ($title === '') {
            $errors['title'] = 'Title is required.';
        } elseif (mb_strlen($title) < 3 || mb_strlen($title) > 120) {
            $errors['title'] = 'Title must be between 3 and 120 characters.';
        }

        if ($excerpt === '') {
            $errors['excerpt'] = 'Excerpt is required.';
        } elseif (mb_strlen($excerpt) < 10 || mb_strlen($excerpt) > self::EXCERPT_MAX) {
            $errors['excerpt'] = 'Excerpt must be between 10 and ' . self::EXCERPT_MAX . ' characters.';
        }

        if ($cta === '') {
            $errors['cta'] = 'Button label is required.';
        } elseif (mb_strlen($cta) < 2 || mb_strlen($cta) > 30) {
            $errors['cta'] = 'Button label must be between 2 and 30 characters.';
        }

        if ($url === '') {
            $errors['url'] = 'Destination URL is required.';
        } elseif (!$this->validDestination($url)) {
            $errors['url'] = 'Use a path such as /member/awards/voting or a full https:// link.';
        }

        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        if ($date === '') {
            $errors['date'] = 'Date is required.';
        } elseif ($parsed === false || $parsed->format('Y-m-d') !== $date) {
            $errors['date'] = 'Enter a valid date.';
        }

        if (mb_strlen($imageAlt) > 200) {
            $errors['image_alt'] = 'Alt text must not exceed 200 characters.';
        }

        if ($this->images->hasUpload($files, 'image')) {
            $imageError = $this->images->inspect($files['image']);

            if ($imageError !== null) {
                $errors['image'] = $imageError;
            }
        }

        return [[
            'category_id' => $categoryId === false ? null : $categoryId,
            'title' => $title,
            'excerpt' => $excerpt,
            'blog_article_id' => $blogArticleId,
            'cta' => $cta,
            'url' => $url,
            'featured' => $featured,
            'date' => $date,
            'image_alt' => $imageAlt,
        ], $errors];
    }

    private function validateCategory(array $input, ?int $ignoreId): array {
        $errors = [];
        $name = $this->clean($input['name'] ?? '');
        $accent = strtolower(trim((string) ($input['accent'] ?? '')));

        if ($name === '') {
            $errors['name'] = 'Category name is required.';
        } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 30) {
            $errors['name'] = 'Name must be between 2 and 30 characters.';
        } elseif (!preg_match(self::CATEGORY_NAME_PATTERN, $name)) {
            $errors['name'] = 'Use letters, numbers, spaces and basic punctuation only.';
        } else {
            $existing = $this->categories->findByName($name);

            if ($existing !== false && (int) $existing['id'] !== $ignoreId) {
                $errors['name'] = 'A category with this name already exists.';
            }
        }

        if ($accent === '') {
            $errors['accent'] = 'Choose an accent.';
        } elseif (!isset(self::accents()[$accent])) {
            $errors['accent'] = 'That accent is not available.';
        } else {
            $holder = $this->categories->findByAccent($accent);

            if ($holder !== false && (int) $holder['id'] !== $ignoreId) {
                $errors['accent'] = 'That accent is already used by "' . $holder['name'] . '".';
            }
        }

        return [['name' => $name, 'accent' => $accent], $errors];
    }

    private function validDestination(string $url): bool {
        if (strlen($url) > 500 || preg_match('/[\x00-\x20\x7f]/', $url)) {
            return false;
        }

        if (str_starts_with($url, '/')) {
            return !str_starts_with($url, '//') && !str_contains($url, '\\');
        }

        return preg_match('#^https?://#i', $url) === 1
            && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    // =========================================================================
    // HELPERS
    // =========================================================================
    private function clean(mixed $value): string {
        return trim((string) preg_replace('/\s+/u', ' ', (string) $value));
    }

    /** A linked, currently public article wins (so renamed slugs follow); otherwise the stored URL. */
    private function destinationUrl(array $row): string {
        if (!empty($row['blog_article_id']) && !empty($row['blog_is_public']) && !empty($row['blog_slug'])) {
            return BlogService::articleUrl(['slug' => $row['blog_slug'], 'public_id' => $row['blog_public_id']]);
        }

        return (string) $row['url'];
    }

    /** @return int[] */
    private function normalizeIds(mixed $ids): array {
        if (!is_array($ids)) {
            return [];
        }

        $clean = [];

        foreach ($ids as $id) {
            if (is_int($id) || (is_string($id) && ctype_digit($id))) {
                $number = (int) $id;

                if ($number > 0) {
                    $clean[$number] = $number;
                }
            }
        }

        return array_slice(array_values($clean), 0, self::MAX_BULK);
    }

    private function duplicateKeyErrors(\PDOException $e): ?array {
        if (($e->errorInfo[0] ?? '') !== '23000' || (int) ($e->errorInfo[1] ?? 0) !== 1062) {
            return null;
        }

        $message = (string) ($e->errorInfo[2] ?? '');

        if (str_contains($message, 'uq_announcement_categories_accent')) {
            return ['accent' => 'That accent is already used by another category.'];
        }

        if (str_contains($message, 'uq_announcement_categories_name')) {
            return ['name' => 'A category with this name already exists.'];
        }

        return null;
    }

    private function fail(string $message, int $status = 422, array $extra = []): array {
        return ['success' => false, 'status' => $status, 'message' => $message] + $extra;
    }
}