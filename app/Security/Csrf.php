<?php
namespace App\Security;

class Csrf {
    public static function token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function verify(?string $token): bool {
        return hash_equals(
            $_SESSION['csrf_token'] ?? '',
            $token ?? ''
        );
    }
}