<?php
namespace App\Models;
use PDO;

class LoginSession {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Create a new authenticated login session.
     */
    public function create(array $data): int|false {
        $stmt = $this->db->prepare("INSERT INTO login_sessions 
            (
                user_id,
                session_id_hash,
                device_identifier_hash,
                auth_session_version,
                device_type,
                brand,
                model,
                os,
                os_version,
                browser,
                browser_version,
                is_bot,
                ip_address,
                user_agent,
                created_at,
                last_activity_at
            )
            VALUES (
                :user_id,
                :session_id_hash,
                :device_identifier_hash,
                :auth_session_version,
                :device_type,
                :brand,
                :model,
                :os,
                :os_version,
                :browser,
                :browser_version,
                :is_bot,
                :ip_address,
                :user_agent,
                CURRENT_TIMESTAMP,
                CURRENT_TIMESTAMP
            )
        ");

        $success = $stmt->execute([
            ':user_id'                  => $data['user_id'],
            ':session_id_hash'          => $data['session_id_hash'],
            ':device_identifier_hash'   => $data['device_identifier_hash'],
            ':auth_session_version'     => $data['auth_session_version'],
            ':device_type'              => $data['device_type'] ?? null,
            ':brand'                    => $data['brand'] ?? null,
            ':model'                    => $data['model'] ?? null,
            ':os'                       => $data['os'] ?? null,
            ':os_version'               => $data['os_version'] ?? null,
            ':browser'                  => $data['browser'] ?? null,
            ':browser_version'          => $data['browser_version'] ?? null,
            ':is_bot'                   => !empty($data['is_bot']) ? 1 : 0,
            ':ip_address'               => $data['ip_address'] ?? null,
            ':user_agent'               => $data['user_agent'] ?? null,
        ]);

        if (!$success) {
            return false;
        }

        return (int) $this->db->lastInsertId();
    }

    /**
     * Find a login session using the hashed PHP session ID.
     */
    public function findBySessionHash(string $sessionIdHash): array|false {
        $stmt = $this->db->prepare("SELECT * FROM login_sessions
            WHERE session_id_hash = :session_id_hash LIMIT 1
        ");

        $stmt->execute([
            ':session_id_hash' => $sessionIdHash,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find an active login session for a user on a specific device.
     */
    public function findActiveByDevice(int $userId,
        string $deviceIdentifierHash
    ): array|false {
        $stmt = $this->db->prepare("SELECT * FROM login_sessions WHERE user_id = :user_id
            AND device_identifier_hash = :device_identifier_hash AND revoked_at IS NULL
            LIMIT 1
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':device_identifier_hash' => $deviceIdentifierHash,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all login sessions belonging to a user.
     */
    public function findByUserId(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM login_sessions WHERE user_id = :user_id
            ORDER BY last_activity_at DESC
        ");

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findActiveById(int $userId, int $sessionId): array|false {
        $stmt = $this->db->prepare("SELECT * FROM login_sessions WHERE id = :id
            AND user_id = :user_id AND revoked_at IS NULL LIMIT 1
        ");

        $stmt->execute([
            ':id' => $sessionId,
            ':user_id' => $userId,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findActiveByUserId(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM login_sessions WHERE user_id = :user_id
            AND revoked_at IS NULL ORDER BY last_activity_at DESC
        ");

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update the last activity timestamp when enough time has passed.
     *
     * This prevents authenticated requests from writing to the database
     * on every request while still keeping activity reasonably fresh.
     */
    public function touch(int $id, int $interval = 300): bool {
        $stmt = $this->db->prepare("UPDATE login_sessions
            SET last_activity_at = CURRENT_TIMESTAMP WHERE id = :id
            AND revoked_at IS NULL AND (last_activity_at IS NULL
                OR last_activity_at <= CURRENT_TIMESTAMP - INTERVAL :interval SECOND
            )
        ");

        return $stmt->execute([
            ':id' => $id,
            ':interval' => $interval,
        ]);
    }

    /**
     * Revoke a specific login session.
     */
    public function revoke(int $id, int $userId): bool {
        $stmt = $this->db->prepare("UPDATE login_sessions
            SET revoked_at = CURRENT_TIMESTAMP WHERE id = :id AND user_id = :user_id
            AND revoked_at IS NULL
        ");

        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
        ]);
    }

    /**
     * Revoke every login session belonging to a user.
     */
    public function revokeAllForUser(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE login_sessions
            SET revoked_at = CURRENT_TIMESTAMP WHERE user_id = :user_id
            AND revoked_at IS NULL
        ");

        return $stmt->execute([
            ':user_id' => $userId,
        ]);
    }

    /**
     * Revoke all sessions except the current session.
     */
    public function revokeOthers(int $userId, string $currentSessionHash): bool {
        $stmt = $this->db->prepare("UPDATE login_sessions
            SET revoked_at = CURRENT_TIMESTAMP WHERE user_id = :user_id
            AND session_id_hash != :session_id_hash AND revoked_at IS NULL
        ");

        return $stmt->execute([
            ':user_id' => $userId,
            ':session_id_hash' => $currentSessionHash,
        ]);
    }
}