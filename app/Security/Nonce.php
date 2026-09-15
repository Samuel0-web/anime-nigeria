<?php
declare(strict_types=1);

namespace App\Security;

/**
 * Per-request CSP nonce.
 *
 * Generated lazily on first call, then cached for the rest of the request.
 * PHP's request isolation means a fresh nonce is produced on every request
 * with no extra bookkeeping.
 */
final class Nonce {
    private static ?string $value = null;

    /**
     * Raw nonce value (for CSP header).
     */
    public static function get(): string {
        if (self::$value === null) {
            // 16 random bytes → 128 bits of entropy. URL-safe base64, no padding,
            // matches CSP's base64-value grammar: [a-zA-Z0-9+/_-]+={0,2}
            self::$value = rtrim(
                strtr(base64_encode(random_bytes(16)), '+/', '-_'),
                '='
            );
        }
        return self::$value;
    }

    /**
     * Ready-to-inject attribute, e.g. ` nonce="abc..."`.
     */
    public static function attr(): string {
        return ' nonce="' . htmlspecialchars(self::get(), ENT_QUOTES, 'UTF-8') . '"';
    }
}