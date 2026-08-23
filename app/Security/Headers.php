<?php
namespace App\Security;

class Headers {
    public static function send(): void {
        // Prevent MIME sniffing
        header('X-Content-Type-Options: nosniff');

        // Prevent clickjacking
        header('X-Frame-Options: DENY');

        // Control referrer information
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Restrict access to sensitive browser features
        header('Permissions-Policy: ' . 'geolocation=(), ' . 'microphone=(), '
            . 'camera=(), ' . 'payment=(), ' . 'usb=(), ' . 'browsing-topics=()'
        );

        // Enforce HTTPS
        // Only send this when the request is actually HTTPS.
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: ' . 'max-age=31536000; '
                . 'includeSubDomains; ' . 'preload'
            );
        }

        // Content Security Policy
        header('Content-Security-Policy: ' . "default-src 'self'; " . "base-uri 'self'; "
            . "form-action 'self'; " . "object-src 'none'; " . "frame-ancestors 'none'; "

            // JavaScript
            . "script-src " . "'self' " . "'unsafe-inline' " . "http://127.0.0.1:5173 "
            . "https://accounts.google.com " . "https://apis.google.com; "

            // Styles
            . "style-src " . "'self' " . "'unsafe-inline' " . "http://127.0.0.1:5173; "

            // Fonts
            . "font-src " . "'self' " . "data: " . "http://127.0.0.1:5173; "

            // Images
            . "img-src " . "'self' " . "data: " . "blob: " . "https:; "

            // AJAX / Fetch / WebSocket
            . "connect-src " . "'self' " . "http://127.0.0.1:5173 "
            . "ws://127.0.0.1:5173; "

            // Google OAuth / authentication frames
            . "frame-src " . "https://accounts.google.com;"
        );
    }
}