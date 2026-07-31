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

    /**
     * Attempt to log the user in via the remember me cookie.
     */
    public function loginFromCookie(): bool {
        // Already logged in
        if (isset($_SESSION['user_id'])) {
            return true;
        }

        // No cookie present
        if (!$this->hasCookie()) {
            return false;
        }

        // Parse and validate cookie format
        $parts = $this->parseCookie();
        if ($parts === null) {
            return false;
        }

        [$selector, $validator] = $parts;

        // Find the token in database
        $token = $this->findToken($selector);
        if (!$token) {
            $this->forget();
            return false;
        }

        // Check if token has expired
        if ($this->isTokenExpired($token)) {
            $this->deleteTokenById($token['id']);
            $this->forget();
            return false;
        }

        // Verify the validator (constant-time comparison)
        if (!$this->validateToken($token, $validator)) {
            // Possible stolen cookie - invalidate all tokens for security
            $this->handleCompromisedToken($token);
            return false;
        }

        // Valid token - log user in and rotate token
        $this->completeLogin($token);
        return true;
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
        $stmt = $this->db->prepare("SELECT * FROM remember_tokens WHERE selector = ? LIMIT 1");
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
        // Delete the potentially stolen token
        $this->deleteTokenById($token['id']);
        
        // Clear the cookie to prevent further attempts
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
        $parts = explode(self::COOKIE_SEPARATOR, $_COOKIE[self::COOKIE_NAME], 2);
        
        if (count($parts) !== 2 || empty($parts[0]) || empty($parts[1])) {
            return null;
        }

        return $parts;
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
                'secure' => !empty($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict',
            ]
        );
    }

    /**
     * Clear the remember me cookie.
     */
    private function clearCookie(): void {
        setcookie(
            self::COOKIE_NAME,
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']),
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
     * Complete the login process and rotate the token.
     */
    private function completeLogin(array $token): void {
        // Log the user in
        session_regenerate_id(true);
        $_SESSION['user_id'] = $token['user_id'];

        // Remove the used token (token rotation for security)
        $this->deleteTokenById($token['id']);

        // Issue a new token
        $this->create((int)$token['user_id']);
    }
}