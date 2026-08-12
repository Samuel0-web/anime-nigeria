<?php
namespace App\Models;
use PDO;

class TwoFactorAuth {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Find a user's 2FA configuration.
     */
    public function findByUserId(int $userId): array|false {
        $stmt = $this->db->prepare("SELECT * FROM two_factor_auth WHERE user_id = :user_id
            LIMIT 1
        ");

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check whether a user has 2FA enabled.
     */
    public function isEnabled(int $userId): bool {
        $stmt = $this->db->prepare("SELECT enabled_at FROM two_factor_auth
            WHERE user_id = :user_id LIMIT 1
        ");

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        $enabledAt = $stmt->fetchColumn();
        return $enabledAt !== false && $enabledAt !== null;
    }

    /**
     * Create or replace a user's 2FA setup.
     *
     * The setup remains disabled until the user successfully
     * verifies their authenticator code.
     */
    public function create(int $userId, string $secret, string $setupExpiresAt): bool {
        $stmt = $this->db->prepare("INSERT INTO two_factor_auth (
                user_id,
                secret,
                setup_expires_at
            )
            VALUES (
                :user_id,
                :secret,
                :setup_expires_at
            )
            ON DUPLICATE KEY UPDATE secret = VALUES(secret),
                setup_expires_at = VALUES(setup_expires_at), recovery_codes = NULL,
                enabled_at = NULL
        ");

        return $stmt->execute([
            ':user_id' => $userId,
            ':secret' => $secret,
            ':setup_expires_at' => $setupExpiresAt,
        ]);
    }

    /**
     * Enable 2FA after successful verification.
     */
    public function enable(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE two_factor_auth SET enabled_at = CURRENT_TIMESTAMP,
            setup_expires_at = NULL WHERE user_id = :user_id
        ");

        return $stmt->execute([
            ':user_id' => $userId,
        ]);
    }

    /**
     * Delete a user's 2FA configuration.
     */
    public function delete(int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM two_factor_auth WHERE user_id = :user_id");

        return $stmt->execute([
            ':user_id' => $userId,
        ]);
    }

    /**
     * Delete a user's 2FA setup only if it is still pending (not yet enabled).
     *
     * Used when a user abandons Step 2 of setup. This must never remove an
     * already-enabled configuration — that's what delete()/2fa.disable is for.
     * The WHERE clause is the actual guarantee here, not the PHP call site.
     */
    public function deletePendingSetup(int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM two_factor_auth
            WHERE user_id = :user_id AND enabled_at IS NULL
        ");

        return $stmt->execute([
            ':user_id' => $userId,
        ]);
    }

    /**
     * Get the stored TOTP secret.
     */
    public function getSecret(int $userId): string|false {
        $stmt = $this->db->prepare("SELECT secret FROM two_factor_auth WHERE user_id = :user_id
            LIMIT 1
        ");

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        return $stmt->fetchColumn();
    }

    /**
     * Get the setup expiration timestamp.
     */
    public function getSetupExpiresAt(int $userId): string|false {
        $stmt = $this->db->prepare("SELECT setup_expires_at FROM two_factor_auth
            WHERE user_id = :user_id LIMIT 1
        ");

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        return $stmt->fetchColumn();
    }

    /**
     * Check whether the current 2FA setup has expired.
     */
    public function isSetupExpired(int $userId): bool {
        $stmt = $this->db->prepare("SELECT setup_expires_at FROM two_factor_auth
            WHERE user_id = :user_id LIMIT 1
        ");

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        $expiresAt = $stmt->fetchColumn();

        if ($expiresAt === false || $expiresAt === null) {
            return true;
        }

        $expiration = new \DateTimeImmutable($expiresAt, new \DateTimeZone('UTC'));
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        return $expiration <= $now;
    }

    /**
     * Get stored recovery codes.
     */
    public function getRecoveryCodes(int $userId): string|false {
        $stmt = $this->db->prepare("SELECT recovery_codes FROM two_factor_auth
            WHERE user_id = :user_id LIMIT 1
        ");

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        return $stmt->fetchColumn();
    }

    /**
     * Store hashed recovery codes.
     */
    public function updateRecoveryCodes(int $userId, string $recoveryCodes): bool {
        $stmt = $this->db->prepare("UPDATE two_factor_auth SET recovery_codes = :recovery_codes
            WHERE user_id = :user_id
        ");

        return $stmt->execute([
            ':user_id' => $userId,
            ':recovery_codes' => $recoveryCodes,
        ]);
    }
}