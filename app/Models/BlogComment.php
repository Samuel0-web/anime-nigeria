<?php
namespace App\Models;
use PDO;

class BlogComment {
    private const SELECT = "SELECT c.id, c.article_id, c.parent_id, c.reply_to_id, c.user_id, c.content,
            c.created_at, u.fullname, u.username, ru.username AS reply_to_username
        FROM blog_comments c
        INNER JOIN users u ON u.id = c.user_id
        LEFT JOIN blog_comments rc ON rc.id = c.reply_to_id
        LEFT JOIN users ru ON ru.id = rc.user_id";

    public function __construct(private PDO $db) {}

    /** Newest first. $before = id of the last comment already shown. Callers pass limit + 1. */
    public function topLevel(int $articleId, ?int $before, int $limit): array {
        $stmt = $this->db->prepare(self::SELECT . ' WHERE c.article_id = :article AND c.parent_id IS NULL'
            . ($before !== null ? ' AND c.id < :before' : '') . ' ORDER BY c.id DESC LIMIT :limit');
        $stmt->bindValue(':article', $articleId, PDO::PARAM_INT);

        if ($before !== null) {
            $stmt->bindValue(':before', $before, PDO::PARAM_INT);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Oldest first, resuming strictly after $after. Callers pass limit + 1. */
    public function replies(int $parentId, ?int $after, int $limit): array {
        $stmt = $this->db->prepare(self::SELECT . ' WHERE c.parent_id = :parent'
            . ($after !== null ? ' AND c.id > :after' : '') . ' ORDER BY c.id ASC LIMIT :limit');
        $stmt->bindValue(':parent', $parentId, PDO::PARAM_INT);

        if ($after !== null) {
            $stmt->bindValue(':after', $after, PDO::PARAM_INT);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** First N replies of every listed thread in one round trip (one index-bound member per thread). */
    public function firstReplies(array $parentIds, int $perThread): array {
        if (!$parentIds) {
            return [];
        }

        $parts = [];
        $params = [];

        foreach (array_values($parentIds) as $i => $parentId) {
            $parts[] = '(' . self::SELECT . " WHERE c.parent_id = :p$i ORDER BY c.id ASC LIMIT " . (int) $perThread . ')';
            $params[":p$i"] = (int) $parentId;
        }

        $stmt = $this->db->prepare(implode(' UNION ALL ', $parts) . ' ORDER BY parent_id ASC, id ASC');

        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @param int[] $parentIds @return array<int,int> parent id => reply count */
    public function replyCounts(array $parentIds): array {
        if (!$parentIds) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT parent_id, COUNT(*) AS n FROM blog_comments
            WHERE parent_id IN (' . implode(',', array_fill(0, count($parentIds), '?')) . ') GROUP BY parent_id');
        $stmt->execute(array_values($parentIds));
        $out = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[(int) $row['parent_id']] = (int) $row['n'];
        }

        return $out;
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare(self::SELECT . ' WHERE c.id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function totalForArticle(int $articleId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM blog_comments WHERE article_id = :a');
        $stmt->execute([':a' => $articleId]);
        return (int) $stmt->fetchColumn();
    }

    public function insert(array $d): int {
        $stmt = $this->db->prepare('INSERT INTO blog_comments
            (article_id, user_id, parent_id, reply_to_id, content, created_at)
            VALUES (:article, :user, :parent, :reply_to, :content, UTC_TIMESTAMP())');
        $stmt->bindValue(':article', $d['article_id'], PDO::PARAM_INT);
        $stmt->bindValue(':user', $d['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':parent', $d['parent_id'], $d['parent_id'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':reply_to', $d['reply_to_id'], $d['reply_to_id'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':content', $d['content']);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    // ---- deletion helpers (call inside a transaction) -----------------------
    public function lockById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT id, article_id, user_id, parent_id
            FROM blog_comments WHERE id = :id LIMIT 1 FOR UPDATE');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** @return int[] every reply of a thread (all of them carry the thread's parent_id) */
    public function lockIdsByParent(int $parentId): array {
        $stmt = $this->db->prepare('SELECT id FROM blog_comments WHERE parent_id = :p FOR UPDATE');
        $stmt->execute([':p' => $parentId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @param int[] $ids @return int[] direct replies to any of these comments */
    public function lockIdsByReplyTo(array $ids): array {
        $stmt = $this->db->prepare('SELECT id FROM blog_comments
            WHERE reply_to_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ') FOR UPDATE');
        $stmt->execute(array_values($ids));
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @param int[] $ids */
    public function deleteMany(array $ids): int {
        $deleted = 0;

        foreach (array_chunk(array_values($ids), 500) as $chunk) {
            $stmt = $this->db->prepare('DELETE FROM blog_comments
                WHERE id IN (' . implode(',', array_fill(0, count($chunk), '?')) . ')');
            $stmt->execute($chunk);
            $deleted += $stmt->rowCount();
        }

        return $deleted;
    }

    // ---- abuse guards --------------------------------------------------------
    public function countRecentByUser(int $userId, int $seconds): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM blog_comments WHERE user_id = :u
            AND created_at > UTC_TIMESTAMP() - INTERVAL ' . (int) $seconds . ' SECOND');
        $stmt->execute([':u' => $userId]);
        return (int) $stmt->fetchColumn();
    }
}