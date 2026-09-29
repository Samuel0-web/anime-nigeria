<?php
namespace App\Auth;
use PDO;
use App\Security\Cookie;

class RememberMe {
    // =========================================================================
    // CONSTANTS
    // =========================================================================
    private const LIFETIME = 60 * 60 * 24 * 30; // 30 days
    private const SELECTOR_BYTES = 12;
    private const VALIDATOR_BYTES = 32;
    private const COOKIE_SEPARATOR = ':';

    // =========================================================================
    // PROPERTIES
    // =========================================================================
    public function __construct(private PDO $db) {}

    // =========================================================================
    // PUBLIC API
    // =========================================================================
    
    /**
     * Create a new "remember me" token and set the cookie.
     */
    public function create(int $userId, int $loginSessionId): void {
        $token = $this->generateToken($userId, $loginSessionId);
        $this->setCookie($token['selector'], $token['validator']);
    }

    private function incrementAuthSessionVersion(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE users
            SET auth_session_version = auth_session_version + 1 WHERE id = ?"
        );

        return $stmt->execute([$userId]) && $stmt->rowCount() === 1;
    }

    /** Consume a valid remember credential once and atomically establish its replacement. */
    public function restoreAndRotate(callable $establishSession): bool {
        // Already logged in
        if (isset($_SESSION['user_id'])) {
            return false;
        }

        // No cookie present
        if (!$this->hasCookie()) {
            return false;
        }

        // Parse and validate cookie format
        $parts = $this->parseCookie();

        if ($parts === null) {
            $this->clearCookie();
            return false;
        }

        [$selector, $validator] = $parts;

        try {
            $this->db->beginTransaction();
            $token = $this->findTokenForUpdate($selector);

            if ($token === null) {
                $this->db->commit();
                $this->clearCookie();
                return false;
            }

            if ($this->isTokenExpired($token)) {
                $this->deleteTokenById((int) $token['id']);
                $this->db->commit();
                $this->clearCookie();
                return false;
            }

            if (!$this->validateToken($token, $validator)) {
                $this->handleCompromisedToken($token);
                $this->db->commit();
                $this->clearCookie();
                return false;
            }

            $linkedSession = $this->findRestorableSession($token);

            if ($linkedSession === null) {
                $this->deleteTokenById((int) $token['id']);
                $this->db->commit();
                $this->clearCookie();
                return false;
            }

            $consume = $this->db->prepare('DELETE FROM remember_tokens WHERE id = :id
                AND validator_hash = :validator_hash AND expires_at > CURRENT_TIMESTAMP');
            $consume->execute([
                ':id' => (int) $token['id'],
                ':validator_hash' => hash('sha256', $validator),
            ]);

            if ($consume->rowCount() !== 1) {
                $this->db->rollBack();
                $this->clearCookie();
                return false;
            }

            $userId = (int) $token['user_id'];
            $loginSessionId = (int) $establishSession($userId);

            if ($loginSessionId < 1) {
                throw new \RuntimeException('Unable to create remembered login session.');
            }

            $replacement = $this->generateToken($userId, $loginSessionId);
            $this->db->commit();
            $this->setCookie($replacement['selector'], $replacement['validator']);
            return true;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Remove the remember me cookie and database record.
     */
    public function forget(): void {
        if ($this->hasCookie()) {
            $parts = $this->parseCookie();
            if ($parts !== null) {
                $this->deleteTokenBySelector($parts[0]);
            }
        }

        $this->clearCookie();
    }

    /**
     * Delete all remember me tokens for a user (e.g., on password reset).
     */
    public function deleteAllForUser(int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    // =========================================================================
    // PRIVATE - Token Generation & Storage
    // =========================================================================
    
    /**
     * Generate a new remember me token and store it in the database.
     */
    private function generateToken(int $userId, int $loginSessionId): array {
        $selector = bin2hex(random_bytes(self::SELECTOR_BYTES));
        $validator = bin2hex(random_bytes(self::VALIDATOR_BYTES));
        $validatorHash = hash('sha256', $validator);
        $expiresAt = date('Y-m-d H:i:s', time() + self::LIFETIME);

        $stmt = $this->db->prepare("INSERT INTO remember_tokens
                (
                    user_id,
                    login_session_id,
                    selector,
                    validator_hash,
                    expires_at
                )
                VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $userId,
            $loginSessionId,
            $selector,
            $validatorHash,
            $expiresAt
        ]);

        return [
            'selector' => $selector,
            'validator' => $validator,
        ];
    }

    /**
     * Find a token by its selector.
     */
    private function findTokenForUpdate(string $selector): ?array {
        $stmt = $this->db->prepare('SELECT * FROM remember_tokens WHERE selector = ?
            LIMIT 1 FOR UPDATE');
        $stmt->execute([$selector]);
        $token = $stmt->fetch(PDO::FETCH_ASSOC);
        return $token ?: null;
    }

    private function findRestorableSession(array $token): ?array {
        if ($token['login_session_id'] === null) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT ls.id FROM login_sessions ls
            INNER JOIN users u ON u.id = ls.user_id
            WHERE ls.id = :session_id AND ls.user_id = :user_id
                AND ls.revoked_at IS NULL
                AND ls.auth_session_version = u.auth_session_version
                AND u.email_verified_at IS NOT NULL
                AND u.banned_at IS NULL
                AND (u.suspended_until IS NULL OR u.suspended_until <= CURRENT_TIMESTAMP)
                AND u.deleted_at IS NULL
            LIMIT 1 FOR UPDATE');
        $stmt->execute([
            ':session_id' => (int) $token['login_session_id'],
            ':user_id' => (int) $token['user_id'],
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Delete a token by its ID.
     */
    private function deleteTokenById(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM remember_tokens WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Delete a token by its selector.
     */
    private function deleteTokenBySelector(string $selector): void {
        $stmt = $this->db->prepare("DELETE FROM remember_tokens WHERE selector = ?");
        $stmt->execute([$selector]);
    }

    // =========================================================================
    // PRIVATE - Token Validation
    // =========================================================================
    
    /** Remember-me has a separate 30-day lifetime; normal session idle age does not apply. */
    private function isTokenExpired(array $token): bool {
        return strtotime($token['expires_at']) < time();
    }

    /**
     * Verify the token validator using constant-time comparison.
     */
    private function validateToken(array $token, string $validator): bool {
        return hash_equals($token['validator_hash'], hash('sha256', $validator));
    }

    // =========================================================================
    // PRIVATE - Security Handling
    // =========================================================================
    
    /**
     * Handle potentially compromised token (invalid validator).
    * Deletes the suspicious token and invalidates existing sessions.
     */
    private function handleCompromisedToken(array $token): void {
        $userId = (int) $token['user_id'];

        // Invalidate every remember-me token.
        $stmt = $this->db->prepare('DELETE FROM remember_tokens WHERE user_id = ?');
        if (!$stmt->execute([$userId])) {
            throw new \RuntimeException('Unable to invalidate remember-me credentials.');
        }

        // Invalidate every existing authenticated session.
        if (!$this->incrementAuthSessionVersion($userId)) {
            throw new \RuntimeException('Unable to invalidate authenticated sessions.');
        }

    }

    // =========================================================================
    // PRIVATE - Cookie Management
    // =========================================================================
    
    /**
     * Check if the remember me cookie exists.
     */
    private function hasCookie(): bool {
        return Cookie::get($this->cookieName()) !== null;
    }

    /**
     * Parse the remember me cookie into selector and validator.
     */
    private function parseCookie(): ?array {
        if (!$this->hasCookie()) {
            return null;
        }

        $cookie = Cookie::get($this->cookieName());
        if ($cookie === null) {
            return null;
        }

        $parts = explode(self::COOKIE_SEPARATOR, $cookie, 2);

        if (count($parts) !== 2) {
            return null;
        }

        [$selector, $validator] = $parts;

        if (
            !preg_match('/^[a-f0-9]{' . (self::SELECTOR_BYTES * 2) . '}$/', $selector) ||
            !preg_match('/^[a-f0-9]{' . (self::VALIDATOR_BYTES * 2) . '}$/', $validator)
        ) {
            return null;
        }

        return [$selector, $validator];
    }

    /**
     * Set the remember me cookie.
     */
    private function setCookie(string $selector, string $validator): void {
        $value = $selector . self::COOKIE_SEPARATOR . $validator;

        Cookie::set($this->cookieName(), $value, self::LIFETIME);
    }

    /**
     * Clear the remember me cookie.
     */
    private function clearCookie(): void {
        Cookie::forget($this->cookieName());
    }

    private function cookieName(): string {
        return \App\Core\Config::cookieName('REMEMBER_ME_COOKIE');
    }

    // =========================================================================
    // PRIVATE - Login Completion
    // =========================================================================
    
}