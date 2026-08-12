<?php
namespace App\Auth;

use App\Mail\Mail;
use App\Models\User;
use App\Auth\RememberMe;
use App\Models\PasswordResetToken;
use App\Models\TwoFactorAuth;
use App\Services\RateLimiter;
use App\Services\TwoFactorService;
use PDO;

class Auth {
    // =========================================================================
    // CONSTANTS
    // =========================================================================
    private const PASSWORD_RESET_RESEND_AFTER = 60;
    private const VERIFICATION_RESEND_AFTER = 60;

    // =========================================================================
    // PROPERTIES
    // =========================================================================
    private User $users;
    private PDO $db;
    private Mail $mail;
    private RememberMe $rememberMe;
    private PasswordResetToken $passwordResetTokens;
    private RateLimiter $rateLimiter;
    private TwoFactorAuth $twoFactorAuth;
    private TwoFactorService $twoFactorService;
    
    private array $errors = [];
    private string $errorType = 'validation';
    private array $meta = [];

    // =========================================================================
    // CONSTRUCTOR
    // =========================================================================
    public function __construct(PDO $db, Mail $mail) {
        $this->db = $db;
        $this->users = new User($db);
        $this->mail = $mail;
        $this->rememberMe = new RememberMe($db);
        $this->passwordResetTokens = new PasswordResetToken($db);
        $this->rateLimiter = new RateLimiter($db);
        $this->twoFactorAuth = new TwoFactorAuth($db);
        $this->twoFactorService = new TwoFactorService($this->twoFactorAuth, $this->rateLimiter);
    }

    // =========================================================================
    // PUBLIC API - State & Error Handling
    // =========================================================================
    
    public function meta(): array {
        return $this->meta;
    }

    public function errors(): array {
        return $this->errors;
    }

    public function error(string $field): ?string {
        return $this->errors[$field] ?? null;
    }

    public function errorType(): string {
        return $this->errorType;
    }

    public function hasErrors(): bool {
        return !empty($this->errors);
    }

    // =========================================================================
    // PUBLIC API - Authentication State
    // =========================================================================
    
    public function boot(): void {
        $this->rememberMe->loginFromCookie();
    }

    public function check(): bool {
        return isset($_SESSION['user_id']);
    }

    public function guest(): bool {
        return !$this->check();
    }

    public function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public function user(): ?array {
        $id = $this->id();

        if ($id === null) {
            return null;
        }
        
        return $this->users->findById($id) ?: null;
    }

    /**
     * Verify a password against the currently authenticated user's stored hash.
     *
     * Used by sensitive account actions (e.g. disabling 2FA) that require
     * password re-confirmation outside of the login flow.
     */
    public function verifyPassword(string $password): bool {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        return $this->users->verifyPassword($user, $password);
    }

    public function requireAuth(string $redirect = '/login'): void {
        if ($this->guest()) {
            header("Location: {$redirect}");
            exit;
        }
    }

    public function requireGuest(string $redirect = '/dashboard'): void {
        if ($this->check()) {
            header("Location: {$redirect}");
            exit;
        }
    }

    // =========================================================================
    // PUBLIC API - Registration Flow
    // =========================================================================
    
    public function register(array $data): array|false {
        $this->resetState();
        
        if ($this->isRateLimited('register')) {
            return false;
        }

        // Clean input
        $fullname = trim($data['fullname'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $confirm = $data['confirm_password'] ?? '';
        $provider = $data['provider'] ?? 'local';
        $acceptedTerms = !empty($data['terms']);

        if (!in_array($provider, ['local', 'google'], true)) {
            $provider = 'local';
        }

        // Validate
        if (!$this->validateRegistration($fullname, $email, $acceptedTerms)) {
            $this->rateLimiter->hit('register');
            return false;
        }

        if ($provider === 'local') {
            $this->validatePassword($password, $confirm);
        }

        if ($this->hasErrors()) {
            $this->rateLimiter->hit('register');
            return false;
        }

        // Create account
        return $this->createUserAccount($fullname, $email, $password, $provider);
    }

    public function checkUsername(string $username): bool {
        $this->errors = [];
        $this->meta = [];
        $this->errorType = 'validation';

        if ($this->rateLimiter->tooManyAttempts('username')) {
            $this->meta['cooldown'] = $this->rateLimiter->remainingSeconds('username');
            $this->addError('username', 'Too many attempts.');
            return false;
        }

        $username = trim($username);

        if ($username === '') {
            $this->addError('username', 'Username is required.');
            return false;
        }

        if (strlen($username) < 3 || strlen($username) > 20) {
            $this->addError('username', 'Username must be between 3 and 20 characters.');
            return false;
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
            $this->addError('username', 'Only letters, numbers and underscores are allowed.');
            return false;
        }

        $reserved = require __DIR__ . '/../../config/reserved_usernames.php';
        if (in_array(strtolower($username), $reserved, true)) {
            $this->addError('username', 'This username is reserved.');
            return false;
        }

        if ($this->users->usernameExists($username)) {
            $this->addError('username', 'Username is already taken.');
            return false;
        }

        if ($this->hasErrors()) {
            $this->rateLimiter->hit('username');
            return false;
        }

        $this->rateLimiter->clear('username');
        return true;
    }

    public function completeRegistration(int $userId, string $username): bool {
        if (!$this->checkUsername($username)) {
            return false;
        }

        if (!$this->users->updateUsername($userId, trim($username))) {
            $this->addError('general', 'Unable to complete your registration.');
            return false;
        }

        unset($_SESSION['pending_username_user_id']);
        $_SESSION['user_id'] = $userId;
        session_regenerate_id(true);
        return true;
    }

    // =========================================================================
    // PUBLIC API - Email Verification
    // =========================================================================
    
    public function verifyEmail(string $token): array|false {
        $this->errors = [];
        $this->errorType = 'auth';
        $token = trim($token);

        if ($token === '') {
            $this->addError('token', 'Verification token is missing.');
            return false;
        }

        $user = $this->users->findByVerificationToken($token);
        if ($user === false) {
            $this->addError('token', 'This verification link is invalid, has already been used, or has expired.');
            return false;
        }

        if (!empty($user['verification_expires_at']) && 
            strtotime($user['verification_expires_at']) < time()) {
            $this->addError('token', 'This verification link has expired.');
            return false;
        }

        if (!$this->users->markEmailVerified((int)$user['id'])) {
            $this->addError('general', 'Unable to verify your email.');
            return false;
        }

        unset($_SESSION['verification_email']);

        if (empty($user['username'])) {
            $_SESSION['pending_username_user_id'] = (int)$user['id'];
        }

        return [
            'status' => 'verified',
            'user' => $user,
        ];
    }

    public function resendVerification(string $email): array|false {
        $this->resetState();

        if ($this->isRateLimited('resend_verification')) {
            return false;
        }

        $result = $this->sendVerificationEmail($email);
        $this->updateRateLimit('resend_verification', $result !== false);
        return $result;
    }

    // =========================================================================
    // PUBLIC API - Login/Logout
    // =========================================================================
    
    public function login(array $data): array|false {
        $this->resetState();

        if ($this->isRateLimited('login')) {
            return false;
        }

        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $remember = !empty($data['remember']);

        // Basic validation
        if ($email === '') {
            $this->addError('email', 'Email is required.');
        }

        if ($password === '') {
            $this->addError('password', 'Password is required.');
        }

        if ($this->hasErrors()) {
            $this->rateLimiter->hit('login');
            return false;
        }

        // Find user
        $user = $this->users->findByEmail($email);

        if ($user === false) {
            return $this->failedLogin('Invalid email or password.');
        }

        // Check provider
        if (empty($user['password'])) {
            return $this->failedLogin(
                'This account uses Google Sign-In. Please continue with Google.'
            );
        }

        // Verify password
        if (!$this->users->verifyPassword($user, $password)) {
            return $this->failedLogin('Invalid email or password.');
        }

        // Check email verification
        if ($user['email_verified_at'] === null) {
            $this->errorType = 'auth';
            $this->addError('general', 'Please verify your email before signing in.'
            );

            return false;
        }

        // Check username completion
        if (empty($user['username'])) {
            $_SESSION['pending_username_user_id'] = (int) $user['id'];

            return [
                'redirect' => '/auth/username',
            ];
        }

        /*
        * Password authentication has succeeded at this point.
        *
        * Do NOT create the authenticated session yet.
        * If 2FA is enabled, the user must complete the second factor first.
        */
        if ($this->twoFactorAuth->isEnabled((int) $user['id'])) {
            $_SESSION['pending_2fa_user_id'] = (int) $user['id'];
            $_SESSION['pending_2fa_remember'] = $remember;

            /*
            * Store only the fact that password authentication succeeded.
            * Do not store the password itself.
            */
            $_SESSION['pending_2fa_started_at'] = time();
            $this->rateLimiter->clear('login');

            return [
                'redirect' => '/auth/2fa',
            ];
        }

        // No 2FA: complete login normally.
        return $this->completeLogin($user, $remember);
    }

    /**
     * Check whether there is a pending 2FA login.
     */
    public function hasPendingTwoFactor(): bool {
        return isset($_SESSION['pending_2fa_user_id']);
    }

    /**
     * Get the user ID currently waiting for 2FA.
     */
    public function pendingTwoFactorUserId(): ?int {
        if (!isset($_SESSION['pending_2fa_user_id'])) {
            return null;
        }

        return (int) $_SESSION['pending_2fa_user_id'];
    }


    /**
     * Complete a login using a TOTP authentication code.
     */
    public function verifyTwoFactor(string $code): array|false {
        $this->resetState();
        $this->errorType = 'auth';
        $userId = $this->pendingTwoFactorUserId();

        if ($userId === null) {
            $this->addError('general', 'Your two-factor authentication session is invalid.');
            return false;
        }

        /*
        * Prevent abandoned 2FA sessions from remaining valid indefinitely.
        */
        $startedAt = $_SESSION['pending_2fa_started_at'] ?? null;

        if (!is_int($startedAt) && !ctype_digit((string) $startedAt)) {
            $this->clearPendingTwoFactor();
            $this->addError('general', 'Your two-factor authentication session has expired.');
            return false;
        }

        if (time() - (int) $startedAt > 10 * 60) {
            $this->clearPendingTwoFactor();
            $this->addError('general', 'Your two-factor authentication session has expired.');
            return false;
        }

        $setup = $this->twoFactorAuth->findByUserId($userId);

        if ($setup === false || $setup['enabled_at'] === null) {
            $this->clearPendingTwoFactor();

            $this->addError(
                'general', 'Two-factor authentication is not enabled for this account.'
            );

            return false;
        }

        $code = trim($code);

        if ($code === '') {
            $this->addError('code', 'Authentication code is required.');
            return false;
        }

        if (!preg_match('/^\d{6}$/', $code)) {
            $this->addError('code', 'Enter the 6-digit authentication code.');
            return false;
        }

        if (!$this->twoFactorService->verifyCode($setup['secret'], $code)) {
            $this->addError('code', 'Invalid authentication code.');
            return false;
        }

        return $this->completePendingTwoFactorLogin();
    }


    /**
     * Complete a login using a recovery code.
     */
    public function verifyTwoFactorRecoveryCode(string $code): array|false {
        $this->resetState();
        $this->errorType = 'auth';
        $userId = $this->pendingTwoFactorUserId();

        if ($userId === null) {
            $this->addError('general', 'Your two-factor authentication session is invalid.');
            return false;
        }

        $startedAt = $_SESSION['pending_2fa_started_at'] ?? null;

        if (!is_int($startedAt) && !ctype_digit((string) $startedAt)) {
            $this->clearPendingTwoFactor();
            $this->addError('general', 'Your two-factor authentication session has expired.');
            return false;
        }

        if (time() - (int) $startedAt > 10 * 60) {
            $this->clearPendingTwoFactor();
            $this->addError('general', 'Your two-factor authentication session has expired.');
            return false;
        }

        $storedCodes = $this->twoFactorAuth->getRecoveryCodes($userId);

        if ($storedCodes === false || $storedCodes === null) {
            $this->addError('code', 'No recovery codes are available for this account.');
            return false;
        }

        $code = strtoupper(trim($code));

        if ($code === '') {
            $this->addError('code', 'Recovery code is required.');
            return false;
        }

        $remainingCodes = $this->twoFactorService->verifyRecoveryCode($code, $storedCodes);

        if ($remainingCodes === false) {
            $this->addError('code', 'Invalid recovery code.');
            return false;
        }

        $remainingCodesJson = json_encode($remainingCodes, JSON_THROW_ON_ERROR);

        if (!$this->twoFactorAuth->updateRecoveryCodes($userId, $remainingCodesJson)) {
            $this->addError('general', 'Unable to update your recovery codes.');
            return false;
        }

        /*
        * A recovery code is single-use.
        * Once successfully consumed, finish authentication normally.
        */
        return $this->completePendingTwoFactorLogin();
    }

    /**
     * Complete authentication after successful 2FA.
     */
    private function completePendingTwoFactorLogin(): array {
        $userId = $this->pendingTwoFactorUserId();

        if ($userId === null) {
            throw new \RuntimeException('Unable to complete two-factor authentication.');
        }

        $remember = !empty($_SESSION['pending_2fa_remember']);
        $user = $this->users->findById($userId);

        if ($user === false) {
            $this->clearPendingTwoFactor();
            throw new \RuntimeException('Unable to find the authenticated user.');
        }

        $this->clearPendingTwoFactor();
        return $this->completeLogin($user, $remember);
    }


    /**
     * Clear the temporary 2FA authentication state.
     */
    private function clearPendingTwoFactor(): void {
        unset($_SESSION['pending_2fa_user_id'], $_SESSION['pending_2fa_remember'],
            $_SESSION['pending_2fa_started_at']
        );
    }

    public function logout(): void {
        $this->rememberMe->forget();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '',
                [
                    'expires' => time() - 3600,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Strict',
                ]
            );
        }

        session_destroy();
    }

    // =========================================================================
    // PUBLIC API - Password Reset
    // =========================================================================
    
    public function forgotPassword(string $email): array|false {
        $this->errors = [];
        $this->errorType = 'auth';

        if ($this->isRateLimited('forgot_password')) {
            return false;
        }

        $result = $this->sendPasswordResetEmail($email);
        $this->updateRateLimit('forgot_password', $result !== false);
        return $result;
    }

    public function resendPasswordReset(string $email): array|false {
        $this->resetState();

        if ($this->isRateLimited('resend_password_reset')) {
            return false;
        }

        $result = $this->sendPasswordResetEmail($email);
        $this->updateRateLimit('resend_password_reset', $result !== false);
        return $result;
    }

    public function resetPassword(string $token, array $data): bool {
        $this->errors = [];
        $this->errorType = 'auth';
        $password = $data['password'] ?? '';
        $confirm = $data['confirm_password'] ?? '';
        $token = trim($token);

        if ($token === '') {
            $this->addError('general', 'Invalid password reset link.');
            return false;
        }

        $this->validatePassword($password, $confirm);
        if ($this->hasErrors()) {
            return false;
        }

        $record = $this->passwordResetTokens->findByToken($token);
        if (!$record) {
            $this->addError('general', 'This password reset link is invalid.');
            return false;
        }

        $user = $this->users->findById((int)$record['user_id']);
        if ($user && $user['auth_provider'] === 'google') {
            $this->passwordResetTokens->deleteByUser((int)$record['user_id']);
            $this->addError('general', 'This account uses Google Sign-In. Please continue with Google.');
            return false;
        }

        if (strtotime($record['expires_at']) < time()) {
            $this->passwordResetTokens->deleteByUser((int)$record['user_id']);
            unset($_SESSION['password_reset_email']);
            $this->addError('general', 'This password reset link has expired.');
            return false;
        }

        return $this->executePasswordReset((int)$record['user_id'], (int)$record['id'], $password);
    }

    // =========================================================================
    // PRIVATE - State Management
    // =========================================================================
    
    private function resetState(): void {
        $this->errors = [];
        $this->meta = [];
        $this->errorType = 'validation';
    }

    private function addError(string $field, string $message): void {
        $this->errors[$field] = $message;
    }

    private function isRateLimited(string $action): bool {
        if ($this->rateLimiter->tooManyAttempts($action)) {
            $this->errorType = 'auth';
            $this->meta['cooldown'] = $this->rateLimiter->remainingSeconds($action);
            $this->addError('general', 'Too many attempts.');
            return true;
        }
        return false;
    }

    private function updateRateLimit(string $action, bool $success): void {
        if ($success) {
            $this->rateLimiter->clear($action);
        } else {
            $this->rateLimiter->hit($action);
        }
    }

    // =========================================================================
    // PRIVATE - Validation
    // =========================================================================
    
    private function validateRegistration(string $fullname, string $email, bool $acceptedTerms): bool {
        if ($fullname === '') {
            $this->addError('fullname', 'Full name is required.');
        } elseif (mb_strlen($fullname) > 100) {
            $this->addError('fullname', 'Full name is too long.');
        }

        if ($email === '') {
            $this->addError('email', 'Email is required.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Enter a valid email address.');
        } else {
            $this->validateUniqueEmail($email);
        }

        if (!$acceptedTerms) {
            $this->addError('terms', 'You must agree to the Terms and Privacy Policy.');
        }

        return empty($this->errors);
    }

    private function validateUniqueEmail(string $email): void {
        $existingUser = $this->users->findByEmail($email);
        if ($existingUser === false) {
            return;
        }

        if ($existingUser['email_verified_at'] !== null && empty($existingUser['username'])) {
            $this->addError('email', 'This email has already been verified. Sign in to continue.');
        } elseif ($existingUser['email_verified_at'] === null) {
            $this->addError('email', 'An account with this email already exists. Please verify your email first.');
        } else {
            $this->addError('email', 'An account with this email already exists.');
        }
    }

    private function validatePassword(string $password, string $confirm): void {
        if ($password === '') {
            $this->addError('password', 'Password is required.');
        } elseif (strlen($password) < 8) {
            $this->addError('password', 'Password must be at least 8 characters.');
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $this->addError('password', 'Password must contain at least one uppercase letter.');
        } elseif (!preg_match('/[0-9]/', $password)) {
            $this->addError('password', 'Password must contain at least one number.');
        } elseif (!preg_match('/[!@#$%&*?,]/', $password)) {
            $this->addError('password', 'Password must contain at least one symbol.');
        }

        if ($confirm === '') {
            $this->addError('confirm_password', 'Please confirm your password.');
        } elseif ($password !== $confirm) {
            $this->addError('confirm_password', 'Passwords do not match.');
        }
    }

    // =========================================================================
    // PRIVATE - Registration Helpers
    // =========================================================================
    
    private function createUserAccount(string $fullname, string $email, string $password, string $provider): array|false {
        $verificationToken = bin2hex(random_bytes(32));
        $verificationTokenHash = hash('sha256', $verificationToken);
        $verificationExpiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $hashedPassword = ($provider === 'local') 
            ? password_hash($password, PASSWORD_DEFAULT) 
            : null;

        $this->db->beginTransaction();
        try {
            $userId = $this->users->create([
                'fullname' => $fullname,
                'email' => $email,
                'password' => $hashedPassword,
                'provider' => $provider,
                'verification_token' => $verificationTokenHash,
                'verification_expires_at' => $verificationExpiresAt,
            ]);

            if ($userId === false) {
                throw new \RuntimeException('Unable to create account.');
            }

            $this->mail->sendVerificationEmail($email, $fullname, $verificationToken);
            $_SESSION['verification_email'] = $email;
            $this->db->commit();
            $this->rateLimiter->clear('register');

            return [
                'id' => $userId,
                'email' => $email,
                'verification_token' => $verificationToken,
                'verification_expires_at' => $verificationExpiresAt,
                'verification_sent_at' => date('Y-m-d H:i:s'),
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // =========================================================================
    // PRIVATE - Email Sending
    // =========================================================================
    
    private function sendVerificationEmail(string $email): array|false {
        $this->errors = [];
        $email = strtolower(trim($email));

        if ($email === '') {
            $this->addError('email', 'Email is required.');
            return false;
        }

        $user = $this->users->findByEmail($email);
        if ($user === false) {
            return [
                'email' => $email,
                'resend_after' => self::VERIFICATION_RESEND_AFTER,
            ];
        }

        if ($user['email_verified_at'] !== null) {
            $this->addError('general', 'This email has already been verified.');
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $this->users->updateVerificationToken((int)$user['id'], $tokenHash, $expiresAt);
        $this->mail->sendVerificationEmail($user['email'], $user['fullname'], $token);
        $_SESSION['verification_email'] = $user['email'];

        return [
            'email' => $user['email'],
            'resend_after' => self::VERIFICATION_RESEND_AFTER,
        ];
    }

    private function sendPasswordResetEmail(string $email): array|false {
        $this->errors = [];
        $email = strtolower(trim($email));

        if ($email === '') {
            $this->addError('email', 'Email is required.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Enter a valid email address.');
        }

        if ($this->hasErrors()) {
            return false;
        }

        $user = $this->users->findByEmail($email);
        
        // Never reveal whether the email exists
        if ($user === false) {
            return [
                'email' => $email,
                'resend_after' => self::PASSWORD_RESET_RESEND_AFTER,
            ];
        }

        if ($user['auth_provider'] === 'google') {
            $this->addError('email', 'This account uses Google Sign-In. Please continue with Google.');
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $this->passwordResetTokens->create((int)$user['id'], $tokenHash, $expiresAt);
        $_SESSION['password_reset_email'] = $user['email'];
        $this->mail->sendPasswordResetEmail($user['email'], $user['fullname'], $token);

        return [
            'email' => $user['email'],
            'resend_after' => self::PASSWORD_RESET_RESEND_AFTER,
        ];
    }

    // =========================================================================
    // PRIVATE - Login/Logout Helpers
    // =========================================================================
    
    private function failedLogin(string $message): false {
        $this->rateLimiter->hit('login');
        $this->errorType = 'auth';
        $this->addError('general', $message);
        return false;
    }

    private function completeLogin(array $user, bool $remember): array {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $this->users->updateLastLogin((int) $user['id']);
        $this->rateLimiter->clear('login');

        if ($remember) {
            $this->rememberMe->create((int)$user['id']);
        }

        return [
            'redirect' => $this->getDashboardRedirect($user),
        ];
    }

    // =========================================================================
    // PRIVATE - Password Reset Helpers
    // =========================================================================
    
    private function executePasswordReset(int $userId, int $recordId, string $password): bool {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $this->db->beginTransaction();
        try {
            if (!$this->users->updatePassword($userId, $hash)) {
                throw new \RuntimeException('Unable to update password.');
            }

            $this->passwordResetTokens->delete($recordId);
            $this->rememberMe->deleteAllForUser($userId);
            unset($_SESSION['password_reset_email']);
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // =========================================================================
    // PRIVATE - Utilities
    // =========================================================================
    
    private function getDashboardRedirect(array $user): string {
        return match ($user['role']) {
            'admin', 'moderator' => '/home',
            'member' => '/dashboard',
            default => '/dashboard',
        };
    }
}