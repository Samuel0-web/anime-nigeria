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
                :verification_token,
                CURRENT_TIMESTAMP,
                :verification_expires_at
            )
        ");

        $success = $stmt->execute([
            ':fullname' => $data['fullname'],
            ':username' => !empty($data['username']) ? $data['username'] : null,
            ':email'    => $data['email'],
            ':password' => $data['password'],
            ':provider' => $data['provider'] ?? 'local',
            ':verification_token' => $data['verification_token'],
            ':verification_expires_at' => $data['verification_expires_at'],
        ]);

        if (! $success) {
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
}