<?php
namespace App\Models;
use PDO;

class AnnouncementCategory {
    private const SELECT = "SELECT c.id, c.name, c.accent, c.created_at, c.updated_at,
            (SELECT COUNT(*) FROM announcements a WHERE a.category_id = c.id) AS usage_count
        FROM announcement_categories c";

    public function __construct(private PDO $db) {}

    public function all(): array {
        return $this->db->query(self::SELECT . " ORDER BY c.name ASC")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Only categories that currently have at least one announcement (member
     * filter tabs), most recently active first. The INNER JOIN is what drops
     * empty categories.
     */
    public function withAnnouncements(): array {
        return $this->db->query("SELECT c.id, c.name, c.accent,
                COUNT(a.id) AS announcement_count
            FROM announcement_categories c
            INNER JOIN announcements a ON a.category_id = c.id
            GROUP BY c.id, c.name, c.accent
            ORDER BY MAX(a.`date`) DESC, c.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare(self::SELECT . " WHERE c.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByName(string $name): array|false {
        $stmt = $this->db->prepare(self::SELECT . " WHERE c.name = :name LIMIT 1");
        $stmt->execute([':name' => $name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByAccent(string $accent): array|false {
        $stmt = $this->db->prepare(self::SELECT . " WHERE c.accent = :accent LIMIT 1");
        $stmt->execute([':accent' => $accent]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(string $name, string $accent): int {
        $stmt = $this->db->prepare("INSERT INTO announcement_categories (name, accent)
            VALUES (:name, :accent)
        ");
        $stmt->execute([':name' => $name, ':accent' => $accent]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $accent): bool {
        $stmt = $this->db->prepare("UPDATE announcement_categories
            SET name = :name, accent = :accent WHERE id = :id
        ");
        return $stmt->execute([':id' => $id, ':name' => $name, ':accent' => $accent]);
    }

    /**
     * Row-lock the given categories inside a transaction and return the ids
     * that exist.
     *
     * @param int[] $ids
     * @return int[]
     */
    public function lockByIds(array $ids): array {
        $stmt = $this->db->prepare("SELECT id FROM announcement_categories
            WHERE id IN (" . $this->placeholders($ids) . ") FOR UPDATE
        ");
        $stmt->execute($ids);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @param int[] $ids */
    public function deleteMany(array $ids): int {
        $stmt = $this->db->prepare("DELETE FROM announcement_categories
            WHERE id IN (" . $this->placeholders($ids) . ")
        ");
        $stmt->execute($ids);
        return $stmt->rowCount();
    }

    private function placeholders(array $ids): string {
        return implode(',', array_fill(0, count($ids), '?'));
    }
}