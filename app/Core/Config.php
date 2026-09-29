<?php
namespace App\Core;

class Config {
    public static function get(string $key, mixed ...$default): mixed {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        if ($default !== []) {
            return $default[0];
        }

        throw new \RuntimeException("Required environment variable '{$key}' is not configured.");
    }

    public static function bool(string $key, bool $default = false): bool {
        return filter_var(self::get($key, $default), FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE) ?? $default;
    }

    public static function sessionName(): string {
        return (string) self::get('SESSION_NAME');
    }

    public static function cookieName(string $key): string {
        return (string) self::get($key);
    }

    public static function cookieOptions(?int $expires = null): array {
        $appEnv = strtolower((string) self::get('APP_ENV', 'production'));
        $secureDefault = $appEnv === 'production'
            || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        return [
            'expires' => $expires ?? 0,
            'path' => (string) self::get('COOKIE_PATH', '/'),
            'domain' => self::get('COOKIE_DOMAIN', ''),
            'secure' => self::bool('COOKIE_SECURE', $secureDefault),
            'httponly' => self::bool('COOKIE_HTTPONLY', true),
            'samesite' => (string) self::get('COOKIE_SAMESITE', 'Lax'),
        ];
    }

    public static function sessionCookieOptions(): array {
        $options = self::cookieOptions();
        unset($options['expires']);
        $options['lifetime'] = (int) self::get('SESSION_COOKIE_LIFETIME', 0);
        return $options;
    }
}