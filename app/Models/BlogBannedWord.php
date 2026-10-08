<?php
namespace App\Models;
use PDO;

class BlogBannedWord {
    public function __construct(private PDO $db) {}

    public function all(): array {
        return $this->db->query('SELECT id, word, created_at, updated_at
            FROM blog_banned_words ORDER BY word ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return string[] */
    public function allWords(): array {
        return $this->db->query('SELECT word FROM blog_banned_words')->fetchAll(PDO::FETCH_COLUMN);
    }

    public function count(): int {
        return (int) $this->db->query('SELECT COUNT(*) FROM blog_banned_words')->fetchColumn();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT id, word, created_at, updated_at FROM blog_banned_words WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByWord(string $word): array|false {
        $stmt = $this->db->prepare('SELECT id, word FROM blog_banned_words WHERE word = :w LIMIT 1');
        $stmt->execute([':w' => $word]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(string $word): int {
        $this->db->prepare('INSERT INTO blog_banned_words (word) VALUES (:w)')->execute([':w' => $word]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $word): void {
        $this->db->prepare('UPDATE blog_banned_words SET word = :w WHERE id = :id')
            ->execute([':w' => $word, ':id' => $id]);
    }

    /** @param int[] $ids */
    public function deleteMany(array $ids): int {
        $stmt = $this->db->prepare('DELETE FROM blog_banned_words
            WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
        $stmt->execute(array_values($ids));
        return $stmt->rowCount();
    }
}