<?php
/**
 * Pure PHP helpers for the Gallery page. Kept separate from
 * gallery-data.php the same way announcements-support.php sits
 * alongside announcements-data.php.
 */

if (!function_exists('akd_gallery_slug')) {
    /**
     * Convert a gallery category label into a URL/attribute-safe slug
     * used to match filter pills to gallery items client side. Mirrors
     * akd_announce_slug() in announcements-support.php.
     */
    function akd_gallery_slug(string $category): string
    {
        $slug = strtolower(trim($category));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        return trim($slug, '-');
    }
}

if (!function_exists('akd_gallery_categories')) {
    /**
     * Derive the filter pill list from the actual gallery data instead
     * of maintaining a second, separate hardcoded category list. "All"
     * is always first regardless of item order.
     *
     * @param list<array{category: string}> $items
     * @return list<string>
     */
    function akd_gallery_categories(array $items): array {
        $categories = [];

        foreach ($items as $item) {
            $categories[$item['category']] = true;
        }

        return array_merge(['All'], array_keys($categories));
    }
}