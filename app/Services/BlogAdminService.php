<?php
namespace App\Services;

use App\Core\Logger;
use App\Database\Database;
use App\Models\BlogArticle;
use App\Models\BlogCategory;
use App\Support\BlogHtml;
use App\Support\BlogTitle;
use App\Support\FileCache;
use App\Support\ImageUpload;
use PDO;

final class BlogAdminService {
    public const MAX_FEATURED = 3;
    public const ADMIN_PAGE_SIZE = 20;

    private const IMAGE_MAX_BYTES = 5 * 1024 * 1024;
    private const MAX_BULK = 200;
    private const MAX_TAGS = 8;
    private const STATUSES = ['draft', 'scheduled', 'published', 'archived'];
    private const LABEL_PATTERN = '/^[\p{L}\p{N}][\p{L}\p{N} &\'’().,\/-]*$/u';
    private const TAG_PATTERN = '/^[\p{L}\p{N}][\p{L}\p{N} &+#.\-]*$/u';

    public function __construct(
        private PDO $db,
        private BlogArticle $articles,
        private BlogCategory $categories,
        private FileCache $cache,
        private ImageUpload $images
    ) {}

    public static function make(): self {
        $db = Database::connection();

        return new self($db, new BlogArticle($db), new BlogCategory($db),
            new FileCache(STORAGE_PATH . '/cache'), new ImageUpload('blog', self::IMAGE_MAX_BYTES));
    }

    // =========================================================================
    // READS
    // =========================================================================
    public function list(array $q): array {
        $status = isset($q['status']) && in_array($q['status'], self::STATUSES, true) ? $q['status'] : null;
        $category = filter_var($q['category'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $search = isset($q['q']) && is_string($q['q']) ? mb_substr(trim($q['q']), 0, 100) : null;
        $page = max(1, min(1000, (int) ($q['page'] ?? 1)));

        $rows = $this->articles->adminList($status, $category === false ? null : $category, $search,
            ($page - 1) * self::ADMIN_PAGE_SIZE, self::ADMIN_PAGE_SIZE + 1); // +1 look-ahead

        return [
            'success' => true,
            'items' => array_map([$this, 'adminCard'], array_slice($rows, 0, self::ADMIN_PAGE_SIZE)),
            'has_more' => count($rows) > self::ADMIN_PAGE_SIZE,
            'page' => $page,
            'counts' => $this->articles->statusCounts(),
        ];
    }

    public function featuredList(): array {
        return array_map([$this, 'adminCard'], $this->articles->featuredRows());
    }

    /** Published articles for the announcement picker. Same visibility and search as the member Blog. */
    public function pickerList(mixed $q): array {
        $search = is_string($q) ? mb_substr(trim($q), 0, 100) : null;
        $rows = $this->articles->listPublic(null, $search, 0, 11); // 10 + 1 look-ahead

        return [
            'success' => true,
            'has_more' => count($rows) > 10,
            'items' => array_map(static fn (array $r): array => [
                'id' => (int) $r['id'],
                'title' => BlogTitle::plain((string) $r['title']),
                'category' => $r['category_label'] ?? null,
                'published_at' => self::iso($r['published_at']),
                'url' => BlogService::articleUrl($r),
            ], array_slice($rows, 0, 10)),
        ];
    }

    public function get(int $id): array {
        return $this->present($id) ?? $this->fail('That article no longer exists.', 404);
    }

    // =========================================================================
    // CREATE / SAVE / AUTOSAVE
    // =========================================================================
    public function create(array $input, int $userId): array {
        $parsed = $this->collect($input, null);

        if ($parsed['errors']) {
            return $this->invalid($parsed['errors']);
        }

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->db->beginTransaction();

            try {
                $data = $parsed['changes'] + ['title' => '', 'excerpt' => '', 'content_html' => '',
                    'word_count' => 0, 'reading_time_minutes' => 1];
                $data['status'] = 'draft';
                $data['author_id'] = $userId;
                $data['public_id'] = $this->generatePublicId();

                $slug = $this->resolveSlug($parsed['slug'], (string) $data['title'], null, null);

                if ($slug !== null) {
                    $data['slug'] = $slug;
                }

                $id = $this->articles->insert($data);

                if ($parsed['tags'] !== null) {
                    $this->articles->syncTags($id, $parsed['tags']);
                }

                $this->db->commit();

                return $this->present($id, 'Draft created.');
            } catch (\PDOException $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }

                if (!$this->isDuplicate($e)) {
                    throw $e;
                }
                // public_id or slug collision under a race: retry with fresh values
            }
        }

        return $this->fail('Could not create the article. Please try again.', 500);
    }

    /**
     * mode 'autosave': draft/scheduled only, silent, never touches status or schedule.
     * mode 'save':     explicit Save. Works on published articles too (and then
     *                  must remain publishable), still never changes the lifecycle.
     */
    public function save(int $id, array $input, string $mode): array {
        $this->db->beginTransaction();

        try {
            $row = $this->articles->lockById($id);

            if ($row === false) {
                $this->db->rollBack();
                return $this->fail('That article no longer exists.', 404);
            }

            if ($row['status'] === 'archived') {
                $this->db->rollBack();
                return $this->fail('Restore this article before editing it.', 409);
            }

            if ($mode === 'autosave' && $row['status'] === 'published') {
                $this->db->rollBack();
                return $this->fail('Published articles are never autosaved. Use Save.', 409);
            }

            $parsed = $this->collect($input, $row);
            $changes = $parsed['changes'];
            $errors = $parsed['errors'];

            $slug = $this->resolveSlug($parsed['slug'], (string) ($changes['title'] ?? $row['title']), $row, $id);

            if ($slug !== null) {
                $changes['slug'] = $slug;
            }

            if ($mode === 'save' && $row['status'] === 'published') {
                $errors += $this->publishErrors(array_merge($row, $changes));
            }

            if ($errors) {
                $this->db->rollBack();
                return $this->invalid($errors);
            }

            // status and published_at are deliberately absent from $changes.
            $this->articles->update($id, $changes);

            if ($parsed['tags'] !== null) {
                $this->articles->syncTags($id, $parsed['tags']);
            }

            if (array_key_exists('featured', $input)) {
                $error = $this->applyFeatured($id, filter_var($input['featured'], FILTER_VALIDATE_BOOLEAN));

                if ($error !== null) {
                    $this->db->rollBack();
                    return $error;
                }
            }

            $this->db->commit();
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            if ($this->isDuplicate($e)) {
                return $this->invalid(['slug' => 'That address was just taken. Save again.']);
            }

            throw $e;
        }

        if ($row['status'] === 'published') {
            $this->invalidate(); // drafts and scheduled articles are not public: no cache churn

            // A renamed slug changes the URL announcements generate for this article.
            if (isset($changes['slug']) && $changes['slug'] !== $row['slug']
                && $this->articles->isLinkedFromAnnouncements([$id])
            ) {
                $this->invalidateAnnouncements();
            }
        }

        return $this->present($id) + ['saved_at' => self::iso(self::now())];
    }

    // =========================================================================
    // LIFECYCLE
    // =========================================================================
    public function publish(int $id): array {
        $this->db->beginTransaction();

        try {
            $row = $this->articles->lockById($id);

            if ($row === false) {
                $this->db->rollBack();
                return $this->fail('That article no longer exists.', 404);
            }

            if ($row['status'] === 'archived') {
                $this->db->rollBack();
                return $this->fail('Restore this article before publishing it.', 409);
            }

            if ($row['status'] === 'published') {
                $this->db->rollBack();
                return $this->present($id);
            }

            if (empty($row['slug'])) {
                $row['slug'] = $this->resolveSlug(null, (string) $row['title'], $row, $id);
            }

            $errors = $this->publishErrors($row);

            if ($errors) {
                $this->db->rollBack();
                return $this->invalid($errors);
            }

            $this->articles->update($id, ['slug' => $row['slug'], 'status' => 'published',
                'published_at' => self::now()]);
            $this->db->commit();
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }

        $this->invalidate();

        return $this->present($id, 'Article published.');
    }

    public function schedule(int $id, mixed $when): array {
        try {
            $at = (new \DateTimeImmutable((string) $when, new \DateTimeZone('UTC')))
                ->setTimezone(new \DateTimeZone('UTC'));
        } catch (\Throwable) {
            return $this->invalid(['scheduled_at' => 'Choose a valid date and time.']);
        }

        $now = time();

        if ($at->getTimestamp() <= $now + 30 || $at->getTimestamp() > $now + 5 * 365 * 86400) {
            return $this->invalid(['scheduled_at' => 'Choose a time in the future (within 5 years).']);
        }

        $this->db->beginTransaction();

        try {
            $row = $this->articles->lockById($id);

            if ($row === false) {
                $this->db->rollBack();
                return $this->fail('That article no longer exists.', 404);
            }

            if (!in_array($row['status'], ['draft', 'scheduled'], true)) {
                $this->db->rollBack();
                return $this->fail('Only drafts can be scheduled.', 409);
            }

            if (empty($row['slug'])) {
                $row['slug'] = $this->resolveSlug(null, (string) $row['title'], $row, $id);
            }

            $errors = $this->publishErrors($row);

            if ($errors) {
                $this->db->rollBack();
                return $this->invalid($errors);
            }

            $this->articles->update($id, ['slug' => $row['slug'], 'status' => 'scheduled',
                'published_at' => $at->format('Y-m-d H:i:s')]);
            $this->db->commit();
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }

        return $this->present($id, 'Article scheduled.'); // not public yet: nothing to invalidate
    }

    public function unschedule(int $id): array {
        $this->db->beginTransaction();

        try {
            $row = $this->articles->lockById($id);

            if ($row === false || $row['status'] !== 'scheduled') {
                $this->db->rollBack();
                return $this->fail('That article is not scheduled.', 409);
            }

            $this->articles->update($id, ['status' => 'draft', 'published_at' => null]);
            $this->db->commit();
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }

        return $this->present($id, 'Moved back to drafts.');
    }

    /** Called by the cron script and by the throttled lazy check. */
    public function publishDue(): int {
        $count = $this->articles->publishDue();

        if ($count > 0) {
            $this->invalidate();
        }

        return $count;
    }

    // =========================================================================
    // FEATURED (database is the source of truth; positions are always 1..n)
    // =========================================================================
    public function setFeatured(int $id, bool $on): array {
        $this->db->beginTransaction();

        try {
            $row = $this->articles->lockById($id);

            if ($row === false) {
                $this->db->rollBack();
                return $this->fail('That article no longer exists.', 404);
            }

            if ($row['status'] === 'archived') {
                $this->db->rollBack();
                return $this->fail('Archived articles cannot be featured.', 409);
            }

            $error = $this->applyFeatured($id, $on);

            if ($error !== null) {
                $this->db->rollBack();
                return $error;
            }

            $this->db->commit();
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            if ($this->isDuplicate($e)) {
                return $this->fail('Featured positions changed. Refresh and try again.', 409);
            }

            throw $e;
        }

        $this->invalidate();

        return ['success' => true, 'featured' => $this->featuredList()];
    }

    /** $ids must be exactly the current featured set in the desired order. */
    public function reorderFeatured(mixed $ids): array {
        $ids = $this->ids($ids);

        $this->db->beginTransaction();

        try {
            $current = $this->articles->featuredIds(true);

            if (count($ids) !== count($current) || array_diff($ids, $current) || array_diff($current, $ids)) {
                $this->db->rollBack();
                return $this->fail('The featured list changed. Refresh and try again.', 409);
            }

            $this->articles->clearFeatured(); // the unique key forbids interim duplicates

            foreach ($ids as $index => $id) {
                $this->articles->setFeaturedPosition($id, $index + 1);
            }

            $this->db->commit();
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }

        $this->invalidate();

        return ['success' => true, 'featured' => $this->featuredList()];
    }

    private function applyFeatured(int $id, bool $on): ?array {
        $current = $this->articles->featuredIds(true);
        $has = in_array($id, $current, true);

        if ($on && !$has) {
            if (count($current) >= self::MAX_FEATURED) {
                return $this->invalid(['featured' => 'Only ' . self::MAX_FEATURED
                    . ' articles can be featured. Remove one first.']);
            }

            $this->articles->setFeaturedPosition($id, count($current) + 1);
        } elseif (!$on && $has) {
            $this->articles->setFeaturedPosition($id, null);
            $this->normalizeFeatured();
        }

        return null;
    }

    private function normalizeFeatured(): void {
        $ids = $this->articles->featuredIds(true);
        $this->articles->clearFeatured();

        foreach ($ids as $index => $id) {
            $this->articles->setFeaturedPosition($id, $index + 1);
        }
    }

    // =========================================================================
    // ARCHIVE / RESTORE / DELETE (admin only: enforced in the API)
    // =========================================================================
    public function archive(mixed $ids): array {
        $ids = $this->ids($ids);

        if (!$ids) {
            return $this->fail('No articles were selected.');
        }

        $count = 0;
        $archivedIds = [];
        $this->db->beginTransaction();

        try {
            foreach ($this->articles->lockByIds($ids) as $row) {
                if ($row['status'] === 'archived') {
                    continue;
                }

                $this->articles->update((int) $row['id'], ['status' => 'archived',
                    'archived_at' => self::now(), 'featured_position' => null]);
                $count++;
                $archivedIds[] = (int) $row['id'];
            }

            $this->normalizeFeatured();
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }

        $this->invalidate();

        if ($archivedIds && $this->articles->isLinkedFromAnnouncements($archivedIds)) {
            $this->invalidateAnnouncements(); // the link falls back to the stored URL
        }

        return ['success' => true, 'archived' => $count, 'message' => 'Articles archived.'];
    }

    /** Restore always returns an article to draft, never to its previous state. */
    public function restore(mixed $ids): array {
        $ids = $this->ids($ids);

        if (!$ids) {
            return $this->fail('No articles were selected.');
        }

        $count = 0;
        $this->db->beginTransaction();

        try {
            foreach ($this->articles->lockByIds($ids) as $row) {
                if ($row['status'] !== 'archived') {
                    continue;
                }

                $this->articles->update((int) $row['id'], ['status' => 'draft', 'archived_at' => null,
                    'published_at' => null]);
                $count++;
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }

        return ['success' => true, 'restored' => $count, 'message' => 'Articles restored as drafts.'];
    }

    /** Irreversible. Archived articles only. */
    /** Irreversible. Archived articles only. Tags and comments go with the row (foreign keys). */
    public function deletePermanent(mixed $ids): array {
        $ids = $this->ids($ids);

        if (!$ids) {
            return $this->fail('No articles were selected.');
        }

        $images = [];
        $this->db->beginTransaction();

        try {
            $rows = array_filter($this->articles->lockByIds($ids),
                static fn (array $r): bool => $r['status'] === 'archived');

            if (!$rows) {
                $this->db->rollBack();
                return $this->fail('Only archived articles can be permanently deleted.', 409);
            }

            $targets = array_map(static fn (array $r): int => (int) $r['id'], $rows);
            $linked = $this->articles->isLinkedFromAnnouncements($targets);

            foreach ($rows as $row) {
                if (!empty($row['cover_image'])) {
                    $images[$row['cover_image']] = true;
                }
            }

            foreach ($targets as $targetId) {
                foreach (self::uploadedImages($this->articles->contentOf($targetId)) as $path) {
                    $images[$path] = true;
                }
            }

            $deleted = $this->articles->deleteMany($targets);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }

        // Files go only after the commit, and only if no remaining article still uses them.
        // ImageUpload::delete() ignores anything it did not create (seed artwork is safe).
        foreach (array_keys($images) as $path) {
            if (!$this->articles->imageReferenced($path)) {
                $this->images->delete($path);
            }
        }

        foreach ($targets as $targetId) {
            try {
                $this->cache->invalidate(BlogCommentService::cacheNamespace($targetId));
            } catch (\Throwable $e) {
                Logger::error($e);
            }
        }

        if ($linked) {
            $this->invalidateAnnouncements();
        }

        $this->invalidate();

        return ['success' => true, 'deleted' => $deleted, 'message' => 'Articles deleted.'];
    }

    // =========================================================================
    // IMAGES (cover + Tiptap content images upload here, then reference the URL)
    // =========================================================================
    public function uploadImage(array $files): array {
        if (!$this->images->hasUpload($files, 'image')) {
            return $this->invalid(['image' => 'Choose an image.']);
        }

        $error = $this->images->inspect($files['image']);

        if ($error !== null) {
            return $this->invalid(['image' => $error]);
        }

        $stored = $this->images->store($files['image']);

        if ($stored === null) {
            return $this->invalid(['image' => 'Unable to save the image. Please try again.']);
        }

        return ['success' => true, 'url' => $stored['publicPath']];
    }

        /** Blog images: seed artwork in /uploads, files uploaded through the blog, or http(s). Nothing else in storage. */
    private static function resolveImage(?string $src): ?string {
        $src = trim((string) $src);
        $allowed = preg_match('#^(https?://|/uploads/|/storage/uploads/blog/[a-f0-9]{32}\.(png|jpg|webp)$)#i', $src) === 1;

        return $allowed ? AnnouncementService::resolveImageUrl($src) : null;
    }

    /** @return string[] blog uploads referenced by a stored HTML body */
    private static function uploadedImages(string $html): array {
        preg_match_all('#/storage/uploads/blog/[a-f0-9]{32}\.(?:png|jpg|webp)#', $html, $m);

        return array_values(array_unique($m[0]));
    }

    private function invalidateAnnouncements(): void {
        try {
            $this->cache->invalidate(AnnouncementService::CACHE_NAMESPACE);
        } catch (\Throwable $e) {
            Logger::error($e);
        }
    }

    // =========================================================================
    // CATEGORIES
    // =========================================================================
    public function listCategories(): array {
        return array_map(static fn (array $r): array => [
            'id' => (int) $r['id'], 'slug' => $r['slug'], 'label' => $r['label'],
            'description' => $r['description'], 'published_count' => (int) $r['published_count'],
            'total_count' => (int) $r['total_count'],
        ], $this->categories->all());
    }

    public function createCategory(array $in): array {
        [$label, $description, $errors] = $this->categoryFields($in, null);

        if ($errors) {
            return $this->invalid($errors);
        }

        $base = BlogTitle::slugify($label) ?: 'category';
        $slug = $base;
        $n = 2;

        while ($this->categories->slugExists($slug)) {
            $slug = $base . '-' . $n++;
        }

        try {
            $id = $this->categories->create($slug, $label, $description);
        } catch (\PDOException $e) {
            if ($this->isDuplicate($e)) {
                return $this->invalid(['label' => 'A category with this name already exists.']);
            }

            throw $e;
        }

        $this->invalidate();

        return ['success' => true, 'message' => 'Category created.', 'category' => ['id' => $id, 'slug' => $slug,
            'label' => $label, 'description' => $description]];
    }

    /** The slug is permanent so category URLs never break on rename. */
    public function updateCategory(int $id, array $in): array {
        if ($this->categories->findById($id) === false) {
            return $this->fail('That category no longer exists.', 404);
        }

        [$label, $description, $errors] = $this->categoryFields($in, $id);

        if ($errors) {
            return $this->invalid($errors);
        }

        try {
            $this->categories->update($id, $label, $description);
        } catch (\PDOException $e) {
            if ($this->isDuplicate($e)) {
                return $this->invalid(['label' => 'A category with this name already exists.']);
            }

            throw $e;
        }

        $this->invalidate();

        return ['success' => true, 'message' => 'Category updated.'];
    }

    /** Articles in use are moved to an explicit destination first, in one transaction. */
    public function deleteCategories(mixed $ids, mixed $reassignTo): array {
        $ids = $this->ids($ids);

        if (!$ids) {
            return $this->fail('No categories were selected.');
        }

        $target = null;

        if ($reassignTo !== null && $reassignTo !== '') {
            $target = filter_var($reassignTo, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($target === false || in_array($target, $ids, true)) {
                return $this->fail('Choose a valid category to move the articles to.');
            }
        }

        $this->db->beginTransaction();

        try {
            if (count($this->categories->lockByIds($ids)) !== count($ids)) {
                $this->db->rollBack();
                return $this->fail('One or more categories no longer exist. Refresh and try again.', 409);
            }

            $moved = 0;

            if ($this->categories->countArticles($ids) > 0) {
                if ($target === null) {
                    $this->db->rollBack();
                    return $this->fail('These categories still have articles. Choose where to move them first.',
                        422, ['code' => 'reassign_required']);
                }

                if (count($this->categories->lockByIds([$target])) !== 1) {
                    $this->db->rollBack();
                    return $this->fail('The category you chose no longer exists.');
                }

                $moved = $this->categories->reassignArticles($ids, $target);
            }

            $deleted = $this->categories->deleteMany($ids);
            $this->db->commit();
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            if (($e->errorInfo[0] ?? '') === '23000') {
                return $this->fail('A category is still in use. Refresh and try again.', 409);
            }

            throw $e;
        }

        $this->invalidate();

        return ['success' => true, 'deleted' => $deleted, 'moved' => $moved, 'message' => 'Categories deleted.'];
    }

    // =========================================================================
    // IMPORT (used by database/seeders/import-blog-mock.php)
    // =========================================================================
    public function importPublished(array $d): int {
        $analysis = BlogHtml::process((string) $d['content_html'],
            static fn (string $s): ?string => self::resolveImage($s));

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->db->beginTransaction();

            try {
                $id = $this->articles->insert([
                    'public_id' => $this->generatePublicId(),
                    'slug' => $this->resolveSlug(null, (string) $d['title'], null, null),
                    'title' => (string) $d['title'],
                    'excerpt' => (string) $d['excerpt'],
                    'category_id' => $d['category_id'],
                    'cover_image' => $d['cover_image'] ?? null,
                    'content_html' => $analysis['html'],
                    'word_count' => $analysis['words'],
                    'reading_time_minutes' => BlogHtml::readingMinutes($analysis['words']),
                    'status' => 'published',
                    'published_at' => $d['published_at'],
                    'featured_position' => $d['featured_position'] ?? null,
                ]);
                $this->articles->syncTags($id, $d['tags'] ?? []);
                $this->db->commit();
                $this->invalidate();

                return $id;
            } catch (\PDOException $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }

                if (!$this->isDuplicate($e)) {
                    throw $e;
                }
            }
        }

        throw new \RuntimeException('Could not import article: ' . $d['title']);
    }

    // =========================================================================
    // VALIDATION
    // =========================================================================
    /**
     * Only keys present in $in are read, so PATCH/autosave can send partial payloads.
     *
     * @return array{changes:array,errors:array,tags:?array,slug:?string}
     */
    private function collect(array $in, ?array $existing): array {
        $c = [];
        $e = [];
        $tags = null;
        $slug = null;

        if (array_key_exists('title', $in)) {
            $v = $this->clean($in['title']);

            if (mb_strlen($v) > 160) {
                $e['title'] = 'Title must not exceed 160 characters.';
            } else {
                $c['title'] = $v;
            }
        }

        if (array_key_exists('excerpt', $in)) {
            $v = $this->clean($in['excerpt']);

            if (mb_strlen($v) > 240) {
                $e['excerpt'] = 'Excerpt must not exceed 240 characters.';
            } else {
                $c['excerpt'] = $v;
            }
        }

        if (array_key_exists('category_id', $in)) {
            if ($in['category_id'] === null || $in['category_id'] === '') {
                $c['category_id'] = null;
            } else {
                $id = filter_var($in['category_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                if ($id === false || $this->categories->findById($id) === false) {
                    $e['category_id'] = 'That category does not exist.';
                } else {
                    $c['category_id'] = $id;
                }
            }
        }

        if (array_key_exists('cover_image', $in)) {
            $v = trim((string) $in['cover_image']);

            if ($v === '') {
                $c['cover_image'] = null;
            } elseif (strlen($v) > 500 || self::resolveImage($v) === null) {
                $e['cover_image'] = 'That image is not available.';
            } else {
                $c['cover_image'] = $v;
            }
        }

        if (array_key_exists('cover_image_alt', $in)) {
            $v = $this->clean($in['cover_image_alt']);

            if (mb_strlen($v) > 200) {
                $e['cover_image_alt'] = 'Alt text must not exceed 200 characters.';
            } else {
                $c['cover_image_alt'] = $v === '' ? null : $v;
            }
        }

        if (array_key_exists('content_html', $in)) {
            $raw = (string) $in['content_html'];

            if (strlen($raw) > BlogHtml::MAX_BYTES) {
                $e['content_html'] = 'The article is too long.';
            } else {
                $r = BlogHtml::process($raw,
                    static fn (string $s): ?string => self::resolveImage($s));
                $c['content_html'] = $r['html'];
                $c['word_count'] = $r['words'];
                $c['reading_time_minutes'] = BlogHtml::readingMinutes($r['words']); // system-managed
            }
        }

        if (array_key_exists('tags', $in)) {
            [$tags, $tagError] = $this->normalizeTags($in['tags']);

            if ($tagError !== null) {
                $e['tags'] = $tagError;
            }
        }

        if (array_key_exists('slug', $in) && trim((string) $in['slug']) !== '') {
            $s = BlogTitle::slugify((string) $in['slug']);

            if ($s === '') {
                $e['slug'] = 'Use letters, numbers and hyphens.';
            } else {
                $slug = substr($s, 0, 150);
            }
        }

        return ['changes' => $c, 'errors' => $e, 'tags' => $tags, 'slug' => $slug];
    }

    /** What an article needs before it can go public (publish, schedule, explicit Save of a published one). */
    private function publishErrors(array $r): array {
        $e = [];

        if (mb_strlen(BlogTitle::plain(trim((string) $r['title']))) < 3) {
            $e['title'] = 'Add a title (at least 3 characters).';
        }

        if (empty($r['slug'])) {
            $e['slug'] = 'Add a URL slug.';
        }

        if (mb_strlen(trim((string) $r['excerpt'])) < 10) {
            $e['excerpt'] = 'Add an excerpt (at least 10 characters).';
        }

        if (empty($r['category_id']) || $this->categories->findById((int) $r['category_id']) === false) {
            $e['category_id'] = 'Choose a category.';
        }

        if ((int) $r['word_count'] < 1) {
            $e['content_html'] = 'Write some content first.';
        }

        return $e;
    }

    private function normalizeTags(mixed $raw): array {
        if (!is_array($raw)) {
            return [null, 'Tags must be a list.'];
        }

        $out = [];
        $seen = [];

        foreach ($raw as $tag) {
            $name = $this->clean(is_scalar($tag) ? $tag : '');

            if ($name === '') {
                continue;
            }

            if (mb_strlen($name) < 2 || mb_strlen($name) > 30 || !preg_match(self::TAG_PATTERN, $name)) {
                return [null, 'Tags are 2 to 30 characters: letters, numbers, spaces and & + # . -'];
            }

            $key = mb_strtolower($name);

            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $out[] = $name;
            }
        }

        return count($out) > self::MAX_TAGS ? [null, 'Use at most ' . self::MAX_TAGS . ' tags.'] : [$out, null];
    }

    private function categoryFields(array $in, ?int $ignoreId): array {
        $label = $this->clean($in['label'] ?? '');
        $description = $this->clean($in['description'] ?? '');
        $e = [];

        if (mb_strlen($label) < 2 || mb_strlen($label) > 40) {
            $e['label'] = 'Name must be between 2 and 40 characters.';
        } elseif (!preg_match(self::LABEL_PATTERN, $label)) {
            $e['label'] = 'Use letters, numbers, spaces and basic punctuation only.';
        } else {
            $found = $this->categories->findByLabel($label);

            if ($found !== false && (int) $found['id'] !== $ignoreId) {
                $e['label'] = 'A category with this name already exists.';
            }
        }

        if (mb_strlen($description) > 240) {
            $e['description'] = 'Description must not exceed 240 characters.';
        }

        return [$label, $description, $e];
    }

    // =========================================================================
    // HELPERS
    // =========================================================================
    /**
     * Slug only changes when one is supplied, or when the article has none yet.
     * Always unique: a collision gets -2, -3 ... and the final slug is returned to the UI.
     */
    private function resolveSlug(?string $wanted, string $title, ?array $existing, ?int $ignoreId): ?string {
        if ($wanted !== null) {
            $base = $wanted;
        } elseif (empty($existing['slug'] ?? null) && trim($title) !== '') {
            $base = BlogTitle::slugFromTitle($title);
        } else {
            return null;
        }

        $base = substr($base, 0, 150);

        if ($base === '') {
            return null;
        }

        $slug = $base;
        $n = 2;

        while ($this->articles->slugTaken($slug, $ignoreId)) {
            $slug = $base . '-' . $n++;

            if ($n > 60) {
                return $base . '-' . bin2hex(random_bytes(3));
            }
        }

        return $slug;
    }

    /** 8 characters from [a-z0-9]: random, URL-safe, independent of id and content. Uniqueness is enforced by the unique key. */
    private function generatePublicId(): string {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $id = '';

        for ($i = 0; $i < 8; $i++) {
            $id .= $alphabet[random_int(0, 35)];
        }

        return $id;
    }

    private function present(int $id, ?string $message = null): ?array {
        $row = $this->articles->findById($id);

        if ($row === false) {
            return null;
        }

        $out = ['success' => true, 'article' => [
            'id' => (int) $row['id'], 'public_id' => $row['public_id'], 'slug' => $row['slug'],
            'title' => $row['title'], 'excerpt' => $row['excerpt'],
            'category_id' => $row['category_id'] !== null ? (int) $row['category_id'] : null,
            'tags' => $this->articles->tagsFor([$id])[$id] ?? [],
            'cover_image' => $row['cover_image'],
            'cover_image_url' => AnnouncementService::resolveImageUrl($row['cover_image']),
            'cover_image_alt' => $row['cover_image_alt'],
            'content_html' => $row['content_html'],
            'word_count' => (int) $row['word_count'],
            'reading_time_minutes' => (int) $row['reading_time_minutes'],
            'status' => $row['status'],
            'published_at' => self::iso($row['published_at']),
            'featured_position' => $row['featured_position'] !== null ? (int) $row['featured_position'] : null,
            'updated_at' => self::iso($row['updated_at']),
            'url' => ($row['status'] === 'published' && $row['slug']) ? BlogService::articleUrl($row) : null,
        ]];

        return $message !== null ? $out + ['message' => $message] : $out;
    }

    private function adminCard(array $r): array {
        return [
            'id' => (int) $r['id'], 'public_id' => $r['public_id'], 'slug' => $r['slug'],
            'title' => $r['title'], 'title_plain' => BlogTitle::plain((string) $r['title']),
            'status' => $r['status'],
            'category_id' => $r['category_id'] !== null ? (int) $r['category_id'] : null,
            'category' => $r['category_label'] ?? null,
            'published_at' => self::iso($r['published_at']),
            'updated_at' => self::iso($r['updated_at']),
            'reading_minutes' => (int) $r['reading_time_minutes'],
            'word_count' => (int) $r['word_count'],
            'featured_position' => $r['featured_position'] !== null ? (int) $r['featured_position'] : null,
            'author' => ($r['author_name'] ?? '') !== '' ? $r['author_name'] : null,
            'cover_image_url' => AnnouncementService::resolveImageUrl($r['cover_image'] ?? null),
            'url' => ($r['status'] === 'published' && $r['slug']) ? BlogService::articleUrl($r) : null,
        ];
    }

    /** Drops every blog read cache (content, lists, discovery). Never throws. */
    private function invalidate(): void {
        foreach ([BlogService::NS_CONTENT, BlogService::NS_LISTS, BlogService::NS_DISCOVERY] as $namespace) {
            try {
                $this->cache->invalidate($namespace);
            } catch (\Throwable $e) {
                Logger::error($e);
            }
        }
    }

    private function clean(mixed $value): string {
        return trim((string) preg_replace('/\s+/u', ' ', (string) $value));
    }

    /** @return int[] */
    private function ids(mixed $ids): array {
        if (!is_array($ids)) {
            return [];
        }

        $clean = [];

        foreach ($ids as $id) {
            if ((is_int($id) || (is_string($id) && ctype_digit($id))) && (int) $id > 0) {
                $clean[(int) $id] = (int) $id;
            }
        }

        return array_slice(array_values($clean), 0, self::MAX_BULK);
    }

    private function isDuplicate(\PDOException $e): bool {
        return ($e->errorInfo[0] ?? '') === '23000' && (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private static function now(): string {
        return gmdate('Y-m-d H:i:s');
    }

    private static function iso(?string $utc): ?string {
        return $utc === null ? null : (new \DateTimeImmutable($utc, new \DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z');
    }

    private function invalid(array $errors): array {
        return ['success' => false, 'status' => 422, 'errors' => $errors];
    }

    private function fail(string $message, int $status = 422, array $extra = []): array {
        return ['success' => false, 'status' => $status, 'message' => $message] + $extra;
    }
}