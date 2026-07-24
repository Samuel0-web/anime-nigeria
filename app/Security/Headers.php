<?php
namespace App\Security;

class Headers {
    public static function send(): void {
        // Prevent MIME sniffing
        header('X-Content-Type-Options: nosniff');

        // Prevent clickjacking
        header('X-Frame-Options: DENY');

        // XSS protection (older browsers)
        header('X-XSS-Protection: 1; mode=block');

        // Don't leak referrer unnecessarily
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Permissions Policy
        header(
            'Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()'
        );

        // Prevent FLoC / Topics API
        header('Permissions-Policy: browsing-topics=()');

        // HSTS (ONLY on HTTPS)
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header(
                'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload'
            );
        }

        // Content Security Policy
        header(
            "Content-Security-Policy: "
            . "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' https://accounts.google.com https://apis.google.com; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
            . "font-src 'self' https://fonts.gstatic.com data:; "
            . "img-src 'self' data: blob: https:; "
            . "connect-src 'self'; "
            . "frame-src https://accounts.google.com; "
            . "object-src 'none'; "
            . "base-uri 'self'; "
            . "form-action 'self';"
        );
    }
}