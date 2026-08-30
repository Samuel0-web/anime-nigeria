<?php
/**
 * Pure PHP helpers for the Announcements page. Kept separate from
 * announcements-data.php the same way awards-support.php sits alongside
 * awards-data.php.
 */

if (!function_exists('akd_announce_slug')) {
    /**
     * Convert an announcement category label into a URL/attribute-safe
     * slug used to match filter pills to announcement rows client side.
     */
    function akd_announce_slug(string $category): string
    {
        $slug = strtolower(trim($category));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        return trim($slug, '-');
    }
}