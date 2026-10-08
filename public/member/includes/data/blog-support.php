<?php
// Pure presentation helpers for the Blog. No database access: data comes from App\Services\BlogService.

if (!defined('AKD_BLOG_COMMENT_MAX_LENGTH')) {
    define('AKD_BLOG_COMMENT_MAX_LENGTH', \App\Services\BlogCommentService::MAX_LENGTH);
}

if (!function_exists('akd_blog_format_date')) {
    function akd_blog_format_date(string $date): string
    {
        $timestamp = strtotime($date);
        return $timestamp ? date('M j, Y', $timestamp) : $date;
    }
}

if (!function_exists('akd_blog_current_page')) {
    function akd_blog_current_page(): int
    {
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);

        return ($page === false || $page < 1) ? 1 : min($page, 500);
    }
}

if (!function_exists('akd_blog_search_query')) {
    function akd_blog_search_query(): string
    {
        $query = $_GET['q'] ?? '';

        return is_string($query) ? mb_substr(trim($query), 0, 100) : '';
    }
}

if (!function_exists('akd_blog_relative_time')) {
    function akd_blog_relative_time(string $datetime): string {
        try {
            $timestamp = (new DateTimeImmutable($datetime, new DateTimeZone('UTC')))->getTimestamp();
        } catch (\Throwable) {
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

if (!function_exists('akd_blog_flatten_replies')) {
    /** Flattens a reply tree into one chronological list with "reply to" attribution. */
    function akd_blog_flatten_replies(array $replies): array
    {
        $flat = [];

        foreach ($replies as $reply) {
            $flat[] = [
                'id' => $reply['id'], 'fullname' => $reply['fullname'], 'username' => $reply['username'],
                'content' => $reply['content'], 'created_at' => $reply['created_at'],
                'avatar_color' => $reply['avatar_color'], 'initials' => $reply['initials'],
                'reply_to_username' => null, 'reply_to_id' => null,
            ];

            if (!empty($reply['replies'])) {
                foreach (akd_blog_flatten_replies($reply['replies']) as $nested) {
                    if ($nested['reply_to_id'] === null) {
                        $nested['reply_to_username'] = $reply['username'];
                        $nested['reply_to_id'] = $reply['id'];
                    }
                    $flat[] = $nested;
                }
            }
        }

        return $flat;
    }
}

if (!function_exists('akd_blog_count_comments_with_replies')) {
    function akd_blog_count_comments_with_replies(array $comments): int
    {
        $count = 0;

        foreach ($comments as $comment) {
            $count++;

            if (!empty($comment['replies'])) {
                $count += akd_blog_count_comments_with_replies($comment['replies']);
            }
        }

        return $count;
    }
}

if (!function_exists('akd_blog_public_url')) {
    /** There is no separate public article route yet, so the member URL is shared. One place to change later. */
    function akd_blog_public_url(array $article, string $memberUrl): string
    {
        return $memberUrl;
    }
}