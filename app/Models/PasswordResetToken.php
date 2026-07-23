<?php
namespace App\Models;
use PDO;

class PasswordResetToken {
    public function __construct(private PDO $db) {}

    public function create(int $userId, string $hash, string $expires): bool {
        $this->deleteByUser($userId);

        $stmt = $this->db->prepare("INSERT INTO password_reset_tokens
            (user_id, token_hash, expires_at)
            VALUES (?, ?, ?)
        ");

        return $stmt->execute([$userId, $hash, $expires]);
    }

    public function deleteByUser(int $userId): void {
        $stmt = $this->db->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

    public function findByToken(string $token): array|false {
        $hash = hash('sha256', trim($token));

        $stmt = $this->db->prepare("SELECT prt.*, u.email, u.fullname FROM password_reset_tokens prt
            INNER JOIN users u ON u.id = prt.user_id WHERE prt.token_hash = ? LIMIT 1
        ");

        $stmt->execute([$hash]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM password_reset_tokens WHERE id = ?");
        $stmt->execute([$id]);
    }
}