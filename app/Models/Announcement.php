<?php
namespace App\Models;
use PDO;

class Announcement {
    private const SELECT = "SELECT a.id, a.category_id, c.name AS category_name,
            c.accent AS accent, a.`date`, a.title, a.excerpt, a.cta, a.url,
            a.featured, a.image, a.image_alt, a.created_at, a.updated_at
        FROM announcements a
        INNER JOIN announcement_categories c ON c.id = a.category_id";

    public function __construct(private PDO $db) {}

    public function list(int $offset, int $limit): array {
        $stmt = $this->db->prepare(self::SELECT
            . " ORDER BY a.featured DESC, a.`date` DESC, a.id DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Keyset page with featured announcements first, then by newest date and id.
     * $after = ['featured' => bool, 'date' => 'Y-m-d', 'id' => int] resumes
     * strictly after that row so inserts and deletes between batches can never
     * repeat or skip a row.
     */
    public function keyset(?int $categoryId, ?array $after, int $limit): array {
        $where = [];
        $params = [];

        if ($categoryId !== null) {
            $where[] = 'a.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        if ($after !== null) {
            // Native prepares cannot reuse a named placeholder, hence repeated names.
            $where[] = '(a.featured < :after_featured OR (a.featured = :after_featured_eq AND (a.`date` < :after_date OR (a.`date` = :after_date_eq AND a.id < :after_id))))';
            $params[':after_featured'] = (int) $after['featured'];
            $params[':after_featured_eq'] = (int) $after['featured'];
            $params[':after_date'] = $after['date'];
            $params[':after_date_eq'] = $after['date'];
            $params[':after_id'] = $after['id'];
        }

        $sql = self::SELECT . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY a.featured DESC, a.`date` DESC, a.id DESC LIMIT :limit';

        $stmt = $this->db->prepare($sql);

        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM announcements")->fetchColumn();
    }

    public function countFeatured(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM announcements WHERE featured = 1");
        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare(self::SELECT . " WHERE a.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** @param int[] $ids */
    public function findByIds(array $ids): array {
        $stmt = $this->db->prepare(self::SELECT
            . " WHERE a.id IN (" . $this->placeholders($ids) . ")");
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO announcements
            (category_id, title, excerpt, cta, url, featured, `date`, image, image_alt)
            VALUES
            (:category_id, :title, :excerpt, :cta, :url, :featured, :date, :image, :image_alt)
        ");
        $stmt->execute($this->bindings($data));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE announcements SET category_id = :category_id,
            title = :title, excerpt = :excerpt, cta = :cta, url = :url,
            featured = :featured, `date` = :date, image = :image, image_alt = :image_alt
            WHERE id = :id
        ");
        return $stmt->execute($this->bindings($data) + [':id' => $id]);
    }

    /** @param int[] $ids */
    public function deleteMany(array $ids): int {
        $stmt = $this->db->prepare("DELETE FROM announcements
            WHERE id IN (" . $this->placeholders($ids) . ")
        ");
        $stmt->execute($ids);
        return $stmt->rowCount();
    }

    /** @param int[] $categoryIds */
    public function countByCategories(array $categoryIds): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM announcements
            WHERE category_id IN (" . $this->placeholders($categoryIds) . ")
        ");
        $stmt->execute($categoryIds);
        return (int) $stmt->fetchColumn();
    }

    /** @param int[] $fromCategoryIds */
    public function reassign(array $fromCategoryIds, int $toCategoryId): int {
        $stmt = $this->db->prepare("UPDATE announcements SET category_id = ?
            WHERE category_id IN (" . $this->placeholders($fromCategoryIds) . ")
        ");
        $stmt->execute(array_merge([$toCategoryId], $fromCategoryIds));
        return $stmt->rowCount();
    }

    private function bindings(array $data): array {
        return [
            ':category_id' => $data['category_id'],
            ':title' => $data['title'],
            ':excerpt' => $data['excerpt'],
            ':cta' => $data['cta'],
            ':url' => $data['url'],
            ':featured' => $data['featured'] ? 1 : 0,
            ':date' => $data['date'],
            ':image' => $data['image'] ?? null,
            ':image_alt' => $data['image_alt'] ?? null,
        ];
    }

    private function placeholders(array $ids): string {
        return implode(',', array_fill(0, count($ids), '?'));
    }
}