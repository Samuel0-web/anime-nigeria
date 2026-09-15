<?php
namespace App\Security;

class Headers {
    public static function send(): void {
        // Dev detection: matches vite_is_dev() in includes/vite.php
        $appEnv = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV']
            ?? (getenv('APP_ENV') !== false ? getenv('APP_ENV') : null) ?? 'production';

        $isDev  = in_array($appEnv, ['local', 'development', 'dev', 'testing'], true);

        // Vite dev server sources — only injected in dev
        $viteHttp   = $isDev ? 'http://127.0.0.1:5173 ' : '';
        $viteWs     = $isDev ? 'ws://127.0.0.1:5173 '   : '';
        // Vite HMR client spawns a SharedWorker/Worker from a blob: URL
        $viteWorker = $isDev ? 'blob: http://127.0.0.1:5173 ' : '';

        // Prevent MIME sniffing
        header('X-Content-Type-Options: nosniff');

        // Prevent clickjacking
        header('X-Frame-Options: DENY');

        // Control referrer information
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Restrict access to sensitive browser features
        header('Permissions-Policy: '
            . 'geolocation=(), microphone=(), camera=(), payment=(), '
            . 'usb=(), browsing-topics=()'
        );

        // Enforce HTTPS (only when the request is actually HTTPS)
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }

        // Content Security Policy
        header('Content-Security-Policy: '
            . "default-src 'self'; "
            . "base-uri 'self'; "
            . "form-action 'self'; "
            . "object-src 'none'; "
            . "frame-ancestors 'none'; "

            // JavaScript
            . "script-src 'self' 'unsafe-inline' " . $viteHttp . "; "

            // Workers — Vite HMR uses a blob: SharedWorker in dev
            . "worker-src 'self' " . $viteWorker . "; "
            . "child-src 'self' "  . $viteWorker . "; "

            // Styles
            . "style-src 'self' 'unsafe-inline' " . $viteHttp . "; "

            // Fonts
            . "font-src 'self' data: " . $viteHttp . "; "

            // Images
            . "img-src 'self' data: blob: https:; "

            // AJAX / Fetch / WebSocket
            . "connect-src 'self' " . $viteHttp . $viteWs . "; "

            // No iframes are ever embedded on this site
            . "frame-src 'none'; "

            // PWA manifest
            . "manifest-src 'self';"
        );

        header('Accept-CH: Sec-CH-UA, Sec-CH-UA-Mobile, Sec-CH-UA-Model, '
            . 'Sec-CH-UA-Platform, Sec-CH-UA-Platform-Version, '
            . 'Sec-CH-UA-Full-Version, Sec-CH-UA-Full-Version-List, '
            . 'Sec-CH-UA-Arch, Sec-CH-UA-Bitness, Sec-CH-UA-Form-Factors'
        );
    }
}