<?php
namespace App\Services;

use App\Models\LoginSession;

class LoginSessionService {
    private LoginSession $loginSessions;

    public function __construct(LoginSession $loginSessions) {
        $this->loginSessions = $loginSessions;
    }

    /**
     * Get all active login sessions belonging to a user.
     *
     * Revoked sessions are excluded because they are no longer
     * active authentication sessions.
     */
    public function getActiveSessions(int $userId): array {
        return $this->loginSessions->findActiveByUserId($userId);
    }

    /**
     * Get a specific active login session belonging to a user.
     */
    public function getActiveSession(int $userId, int $sessionId): ?array {
        return $this->loginSessions->findActiveById($userId, $sessionId) ?: null;
    }

    /**
     * Revoke a specific login session belonging to a user.
     */
    public function revoke(int $userId, int $sessionId): bool {
        $session = $this->getActiveSession($userId, $sessionId);

        if ($session === null) {
            return false;
        }

        return $this->loginSessions->revoke($sessionId, $userId);
    }

    /**
     * Revoke all active login sessions belonging to a user.
     */
    public function revokeAll(int $userId): bool {
        return $this->loginSessions->revokeAllForUser($userId);
    }

    /**
     * Revoke all active login sessions except the current session.
     */
    public function revokeOthers(int $userId): bool {
        $sessionId = session_id();

        if ($sessionId === '') {
            return false;
        }

        $currentSessionHash = hash('sha256', $sessionId);
        return $this->loginSessions->revokeOthers($userId, $currentSessionHash);
    }
}