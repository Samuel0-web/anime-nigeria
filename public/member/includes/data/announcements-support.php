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

if (!function_exists('akd_announce_image')) {
    /**
     * Resolve an announcement's image for rendering.
     *
     * Returns null when the announcement has no image, or when a local
     * (root-relative) image path does not exist on disk. The card then
     * renders its neutral placeholder instead of a broken <img>.
     *
     * Absolute http(s) URLs are passed through untouched, so a future
     * repository/service can return CDN URLs without changes here.
     *
     * Alt text comes from the optional `image_alt` field and falls back
     * to the announcement title, so it is never empty.
     *
     * @param array<string, mixed> $item
     * @return array{src: string, alt: string}|null
     */
    function akd_announce_image(array $item): ?array
    {
        $src = trim((string) ($item['image'] ?? ''));

        if ($src === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $src)) {
            // Root-relative paths only. Rejects "//host/..." and relative paths.
            if ($src[0] !== '/' || str_starts_with($src, '//')) {
                return null;
            }

            // dirname(__DIR__, 3): includes/data -> includes -> member -> public
            $publicRoot = realpath(dirname(__DIR__, 3));
            $file       = $publicRoot !== false ? realpath($publicRoot . $src) : false;

            if (
                $file === false
                || !str_starts_with($file, $publicRoot . DIRECTORY_SEPARATOR)
                || !is_file($file)
            ) {
                return null;
            }
        }

        $alt = trim((string) ($item['image_alt'] ?? ''));

        return [
            'src' => $src,
            'alt' => $alt !== '' ? $alt : (string) ($item['title'] ?? ''),
        ];
    }
}