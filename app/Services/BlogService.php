<?php
namespace App\Services;

use App\Core\Logger;
use App\Database\Database;
use App\Models\BlogArticle;
use App\Models\BlogCategory;
use App\Support\BlogHtml;
use App\Support\BlogTitle;
use App\Support\FileCache;

/**
 * The one read path for the member Blog (home, lists, search, article, discovery).
 * Article cache answers "what is this article"; discovery cache answers
 * "which articles does this editorial section show".
 */
final class BlogService {
    public const PAGE_SIZE = 12;
    public const HOME_LATEST = 6;
    public const HOME_FEATURED = 3;
    public const SUGGESTIONS = 5;

    public const NS_CONTENT = 'blog-content';
    public const NS_LISTS = 'blog-lists';
    public const NS_DISCOVERY = 'blog-discovery';

    private const TTL_CONTENT = 3600;
    private const TTL_LISTS = 180;
    private const TTL_RELATED = 600;

    public function __construct(
        private BlogArticle $articles,
        private BlogCategory $categories,
        private FileCache $cache
    ) {}

    public static function make(): self {
        $db = Database::connection();

        return new self(new BlogArticle($db), new BlogCategory($db), new FileCache(STORAGE_PATH . '/cache'));
    }

    /** Generated from the current slug + the permanent public ID: renaming a slug never changes identity. */
    public static function articleUrl(array $row): string {
        return '/member/blog/post/' . $row['slug'] . '-' . $row['public_id'];
    }

    // =========================================================================
    // CATEGORIES
    // =========================================================================
    /** @return list<array{id:int,slug:string,label:string,description:string,count:int}> */
    public function categories(): array {
        return $this->cache->remember(self::NS_CONTENT, 'categories', self::TTL_CONTENT,
            fn (): array => array_map(static fn (array $r): array => [
                'id' => (int) $r['id'],
                'slug' => $r['slug'],
                'label' => $r['label'],
                'description' => $r['description'],
                'count' => (int) $r['published_count'],
            ], $this->categories->all())
        );
    }

    public function categoryBySlug(string $slug): ?array {
        foreach ($this->categories() as $category) {
            if ($category['slug'] === $slug) {
                return $category;
            }
        }

        return null;
    }

    // =========================================================================
    // HOME
    // =========================================================================
    /** @return array{featured:array,latest:array,showcases:array,more:array} */
    public function homepage(): array {
        $this->tick();

        return $this->cache->remember(self::NS_LISTS, 'home', self::TTL_LISTS, fn (): array => $this->buildHome());
    }

    private function buildHome(): array {
        $featured = array_map([self::class, 'card'], $this->articles->featuredPublic(self::HOME_FEATURED));
        $featuredIds = array_column($featured, 'id');
        $latest = array_map([self::class, 'card'],
            $this->articles->listPublic(null, null, 0, self::HOME_LATEST, $featuredIds));
        $exclude = array_merge($featuredIds, array_column($latest, 'id'));

        $showcases = $this->showcases($exclude);

        foreach ($showcases as $showcase) {
            $exclude = array_merge($exclude, array_column($showcase['articles'], 'id'));
        }

        return [
            'featured' => $featured,
            'latest' => $latest,
            'showcases' => $showcases,
            'more' => $this->moreToExplore($exclude),
        ];
    }

    /**
     * Two eligible categories (>= 1 visible article left after the Latest/Featured
     * exclusions), chosen by a day-seeded hash order. Stable for the whole UTC day,
     * rotates daily, and any article mutation drops it so it can never go stale.
     */
    private function showcases(array $exclude): array {
        $seed = gmdate('Y-m-d');

        return $this->cache->remember(self::NS_DISCOVERY, 'showcases:' . $seed, $this->untilMidnight(),
            function () use ($exclude, $seed): array {
                $ids = $this->articles->eligibleCategoryIds($exclude);
                usort($ids, static fn (int $a, int $b): int => strcmp(sha1("$seed:$a"), sha1("$seed:$b")));

                $byId = [];

                foreach ($this->categories() as $category) {
                    $byId[$category['id']] = $category;
                }

                $out = [];

                foreach (array_slice($ids, 0, 2) as $categoryId) {
                    $rows = $this->articles->listPublic($categoryId, null, 0, 3, $exclude);

                    if (!isset($byId[$categoryId]) || !$rows) {
                        continue;
                    }

                    $c = $byId[$categoryId];
                    $out[] = [
                        'category' => ['id' => $c['id'], 'slug' => $c['slug'], 'label' => $c['label']],
                        'articles' => array_map([self::class, 'card'], $rows),
                    ];
                }

                return $out;
            });
    }

    /** Needs at least 3 eligible articles, otherwise the section is hidden (empty list). */
    private function moreToExplore(array $exclude): array {
        $seed = gmdate('Y-m-d');

        return $this->cache->remember(self::NS_DISCOVERY, 'explore:' . $seed, $this->untilMidnight(),
            function () use ($exclude, $seed): array {
                $rows = $this->articles->randomPublic($exclude, 'explore:' . $seed, 3);

                return count($rows) < 3 ? [] : array_map([self::class, 'card'], $rows);
            });
    }

    // =========================================================================
    // LISTS + SEARCH (query LIMIT page + 1, return page)
    // =========================================================================
    /** @return array{items:array,has_more:bool,page:int} */
    public function list(?int $categoryId, int $page): array {
        $this->tick();
        $page = max(1, min(500, $page));
        $compute = fn (): array => $this->pageOf($categoryId, null, $page);

        // Only first pages are cached: the key space is bounded by the number of real categories.
        if ($page > 1) {
            return $compute();
        }

        return $this->cache->remember(self::NS_LISTS, 'list:' . ($categoryId ?? 'all') . ':1',
            self::TTL_LISTS, $compute);
    }

    /** Never cached: arbitrary search strings must not create cache entries. */
    public function search(string $query, int $page): array {
        $this->tick();

        return $this->pageOf(null, $query, max(1, min(500, $page)));
    }

    public function suggest(string $query): array {
        $rows = $this->articles->listPublic(null, $query, 0, self::SUGGESTIONS);

        return array_map(static function (array $row): array {
            $card = self::card($row);

            return ['title' => $card['title'], 'category' => $card['category'], 'url' => $card['url']];
        }, $rows);
    }

    private function pageOf(?int $categoryId, ?string $query, int $page): array {
        $rows = $this->articles->listPublic($categoryId, $query, ($page - 1) * self::PAGE_SIZE,
            self::PAGE_SIZE + 1);
        $hasMore = count($rows) > self::PAGE_SIZE;

        return [
            'items' => array_map([self::class, 'card'], array_slice($rows, 0, self::PAGE_SIZE)),
            'has_more' => $hasMore,
            'page' => $page,
        ];
    }

    // =========================================================================
    // SINGLE ARTICLE
    // =========================================================================
    public function article(string $publicId): ?array {
        if (!preg_match('/^[a-z0-9]{8}$/', $publicId)) {
            return null;
        }

        $this->tick();

        // Misses are never stored, so random ids cannot grow the cache.
        return $this->cache->remember(self::NS_CONTENT, 'article:' . $publicId, self::TTL_CONTENT,
            fn (): ?array => $this->loadArticle($publicId),
            static fn (mixed $value): bool => $value !== null
        );
    }

    private function loadArticle(string $publicId): ?array {
        $row = $this->articles->findPublicByPublicId($publicId);

        if ($row === false) {
            return null;
        }

        $id = (int) $row['id'];
        $stored = (string) $row['content_html'];
        $published = (int) strtotime($row['published_at'] . ' UTC');
        $updated = (int) strtotime($row['updated_at'] . ' UTC');

        return self::card($row) + [
            'content_html' => BlogHtml::decorate($stored),
            'toc' => BlogHtml::toc($stored),
            'tags' => $this->articles->tagsFor([$id])[$id] ?? [],
            'word_count' => (int) $row['word_count'],
            // "Updated" only when edited meaningfully after publication.
            'updated_at' => ($updated - $published) > 3600 ? $row['updated_at'] : null,
        ];
    }

    /** More from {category}: only the current article is excluded. */
    public function moreFromCategory(array $article): array {
        if (empty($article['category_id'])) {
            return [];
        }

        return $this->cache->remember(self::NS_DISCOVERY, 'from-category:' . $article['id'], self::TTL_RELATED,
            fn (): array => array_map([self::class, 'card'],
                $this->articles->listPublic((int) $article['category_id'], null, 0, 3, [(int) $article['id']]))
        );
    }

    /** More to Explore on an article page: only the current article is excluded. */
    public function moreToExploreFor(array $article): array {
        $seed = 'explore-article:' . gmdate('Y-m-d') . ':' . $article['id'];

        return $this->cache->remember(self::NS_DISCOVERY, 'explore-article:' . $article['id'], self::TTL_RELATED,
            fn (): array => array_map([self::class, 'card'],
                $this->articles->randomPublic([(int) $article['id']], $seed, 3))
        );
    }

    // =========================================================================
    // PRESENTATION
    // =========================================================================
    /** Card shape the existing partials use, plus title_html (safe to echo) and url. */
    public static function card(array $r): array {
        $source = (string) $r['title'];

        return [
            'id' => (int) $r['id'],
            'public_id' => $r['public_id'],
            'slug' => $r['slug'],
            'title' => BlogTitle::plain($source),
            'title_html' => BlogTitle::html($source),
            'title_source' => $source,
            'excerpt' => $r['excerpt'],
            'category' => (string) ($r['category_label'] ?? ''),
            'category_id' => $r['category_id'] !== null ? (int) $r['category_id'] : null,
            'category_slug' => $r['category_slug'] ?? null,
            'image' => AnnouncementService::resolveImageUrl($r['cover_image'] ?? null),
            'published_at' => $r['published_at'],
            'reading_time' => ((int) $r['reading_time_minutes']) . ' min read',
            'featured' => $r['featured_position'] !== null,
            'featured_position' => $r['featured_position'] !== null ? (int) $r['featured_position'] : null,
            'author' => ($r['author_name'] ?? '') !== '' ? $r['author_name'] : 'Anime Nigeria Editorial',
            'url' => !empty($r['slug']) ? self::articleUrl($r) : '#',
        ];
    }

    private function untilMidnight(): int {
        $now = time();

        return max(60, ((int) (floor($now / 86400) + 1)) * 86400 - $now);
    }

    /**
     * Safety net for scheduled publishing when cron is not running: at most one
     * cheap UPDATE per 30 seconds, then the caches are invalidated by publishDue().
     */
    private function tick(): void {
        try {
            $this->cache->remember('blog-sched', 'tick', 30,
                static fn (): int => BlogAdminService::make()->publishDue());
        } catch (\Throwable $e) {
            Logger::error($e);
        }
    }
}