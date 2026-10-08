<?php
namespace App\Models;
use PDO;

class BlogCategory {
    private const PUBLIC = "a.status = 'published' AND a.published_at <= UTC_TIMESTAMP()";

    public function __construct(private PDO $db) {}

    public function all(): array {
        return $this->db->query("SELECT c.id, c.slug, c.label, c.description, c.created_at, c.updated_at,
                (SELECT COUNT(*) FROM blog_articles a WHERE a.category_id = c.id AND " . self::PUBLIC . ") AS published_count,
                (SELECT COUNT(*) FROM blog_articles a WHERE a.category_id = c.id) AS total_count
            FROM blog_categories c ORDER BY c.label ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM blog_categories WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByLabel(string $label): array|false {
        $stmt = $this->db->prepare("SELECT * FROM blog_categories WHERE label = :label LIMIT 1");
        $stmt->execute([':label' => $label]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function slugExists(string $slug): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM blog_categories WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetchColumn() !== false;
    }

    public function create(string $slug, string $label, string $description): int {
        $stmt = $this->db->prepare("INSERT INTO blog_categories (slug, label, description)
            VALUES (:slug, :label, :description)
        ");
        $stmt->execute([':slug' => $slug, ':label' => $label, ':description' => $description]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $label, string $description): void {
        $stmt = $this->db->prepare("UPDATE blog_categories SET label = :label,
            description = :description WHERE id = :id
        ");
        $stmt->execute([':id' => $id, ':label' => $label, ':description' => $description]);
    }

    /** @param int[] $ids @return int[] ids that exist (row-locked) */
    public function lockByIds(array $ids): array {
        $stmt = $this->db->prepare("SELECT id FROM blog_categories
            WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ") FOR UPDATE
        ");
        $stmt->execute(array_values($ids));
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @param int[] $ids */
    public function deleteMany(array $ids): int {
        $stmt = $this->db->prepare("DELETE FROM blog_categories
            WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")
        ");
        $stmt->execute(array_values($ids));
        return $stmt->rowCount();
    }

    /** @param int[] $fromIds */
    public function countArticles(array $fromIds): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM blog_articles
            WHERE category_id IN (" . implode(',', array_fill(0, count($fromIds), '?')) . ")
        ");
        $stmt->execute(array_values($fromIds));
        return (int) $stmt->fetchColumn();
    }

    /** @param int[] $fromIds */
    public function reassignArticles(array $fromIds, int $toId): int {
        $stmt = $this->db->prepare("UPDATE blog_articles SET category_id = ?
            WHERE category_id IN (" . implode(',', array_fill(0, count($fromIds), '?')) . ")
        ");
        $stmt->execute(array_merge([$toId], array_values($fromIds)));
        return $stmt->rowCount();
    }
}