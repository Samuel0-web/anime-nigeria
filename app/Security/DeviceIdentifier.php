<?php
namespace App\Security;

class DeviceIdentifier {
    private const COOKIE_NAME = 'DEVICEIDENTIFIER';
    private const IDENTIFIER_BYTES = 32;
    private const LIFETIME = 60 * 60 * 24 * 365 * 2; // 2 years

    public static function get(): string {
        if (!empty($_COOKIE[self::COOKIE_NAME])) {
            return $_COOKIE[self::COOKIE_NAME];
        }

        $identifier = bin2hex(random_bytes(self::IDENTIFIER_BYTES));

        setcookie(self::COOKIE_NAME, $identifier,
            [
                'expires' => time() + self::LIFETIME,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );

        $_COOKIE[self::COOKIE_NAME] = $identifier;
        return $identifier;
    }

    public static function hash(string $identifier): string {
        return hash('sha256', $identifier);
    }
}