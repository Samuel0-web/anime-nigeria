<?php
namespace App\Auth;
use PDO;

class RememberMe {
    private const COOKIE_NAME = 'remember_me';
    private const LIFETIME = 60 * 60 * 24 * 30; // 30 days
    public function __construct(private PDO $db) { }

    public function create(int $userId): void {
        $this->createToken($userId);
    }

    private function createToken(int $userId): string {
        $selector = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));
        $validatorHash = hash('sha256', $validator);
        $expiresAt = date('Y-m-d H:i:s', time() + self::LIFETIME);

        $this->db->prepare("INSERT INTO remember_tokens
            (
                user_id,
                selector,
                validator_hash,
                expires_at
            ) VALUES (?, ?, ?, ?)
        ")->execute([
            $userId,
            $selector,
            $validatorHash,
            $expiresAt
        ]);

        setcookie(
            self::COOKIE_NAME,
            $selector . ':' . $validator,
            [
                'expires'  => time() + self::LIFETIME,
                'path'     => '/',
                'secure'   => !empty($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict',
            ]
        );

        return $selector;
    }

    public function loginFromCookie(): bool {
        if (isset($_SESSION['user_id'])) {
            return true;
        }

        if (empty($_COOKIE[self::COOKIE_NAME])) {
            return false;
        }

        $parts = explode(':', $_COOKIE[self::COOKIE_NAME], 2);

        if (count($parts) !== 2) {
            return false;
        }

        [$selector, $validator] = $parts;
        $stmt = $this->db->prepare("SELECT * FROM remember_tokens WHERE selector = ? LIMIT 1");
        $stmt->execute([$selector]);
        $token = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$token) {
            return false;
        }

        if (strtotime($token['expires_at']) < time()) {
            $this->db->prepare("DELETE FROM remember_tokens WHERE id = ?")->execute([
                $token['id']
            ]);

            $this->forget();
            return false;
        }

        if (!hash_equals($token['validator_hash'], hash('sha256', $validator))) {
            // Possible stolen cookie.
            $this->db->prepare("DELETE FROM remember_tokens WHERE id = ?")->execute([
                $token['id']
            ]);

            $this->forget();
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $token['user_id'];

        // Remove the old remember token.
        $this->db->prepare("DELETE FROM remember_tokens WHERE id = ?")->execute([
            $token['id']
        ]);

        // Issue a brand new one.
        $this->createToken((int)$token['user_id']);
        return true;
    }

    public function forget(): void {
        if (empty($_COOKIE[self::COOKIE_NAME])) {
            return;
        }

        $parts = explode(':', $_COOKIE[self::COOKIE_NAME], 2);

        if (count($parts) !== 2) {
            return;
        }

        [$selector] = $parts;
        $this->db->prepare("DELETE FROM remember_tokens WHERE selector = ?")->execute([$selector]);

        setcookie(
            self::COOKIE_NAME,
            '',
            [
                'expires'  => time() - 3600,
                'path'     => '/',
                'secure'   => !empty($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict',
            ]
        );

        unset($_COOKIE[self::COOKIE_NAME]);
    }

    public function deleteAllForUser(int $userId): void {
        $stmt = $this->db->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
}