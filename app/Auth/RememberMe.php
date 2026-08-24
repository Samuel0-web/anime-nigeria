<?php
namespace App\Auth;
use PDO;

class RememberMe {
    // =========================================================================
    // CONSTANTS
    // =========================================================================
    private const COOKIE_NAME = 'remember_me';
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
    public function create(int $userId): void {
        $token = $this->generateToken($userId);
        $this->setCookie($token['selector'], $token['validator']);
    }

    private function incrementAuthSessionVersion(int $userId): void {
        $stmt = $this->db->prepare("UPDATE users
            SET auth_session_version = auth_session_version + 1 WHERE id = ?"
        );

        $stmt->execute([$userId]);
    }

    /**
     * Attempt to authenticate the user via the remember me cookie.
     *
     * Returns authenticated user data on success, or null when the
     * remember me cookie cannot be used.
     */
    public function loginFromCookie(): ?array {
        // Already logged in
        if (isset($_SESSION['user_id'])) {
            return null;
        }

        // No cookie present
        if (!$this->hasCookie()) {
            return null;
        }

        // Parse and validate cookie format
        $parts = $this->parseCookie();

        if ($parts === null) {
            $this->clearCookie();
            return null;
        }

        [$selector, $validator] = $parts;

        // Find the token in database
        $token = $this->findToken($selector);

        if (!$token) {
            $this->forget();
            return null;
        }

        // Check if token has expired
        if ($this->isTokenExpired($token)) {
            $this->deleteTokenById((int) $token['id']);
            $this->clearCookie();
            return null;
        }

        // Verify validator using constant-time comparison
        if (!$this->validateToken($token, $validator)) {
            $this->handleCompromisedToken($token);
            return null;
        }

        /*
        * The remember-me token has successfully authenticated
        * the user.
        *
        * Rotate the token before returning control to Auth.
        */
        $this->completeLogin($token);

        return [
            'user_id' => (int) $token['user_id'],
        ];
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
    public function deleteAllForUser(int $userId): void {
        $stmt = $this->db->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

    // =========================================================================
    // PRIVATE - Token Generation & Storage
    // =========================================================================
    
    /**
     * Generate a new remember me token and store it in the database.
     */
    private function generateToken(int $userId): array {
        $selector = bin2hex(random_bytes(self::SELECTOR_BYTES));
        $validator = bin2hex(random_bytes(self::VALIDATOR_BYTES));
        $validatorHash = hash('sha256', $validator);
        $expiresAt = date('Y-m-d H:i:s', time() + self::LIFETIME);

        $stmt = $this->db->prepare(
            "INSERT INTO remember_tokens (user_id, selector, validator_hash, expires_at) 
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $selector, $validatorHash, $expiresAt]);

        return [
            'selector' => $selector,
            'validator' => $validator,
        ];
    }

    /**
     * Find a token by its selector.
     */
    private function findToken(string $selector): ?array {
        $stmt = $this->db->prepare("SELECT remember_tokens.* FROM remember_tokens
            WHERE selector = ? LIMIT 1"
        );

        $stmt->execute([$selector]);
        $token = $stmt->fetch(PDO::FETCH_ASSOC);
        return $token ?: null;
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
    
    /**
     * Check if a token has expired.
     */
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
     * Deletes the suspicious token and clears the cookie.
     */
    private function handleCompromisedToken(array $token): void {
        $userId = (int) $token['user_id'];

        // Invalidate every remember-me token.
        $this->deleteAllForUser($userId);

        // Invalidate every existing authenticated session.
        $this->incrementAuthSessionVersion($userId);

        // Clear the compromised cookie.
        $this->clearCookie();
    }

    // =========================================================================
    // PRIVATE - Cookie Management
    // =========================================================================
    
    /**
     * Check if the remember me cookie exists.
     */
    private function hasCookie(): bool {
        return !empty($_COOKIE[self::COOKIE_NAME]);
    }

    /**
     * Parse the remember me cookie into selector and validator.
     */
    private function parseCookie(): ?array {
        if (!$this->hasCookie()) {
            return null;
        }

        $parts = explode(self::COOKIE_SEPARATOR, $_COOKIE[self::COOKIE_NAME], 2);

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
        
        setcookie(
            self::COOKIE_NAME,
            $value,
            [
                'expires' => time() + self::LIFETIME,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
            ]
        );
    }

    /**
     * Clear the remember me cookie.
     */
    private function clearCookie(): void {
        setcookie(self::COOKIE_NAME, '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
            ]
        );

        unset($_COOKIE[self::COOKIE_NAME]);
    }

    // =========================================================================
    // PRIVATE - Login Completion
    // =========================================================================
    
    /**
     * Rotate a successfully used remember-me token.
     */
    private function completeLogin(array $token): void {
        // Remove the used token
        $this->deleteTokenById((int) $token['id']);

        // Issue a fresh token
        $this->create((int) $token['user_id']);
    }
}