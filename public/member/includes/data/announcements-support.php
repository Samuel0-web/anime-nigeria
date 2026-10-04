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
     * Delegates to AnnouncementService::resolveImageUrl(), the same resolver
     * the admin uses, so there is one definition of "usable image":
     * absolute http(s) URLs pass through, root-relative paths must exist under
     * the public root, anything else is null. (The previous realpath()
     * containment check rejected files served through the public/storage link,
     * so every uploaded image fell back to the icon.)
     *
     * Returns null when there is no usable image; row.php then renders the
     * existing icon. Alt text falls back to the title, so it is never empty.
     *
     * @param array<string, mixed> $item
     * @return array{src: string, alt: string}|null
     */
    function akd_announce_image(array $item): ?array
    {
        $src = \App\Services\AnnouncementService::resolveImageUrl(
            isset($item['image']) ? (string) $item['image'] : null
        );

        if ($src === null) {
            return null;
        }

        $alt = trim((string) ($item['image_alt'] ?? ''));

        return [
            'src' => $src,
            'alt' => $alt !== '' ? $alt : (string) ($item['title'] ?? ''),
        ];
    }
}