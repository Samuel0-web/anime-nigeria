<?php
namespace App\Models;
use PDO;
class User {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Create a new user
     */
    public function create(array $data): int|false {
        $stmt = $this->db->prepare("INSERT INTO users
            (
                fullname,
                username,
                email,
                password,
                auth_provider,
                google_id,
                avatar,
                email_verified_at,
                email_verification_token,
                email_verification_sent_at,
                email_verification_expires_at
            )
            VALUES (
                :fullname,
                :username,
                :email,
                :password,
                :provider,
                :google_id,
                :avatar,
                :email_verified_at,
                :verification_token,
                :verification_sent_at,
                :verification_expires_at
            )
        ");

        $success = $stmt->execute([
            ':fullname' => $data['fullname'],
            ':username' => $data['username'] ?? null,
            ':email' => $data['email'],
            ':password' => $data['password'] ?? null,
            ':provider' => $data['provider'] ?? 'local',
            ':google_id' => $data['google_id'] ?? null,
            ':avatar' => $data['avatar'] ?? null,
            ':email_verified_at' => $data['email_verified_at'] ?? null,
            ':verification_token' => $data['verification_token'] ?? null,
            ':verification_sent_at' => $data['verification_sent_at'] ?? null,
            ':verification_expires_at' => $data['verification_expires_at'] ?? null,
        ]);

        if (!$success) {
            return false;
        }

        return (int) $this->db->lastInsertId();
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): array|false {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");

        $stmt->execute([
            ':email' => $email,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find user by username
     */
    public function findByUsername(string $username): array|false {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(:username)
            LIMIT 1
        ");

        $stmt->execute([
            ':username' => trim($username),
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");

        $stmt->execute([
            ':id' => $id,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check whether an email already exists
     */
    public function emailExists(string $email): bool {
        return $this->findByEmail($email) !== false;
    }

    /**
     * Check whether a username already exists
     */
    public function usernameExists(string $username): bool {
        return $this->findByUsername($username) !== false;
    }

    public function findByVerificationToken(string $token): array|false {
        $hash = hash('sha256', $token);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE email_verification_token = :token
            LIMIT 1
        ");

        $stmt->execute([
            ':token' => $hash,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function markEmailVerified(int $id): bool {
        $stmt = $this->db->prepare("UPDATE users SET email_verified_at = CURRENT_TIMESTAMP,
            email_verification_token = NULL, email_verification_expires_at = NULL WHERE id = :id
            AND email_verified_at IS NULL
        ");

        return $stmt->execute([
            ':id' => $id,
        ]);
    }

    public function updateUsername(int $id, string $username): bool {
        $stmt = $this->db->prepare("UPDATE users SET username = :username,
            username_changed_at = CURRENT_TIMESTAMP WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':username' => $username,
        ]);
    }

    public function updateVerificationToken(int $id, string $token, string $expiresAt): bool {
        $stmt = $this->db->prepare("UPDATE users SET email_verification_token = :token,
            email_verification_expires_at = :expires, email_verification_sent_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':token' => $token,
            ':expires' => $expiresAt,
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");

        return $stmt->execute([
            ':id' => $id,
        ]);
    }

    public function verifyPassword(array $user, string $password): bool {
        if (empty($user['password'])) {
            return false;
        }

        return password_verify($password, $user['password']);
    }

    public function updatePassword(int $userId, string $passwordHash): bool {
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$passwordHash, $userId]);
    }

    public function findByGoogleId(string $googleId): array|false {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE google_id = :google_id LIMIT 1");

        $stmt->execute([
            ':google_id' => $googleId,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function linkGoogleAccount(int $userId, string $googleId, ?string $avatar): bool {
        $stmt = $this->db->prepare("UPDATE users SET google_id = :google_id, avatar = :avatar
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $userId,
            ':google_id' => $googleId,
            ':avatar' => $avatar,
        ]);
    }

    public function updateAvatar(int $userId, ?string $avatar): bool {
        $stmt = $this->db->prepare("UPDATE users SET avatar = :avatar WHERE id = :id");

        return $stmt->execute([
            ':id' => $userId,
            ':avatar' => $avatar,
        ]);
    }

    public function hasGoogleLinked(int $userId): bool {
        $stmt = $this->db->prepare("SELECT google_id FROM users WHERE id = :id LIMIT 1");

        $stmt->execute([
            ':id' => $userId,
        ]);

        return !empty($stmt->fetchColumn());
    }

    public function unlinkGoogle(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE users SET google_id = NULL WHERE id = :id");

        return $stmt->execute([
            ':id' => $userId,
        ]);
    }
}