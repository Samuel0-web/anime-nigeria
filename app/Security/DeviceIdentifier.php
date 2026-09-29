<?php
namespace App\Security;

class DeviceIdentifier {
    private const IDENTIFIER_BYTES = 32;
    private const LIFETIME = 60 * 60 * 24 * 365 * 2; // 2 years

    public static function get(): string {
        $cookieName = self::cookieName();
        $identifier = Cookie::get($cookieName);
        if ($identifier !== null && $identifier !== '') {
            return $identifier;
        }

        $identifier = bin2hex(random_bytes(self::IDENTIFIER_BYTES));

        Cookie::set($cookieName, $identifier, self::LIFETIME);
        return $identifier;
    }

    private static function cookieName(): string {
        return \App\Core\Config::cookieName('DEVICE_IDENTIFIER_COOKIE');
    }

    public static function hash(string $identifier): string {
        return hash('sha256', $identifier);
    }
}