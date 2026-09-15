<?php
// Pure helper functions for the Blog. No database or network calls
// here, they only operate on the mock arrays in blog-data.php and the
// player roster in players.php, so nothing here needs to change once
// real data replaces the mock set.

require_once __DIR__ . '/players.php';

if (!defined('AKD_BLOG_COMMENT_MAX_LENGTH')) {
    define('AKD_BLOG_COMMENT_MAX_LENGTH', 500);
}
if (!function_exists('akd_blog_format_date')) {
    function akd_blog_format_date(string $date): string
    {
        $timestamp = strtotime($date);
        return $timestamp ? date('M j, Y', $timestamp) : $date;
    }
}

if (!function_exists('akd_blog_find_featured')) {
    function akd_blog_find_featured(array $articles): ?array
    {
        foreach ($articles as $article) {
            if (!empty($article['featured'])) {
                return $article;
            }
        }

        return $articles[0] ?? null;
    }
}

if (!function_exists('akd_blog_sort_by_date_desc')) {
    function akd_blog_sort_by_date_desc(array $articles): array
    {
        usort($articles, static function (array $a, array $b): int {
            return strtotime($b['published_at']) <=> strtotime($a['published_at']);
        });

        return $articles;
    }
}

if (!function_exists('akd_blog_pick')) {
    /**
     * Pulls up to $limit articles from $pool, optionally restricted to a
     * single category, skipping any id already present in $usedIds.
     * $usedIds is passed by reference and grows with every id returned,
     * so sections built one after another never repeat an article.
     */
    function akd_blog_pick(array $pool, array &$usedIds, int $limit, ?string $category = null): array
    {
        $pool = akd_blog_sort_by_date_desc($pool);
        $picked = [];

        foreach ($pool as $article) {
            if (count($picked) >= $limit) {
                break;
            }

            if (in_array($article['id'], $usedIds, true)) {
                continue;
            }

            if ($category !== null && $article['category'] !== $category) {
                continue;
            }

            $picked[] = $article;
            $usedIds[] = $article['id'];
        }

        return $picked;
    }
}

if (!function_exists('akd_blog_category_by_slug')) {
    function akd_blog_category_by_slug(array $categories, string $slug): ?array
    {
        foreach ($categories as $category) {
            if ($category['slug'] === $slug) {
                return $category;
            }
        }

        return null;
    }
}

if (!function_exists('akd_blog_articles_by_category')) {
    function akd_blog_articles_by_category(array $articles, string $categoryLabel): array
    {
        return array_values(array_filter($articles, static function (array $article) use ($categoryLabel) {
            return $article['category'] === $categoryLabel;
        }));
    }
}

if (!function_exists('akd_blog_category_counts')) {
    /**
     * Article counts keyed by category slug, derived from the mock data
     * so the number shown next to each category can never drift from
     * what is actually rendered.
     */
    function akd_blog_category_counts(array $articles, array $categories): array
    {
        $counts = [];

        foreach ($categories as $category) {
            $counts[$category['slug']] = count(akd_blog_articles_by_category($articles, $category['label']));
        }

        return $counts;
    }
}

if (!defined('AKD_BLOG_PER_PAGE')) {
    // Single place to change how many articles show per archive/category
    // page, instead of a number scattered across templates.
    define('AKD_BLOG_PER_PAGE', 12);
}

if (!function_exists('akd_blog_current_page')) {
    function akd_blog_current_page(): int
    {
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);

        if ($page === false || $page < 1) {
            return 1;
        }

        return $page;
    }
}

if (!function_exists('akd_blog_paginate')) {
    /**
     * Slices $items into a page. The requested page is clamped between
     * 1 and the actual last page, so an out of range value such as
     * ?page=999 falls back to the final page instead of an empty
     * slice, a negative offset, or a warning.
     *
     * @param array<int,array<string,mixed>> $items
     * @return array{items: array<int,array<string,mixed>>, current_page: int, per_page: int, total_items: int, total_pages: int}
     */
    function akd_blog_paginate(array $items, int $page, int $perPage): array
    {
        $perPage = max(1, $perPage);
        $totalItems = count($items);
        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        return [
            'items' => array_slice($items, $offset, $perPage),
            'current_page' => $page,
            'per_page' => $perPage,
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
        ];
    }
}

if (!function_exists('akd_blog_pagination_pages')) {
    /**
     * Condensed page number list for the pagination control. 'ellipsis'
     * is a placeholder entry wherever a gap should render.
     *
     * @return array<int,int|string>
     */
    function akd_blog_pagination_pages(int $currentPage, int $totalPages): array
    {
        $maxVisible = 7;

        if ($totalPages <= $maxVisible) {
            return range(1, $totalPages);
        }

        if ($currentPage <= 4) {
            return [1, 2, 3, 4, 5, 'ellipsis', $totalPages];
        }

        if ($currentPage >= $totalPages - 3) {
            return array_merge([1, 'ellipsis'], range($totalPages - 4, $totalPages));
        }

        return [1, 'ellipsis', $currentPage - 1, $currentPage, $currentPage + 1, 'ellipsis', $totalPages];
    }
}

if (!function_exists('akd_blog_search_query')) {
    /**
     * Reads and normalizes the ?q= parameter. Handles a missing value,
     * an array value (e.g. ?q[]=x), whitespace-only input, and very
     * long input, always returning a plain, bounded string.
     */
    function akd_blog_search_query(): string
    {
        $query = $_GET['q'] ?? '';

        if (!is_string($query)) {
            return '';
        }

        $query = trim($query);

        return mb_substr($query, 0, 100);
    }
}

if (!function_exists('akd_blog_search')) {
    /**
     * Filters $articles to those whose title, excerpt, category, or
     * tags contain $query (case-insensitive substring match). Mirrors
     * the matching rules in blog.js's instant search dropdown, so a
     * query that finds an article there finds it here too.
     *
     * @param array<int,array<string,mixed>> $articles
     * @return array<int,array<string,mixed>>
     */
    function akd_blog_search(array $articles, string $query): array
    {
        $needle = mb_strtolower($query);

        return array_values(array_filter($articles, static function (array $article) use ($needle) {
            $haystack = mb_strtolower(implode(' ', array_merge(
                [$article['title'], $article['excerpt'], $article['category']],
                $article['tags'] ?? []
            )));

            return str_contains($haystack, $needle);
        }));
    }
}

if (!function_exists('akd_blog_find_by_slug')) {
    function akd_blog_find_by_slug(array $articles, string $slug): ?array
    {
        foreach ($articles as $article) {
            if ($article['slug'] === $slug) {
                return $article;
            }
        }

        return null;
    }
}

if (!function_exists('akd_blog_category_by_label')) {
    function akd_blog_category_by_label(array $categories, string $label): ?array
    {
        foreach ($categories as $category) {
            if ($category['label'] === $label) {
                return $category;
            }
        }

        return null;
    }
}

if (!function_exists('akd_blog_related_articles')) {
    /**
     * Related articles for $article: same category first, ranked by
     * shared tags then recency, current article always excluded. A
     * simple deterministic sort, not a recommendation engine.
     *
     * @param array<int,array<string,mixed>> $articles
     * @return array<int,array<string,mixed>>
     */
    function akd_blog_related_articles(array $articles, array $article, int $limit = 3): array
    {
        $pool = array_values(array_filter($articles, static function (array $candidate) use ($article) {
            return $candidate['id'] !== $article['id'] && $candidate['category'] === $article['category'];
        }));

        $tags = $article['tags'] ?? [];

        usort($pool, static function (array $a, array $b) use ($tags) {
            $sharedA = count(array_intersect($a['tags'] ?? [], $tags));
            $sharedB = count(array_intersect($b['tags'] ?? [], $tags));

            if ($sharedA !== $sharedB) {
                return $sharedB <=> $sharedA;
            }

            return strtotime($b['published_at']) <=> strtotime($a['published_at']);
        });

        return array_slice($pool, 0, $limit);
    }
}

if (!function_exists('akd_blog_slugify')) {
    function akd_blog_slugify(string $text): string
    {
        $slug = mb_strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9]+/u', '-', $slug) ?? '';
        return trim($slug, '-') ?: 'section';
    }
}

if (!function_exists('akd_blog_prepare_content')) {
    /**
     * Walks $blocks once, assigning a stable slug id to every heading
     * block (used as scroll anchors) and building the matching table
     * of contents alongside it, so the renderer and the ToC are always
     * derived from the same pass over the content.
     *
     * @param array<int,array<string,mixed>> $blocks
     * @return array{blocks: array<int,array<string,mixed>>, toc: array<int,array{id:string,text:string}>}
     */
    function akd_blog_prepare_content(array $blocks): array
    {
        $toc = [];
        $used = [];

        foreach ($blocks as $index => $block) {
            if (($block['type'] ?? null) !== 'heading') {
                continue;
            }

            $base = akd_blog_slugify($block['text']);
            $id = $base;
            $suffix = 2;

            while (isset($used[$id])) {
                $id = $base . '-' . $suffix;
                $suffix++;
            }

            $used[$id] = true;
            $blocks[$index]['id'] = $id;
            $toc[] = ['id' => $id, 'text' => $block['text']];
        }

        return ['blocks' => $blocks, 'toc' => $toc];
    }
}

if (!function_exists('akd_blog_relative_time')) {
    function akd_blog_relative_time(string $datetime): string
    {
        $timestamp = strtotime($datetime);

        if (!$timestamp) {
            return $datetime;
        }

        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Just now';
        }
        if ($diff < 3600) {
            $minutes = (int) floor($diff / 60);
            return $minutes . ' minute' . ($minutes === 1 ? '' : 's') . ' ago';
        }
        if ($diff < 86400) {
            $hours = (int) floor($diff / 3600);
            return $hours . ' hour' . ($hours === 1 ? '' : 's') . ' ago';
        }
        if ($diff < 2592000) {
            $days = (int) floor($diff / 86400);
            return $days . ' day' . ($days === 1 ? '' : 's') . ' ago';
        }

        return date('M j, Y', $timestamp);
    }
}

if (!function_exists('akd_blog_initials')) {
    function akd_blog_initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $first = $parts[0][0] ?? '';
        $second = isset($parts[1]) ? $parts[1][0] : '';
        return mb_strtoupper($first . $second);
    }
}

if (!function_exists('akd_blog_comments_for_article')) {
    /**
     * Hydrates the mock comments for one article with identity details
     * from the player roster (fullname, avatar color, initials), then
     * sorts chronologically. Comments store only a username, roster
     * data stays the single source of truth for everything else.
     *
     * @param array<int,array<int,array<string,mixed>>> $commentsByArticle
     * @return array<int,array<string,mixed>>
     */
    function akd_blog_comments_for_article(array $commentsByArticle, int $articleId): array
    {
        $comments = $commentsByArticle[$articleId] ?? [];
        $hydrated = [];

        foreach ($comments as $comment) {
            $player = akdFindPlayerByUsername($comment['username']);
            $fullname = $player['fullname'] ?? $comment['username'];

            $hydrated[] = [
                'id' => $comment['id'],
                'fullname' => $fullname,
                'username' => $comment['username'],
                'content' => $comment['content'],
                'created_at' => $comment['created_at'],
                'avatar_color' => akdPlayerAvatarColor($comment['username']),
                'initials' => akd_blog_initials($fullname),
            ];
        }

        usort($hydrated, static function (array $a, array $b): int {
            return strtotime($a['created_at']) <=> strtotime($b['created_at']);
        });

        return $hydrated;
    }
}