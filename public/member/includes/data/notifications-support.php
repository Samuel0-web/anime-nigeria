<?php
/**
 * Pure PHP helpers for the Notifications feature. Kept self-contained
 * (mirrors announcements-support.php's own slug helper) rather than
 * reusing akd_announce_slug(), so this feature has no cross-file
 * dependency on Announcements.
 */

if (!function_exists('akd_notify_slug')) {
    function akd_notify_slug(string $category): string
    {
        $slug = strtolower(trim($category));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        return trim($slug, '-');
    }
}

if (!function_exists('akd_notify_unread_count')) {
    function akd_notify_unread_count(array $notifications): int
    {
        return count(array_filter($notifications, fn ($n) => !empty($n['unread'])));
    }
}

if (!function_exists('akd_notify_preview')) {
    function akd_notify_preview(array $notifications, int $limit = 4): array
    {
        return array_slice($notifications, 0, $limit);
    }
}