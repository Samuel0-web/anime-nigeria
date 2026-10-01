<?php
declare(strict_types=1);

/**
 * Single source of truth for the member footer controls.
 * URLs are copied exactly from the old Help Centre footer:
 * Contact => /contact, Privacy => /privacy, Terms => /terms.
 */

if (!function_exists('akd_sanitize_internal_path')) {
    /**
     * Returns $value only if it is a safe internal path (optionally with a
     * query string). Anything else returns '' so it is never used as context.
     */
    function akd_sanitize_internal_path(?string $value, int $maxLength = 500): string
    {
        $value = trim((string) $value);

        if ($value === '' || strlen($value) > $maxLength) {
            return '';
        }

        // Must start with a single "/", never "//" (protocol-relative) or "/\".
        if ($value[0] !== '/' || str_starts_with($value, '//') || str_contains($value, '\\')) {
            return '';
        }

        // No control characters.
        if (preg_match('/[\x00-\x1F\x7F]/', $value)) {
            return '';
        }

        return $value;
    }
}

if (!function_exists('akd_bug_report_url')) {
    /**
     * Builds /member/help?report=bug[&from=<encoded internal path>].
     * http_build_query percent-encodes "from" exactly once (RFC 3986).
     */
    function akd_bug_report_url(?string $from = null): string
    {
        $params = ['report' => 'bug'];

        $from = akd_sanitize_internal_path($from);
        if ($from !== '') {
            $params['from'] = $from;
        }

        return '/member/help?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }
}

if (!function_exists('akd_member_footer_links')) {
    function akd_member_footer_links(string $requestUri): array
    {
        $path       = rtrim(parse_url($requestUri, PHP_URL_PATH) ?: '/', '/') ?: '/';
        $onHelpPage = $path === '/member/help';

        return [
            'contact' => [
                'label'    => 'Contact',
                'href'     => '/contact',
                'external' => true,
            ],
            'bug' => [
                'label'    => 'Report a Bug',
                // The Help Centre is never treated as the page with the bug.
                'href'     => akd_bug_report_url($onHelpPage ? null : $requestUri),
                'external' => false,
            ],
            'privacy' => [
                'label'    => 'Privacy Policy',
                'href'     => '/privacy',
                'external' => true,
            ],
            'terms' => [
                'label'    => 'Terms of Use',
                'href'     => '/terms',
                'external' => true,
            ],
        ];
    }
}