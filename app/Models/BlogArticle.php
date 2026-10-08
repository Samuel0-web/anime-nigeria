<?php
namespace App\Models;
use PDO;

class BlogArticle {
    /** The one definition of "publicly visible". */
    private const PUBLIC = "a.status = 'published' AND a.published_at <= UTC_TIMESTAMP()";
    private const JOINS = " FROM blog_articles a
        LEFT JOIN blog_categories c ON c.id = a.category_id
        LEFT JOIN users u ON u.id = a.author_id";
    // Cards never load the article body.
    private const CARD = "SELECT a.id, a.public_id, a.slug, a.title, a.excerpt, a.cover_image, a.cover_image_alt,
        a.status, a.published_at, a.archived_at, a.reading_time_minutes, a.word_count, a.featured_position,
        a.category_id, a.updated_at, c.slug AS category_slug, c.label AS category_label,
        u.fullname AS author_name" . self::JOINS;
    private const FULL = "SELECT a.*, c.slug AS category_slug, c.label AS category_label,
        u.fullname AS author_name" . self::JOINS;
    private const WRITABLE = ['public_id', 'slug', 'title', 'excerpt', 'category_id', 'cover_image',
        'cover_image_alt', 'content_html', 'word_count', 'reading_time_minutes', 'status', 'published_at',
        'archived_at', 'featured_position', 'author_id'];

    public function __construct(private PDO $db) {}

    // ---- public reads ------------------------------------------------------
    public function findPublicByPublicId(string $publicId): array|false {
        $stmt = $this->db->prepare(self::FULL . " WHERE a.public_id = :pid AND " . self::PUBLIC . " LIMIT 1");
        $stmt->execute([':pid' => $publicId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Newest first, ties broken by id: a total, stable order. Callers pass limit + 1 for look-ahead. */
    public function listPublic(?int $categoryId, ?string $search, int $offset, int $limit,
        array $exclude = []
    ): array {
        $params = [];
        $where = self::PUBLIC;

        if ($categoryId !== null) {
            $where .= ' AND a.category_id = :cat';
            $params[':cat'] = $categoryId;
        }

        if ($search !== null && $search !== '') {
            $where .= " AND (REPLACE(a.title, '_', '') LIKE :q1 ESCAPE '!' OR a.excerpt LIKE :q2 ESCAPE '!'
                OR c.label LIKE :q3 ESCAPE '!' OR EXISTS (SELECT 1 FROM blog_article_tags x
                    INNER JOIN blog_tags t ON t.id = x.tag_id
                    WHERE x.article_id = a.id AND t.name LIKE :q4 ESCAPE '!'))";
            $like = self::like($search);

            foreach ([':q1', ':q2', ':q3', ':q4'] as $name) {
                $params[$name] = $like;
            }
        }

        $where .= $this->exclusion($exclude, $params);

        $stmt = $this->db->prepare(self::CARD . " WHERE $where
            ORDER BY a.published_at DESC, a.id DESC LIMIT :limit OFFSET :offset");
        $this->bind($stmt, $params);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function featuredPublic(int $limit): array {
        $stmt = $this->db->prepare(self::CARD . " WHERE " . self::PUBLIC . "
            AND a.featured_position IS NOT NULL ORDER BY a.featured_position ASC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return int[] categories that still have a visible article after the exclusions */
    public function eligibleCategoryIds(array $exclude): array {
        $params = [];
        $stmt = $this->db->prepare("SELECT DISTINCT a.category_id FROM blog_articles a
            WHERE " . self::PUBLIC . " AND a.category_id IS NOT NULL" . $this->exclusion($exclude, $params));
        $this->bind($stmt, $params);
        $stmt->execute();
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Pseudo-random but deterministic for a given seed: stable until the seed changes. */
    public function randomPublic(array $exclude, string $seed, int $limit): array {
        $params = [':seed' => $seed];
        $stmt = $this->db->prepare(self::CARD . " WHERE " . self::PUBLIC . $this->exclusion($exclude, $params) . "
            ORDER BY SHA1(CONCAT(:seed, a.id)) ASC, a.id ASC LIMIT :limit");
        $this->bind($stmt, $params);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @param int[] $ids @return array<int, list<string>> */
    public function tagsFor(array $ids): array {
        if (!$ids) {
            return [];
        }

        $stmt = $this->db->prepare("SELECT x.article_id, t.name FROM blog_article_tags x
            INNER JOIN blog_tags t ON t.id = x.tag_id
            WHERE x.article_id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")
            ORDER BY t.name ASC");
        $stmt->execute(array_values($ids));
        $map = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $map[(int) $row['article_id']][] = $row['name'];
        }

        return $map;
    }

    // ---- admin reads -------------------------------------------------------
    public function findById(int $id): array|false {
        $stmt = $this->db->prepare(self::FULL . " WHERE a.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function lockById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM blog_articles WHERE id = :id LIMIT 1 FOR UPDATE");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** @param int[] $ids */
    public function lockByIds(array $ids): array {
        $stmt = $this->db->prepare("SELECT id, status, cover_image FROM blog_articles
            WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ") FOR UPDATE");
        $stmt->execute(array_values($ids));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** $status null = everything except archived. */
    public function adminList(?string $status, ?int $categoryId, ?string $search, int $offset, int $limit): array {
        $params = [];
        $where = '1 = 1';

        if ($status === null) {
            $where .= " AND a.status <> 'archived'";
        } else {
            $where .= ' AND a.status = :status';
            $params[':status'] = $status;
        }

        if ($categoryId !== null) {
            $where .= ' AND a.category_id = :cat';
            $params[':cat'] = $categoryId;
        }

        if ($search !== null && $search !== '') {
            // Title (italic markers ignored), slug (typed with hyphens or spaces), excerpt, category.
            $where .= " AND (REPLACE(a.title, '_', '') LIKE :q1 ESCAPE '!'
                OR a.slug LIKE :q2 ESCAPE '!' OR REPLACE(a.slug, '-', ' ') LIKE :q3 ESCAPE '!'
                OR a.excerpt LIKE :q4 ESCAPE '!' OR c.label LIKE :q5 ESCAPE '!')";
            $like = self::like($search);

            foreach ([':q1', ':q2', ':q3', ':q4', ':q5'] as $name) {
                $params[$name] = $like;
            }
        }

        $stmt = $this->db->prepare(self::CARD . " WHERE $where
            ORDER BY a.updated_at DESC, a.id DESC LIMIT :limit OFFSET :offset");
        $this->bind($stmt, $params);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string,int> */
    public function statusCounts(): array {
        $counts = ['draft' => 0, 'scheduled' => 0, 'published' => 0, 'archived' => 0];

        foreach ($this->db->query("SELECT status, COUNT(*) AS n FROM blog_articles GROUP BY status") as $row) {
            $counts[$row['status']] = (int) $row['n'];
        }

        return $counts;
    }

    public function featuredRows(): array {
        return $this->db->query(self::CARD . " WHERE a.featured_position IS NOT NULL
            ORDER BY a.featured_position ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    // ---- lookups used by comments and announcements ------------------------
    public function publicIdFor(string $publicId): ?int {
        $stmt = $this->db->prepare("SELECT a.id FROM blog_articles a
            WHERE a.public_id = :pid AND " . self::PUBLIC . " LIMIT 1");
        $stmt->execute([':pid' => $publicId]);
        $id = $stmt->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    public function isPublic(int $id): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM blog_articles a WHERE a.id = :id AND " . self::PUBLIC . " LIMIT 1");
        $stmt->execute([':id' => $id]);

        return $stmt->fetchColumn() !== false;
    }

    /** Just what an announcement needs to link to a public article. */
    public function findPublicLink(int $id): array|false {
        $stmt = $this->db->prepare("SELECT a.id, a.slug, a.public_id FROM blog_articles a
            WHERE a.id = :id AND a.slug IS NOT NULL AND " . self::PUBLIC . " LIMIT 1");
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** @param int[] $ids */
    public function isLinkedFromAnnouncements(array $ids): bool {
        if (!$ids) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT 1 FROM announcements
            WHERE blog_article_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ') LIMIT 1');
        $stmt->execute(array_values($ids));

        return $stmt->fetchColumn() !== false;
    }

    public function contentOf(int $id): string {
        $stmt = $this->db->prepare('SELECT content_html FROM blog_articles WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return (string) $stmt->fetchColumn();
    }

    /** Is this uploaded file still used as a cover or inside any article's content? */
    public function imageReferenced(string $path): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM blog_articles
            WHERE cover_image = :p OR content_html LIKE :like ESCAPE '!' LIMIT 1");
        $stmt->execute([':p' => $path, ':like' => self::like($path)]);

        return $stmt->fetchColumn() !== false;
    }

    // ---- writes ------------------------------------------------------------
    public function insert(array $data): int {
        $cols = array_values(array_intersect(self::WRITABLE, array_keys($data)));
        $stmt = $this->db->prepare('INSERT INTO blog_articles (' . implode(',', $cols) . ') VALUES ('
            . implode(',', array_map(static fn (string $c): string => ':' . $c, $cols)) . ')');

        foreach ($cols as $c) {
            $stmt->bindValue(':' . $c, $data[$c], self::type($data[$c]));
        }

        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $cols = array_values(array_intersect(self::WRITABLE, array_keys($data)));

        if (!$cols) {
            return;
        }

        $stmt = $this->db->prepare('UPDATE blog_articles SET ' . implode(',', array_map(
            static fn (string $c): string => "$c = :$c", $cols)) . ' WHERE id = :__id');

        foreach ($cols as $c) {
            $stmt->bindValue(':' . $c, $data[$c], self::type($data[$c]));
        }

        $stmt->bindValue(':__id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function syncTags(int $articleId, array $names): void {
        $this->db->prepare('DELETE FROM blog_article_tags WHERE article_id = ?')->execute([$articleId]);
        $insert = $this->db->prepare('INSERT IGNORE INTO blog_tags (name) VALUES (?)');
        $select = $this->db->prepare('SELECT id FROM blog_tags WHERE name = ? LIMIT 1');
        $link = $this->db->prepare('INSERT IGNORE INTO blog_article_tags (article_id, tag_id) VALUES (?, ?)');

        foreach ($names as $name) {
            $insert->execute([$name]);
            $select->execute([$name]);
            $tagId = (int) $select->fetchColumn();

            if ($tagId > 0) {
                $link->execute([$articleId, $tagId]);
            }
        }
    }

    public function slugTaken(string $slug, ?int $ignoreId): bool {
        $stmt = $this->db->prepare('SELECT id FROM blog_articles WHERE slug = :slug LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $found = $stmt->fetchColumn();

        return $found !== false && (int) $found !== $ignoreId;
    }

    /** @return int[] ordered by position */
    public function featuredIds(bool $lock): array {
        $sql = 'SELECT id FROM blog_articles WHERE featured_position IS NOT NULL
            ORDER BY featured_position ASC' . ($lock ? ' FOR UPDATE' : '');

        return array_map('intval', $this->db->query($sql)->fetchAll(PDO::FETCH_COLUMN));
    }

    public function clearFeatured(): void {
        $this->db->exec('UPDATE blog_articles SET featured_position = NULL WHERE featured_position IS NOT NULL');
    }

    public function setFeaturedPosition(int $id, ?int $position): void {
        $stmt = $this->db->prepare('UPDATE blog_articles SET featured_position = :p WHERE id = :id');
        $stmt->bindValue(':p', $position, $position === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /** @param int[] $ids */
    public function deleteMany(array $ids): int {
        $stmt = $this->db->prepare('DELETE FROM blog_articles
            WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
        $stmt->execute(array_values($ids));
        return $stmt->rowCount();
    }

    /** One atomic statement: a due scheduled article flips exactly once, however many callers race. */
    public function publishDue(): int {
        // Autosave may have left a scheduled article half-edited: only complete ones go live.
        // An incomplete one stays scheduled (the editor shows what is missing) until it is fixed.
        return (int) $this->db->exec("UPDATE blog_articles SET status = 'published'
            WHERE status = 'scheduled' AND published_at IS NOT NULL AND 
            published_at <= UTC_TIMESTAMP() AND slug IS NOT NULL AND slug <> '' AND 
            category_id IS NOT NULL AND word_count > 0 AND CHAR_LENGTH(TRIM(excerpt)) >= 10 
            AND CHAR_LENGTH(REPLACE(title, '_', '')) >= 3")
        ;
    }

    // ---- helpers -----------------------------------------------------------
    /** @param array<string,mixed> $params filled by reference with :ex0, :ex1 ... */
    private function exclusion(array $ids, array &$params): string {
        if (!$ids) {
            return '';
        }

        $names = [];

        foreach (array_values($ids) as $i => $id) {
            $name = ':ex' . $i;
            $names[] = $name;
            $params[$name] = (int) $id;
        }

        return ' AND a.id NOT IN (' . implode(',', $names) . ')';
    }

    private function bind(\PDOStatement $stmt, array $params): void {
        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    }

    /** LIKE pattern with ! as the escape character, independent of the SQL mode. */
    private static function like(string $s): string {
        return '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $s) . '%';
    }

    private static function type(mixed $v): int {
        return $v === null ? PDO::PARAM_NULL : (is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
}