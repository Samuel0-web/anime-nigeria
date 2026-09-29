<?php
namespace App\Security;

class Cookie {
    public static function get(string $name): ?string {
        return isset($_COOKIE[$name]) && is_string($_COOKIE[$name]) ? $_COOKIE[$name]
            : null;
    }

    public static function set(string $name, string $value, int $lifetime): void {
        setcookie($name, $value, \App\Core\Config::cookieOptions(time() + $lifetime));
        $_COOKIE[$name] = $value;
    }

    public static function forget(string $name): void {
        setcookie($name, '', \App\Core\Config::cookieOptions(time() - 3600));
        unset($_COOKIE[$name]);
    }
}