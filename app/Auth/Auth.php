<?php
namespace App\Auth;

use App\Mail\Mail;
use App\Models\User;
use App\Auth\RememberMe;
use App\Models\PasswordResetToken;
use App\Models\TwoFactorAuth;
use App\Models\LoginSession;
use App\Security\Device;
use App\Security\DeviceIdentifier;
use App\Services\RateLimiter;
use App\Services\TwoFactorService;
use PDO;

/**
 * Authentication service handling registration, login, session management,
 * email verification, password reset, and two-factor authentication.
 *
 * Security features:
 * - Persistent login sessions with device tracking
 * - Session versioning to invalidate sessions after password changes
 * - Remember-me tokens with rotation
 * - Rate limiting on sensitive operations
 * - Two-factor authentication with TOTP and recovery codes
 * - Google OAuth integration
 * - Email verification for new accounts
 *
 * Session state values used:
 * - user_id: Authenticated user ID
 * - role: Authenticated user role
 * - pending_username_user_id: User ID awaiting username completion
 * - pending_2fa_user_id: User ID awaiting 2FA verification
 * - pending_2fa_remember: Remember-me preference during 2FA
 * - pending_2fa_started_at: Timestamp when 2FA challenge began
 * - verification_email: Email awaiting verification
 * - password_reset_email: Email in password reset flow
 */
class Auth {
    private const PASSWORD_RESET_RESEND_AFTER = 60;
    private const VERIFICATION_RESEND_AFTER = 60;
    private const LOGIN_SESSION_TOUCH_INTERVAL = 300; // 5 minutes
    private const TWO_FACTOR_TIMEOUT = 600; // 10 minutes

    private User $users;
    private PDO $db;
    private Mail $mail;
    private RememberMe $rememberMe;
    private PasswordResetToken $passwordResetTokens;
    private RateLimiter $rateLimiter;
    private LoginSession $loginSessions;
    private TwoFactorAuth $twoFactorAuth;
    private TwoFactorService $twoFactorService;
    
    private array $errors = [];
    private string $errorType = 'validation';
    private array $meta = [];

    public function __construct(PDO $db, Mail $mail) {
        $this->db = $db;
        $this->users = new User($db);
        $this->mail = $mail;
        $this->rememberMe = new RememberMe($db);
        $this->passwordResetTokens = new PasswordResetToken($db);
        $this->rateLimiter = new RateLimiter($db);
        $this->loginSessions = new LoginSession($db);
        $this->twoFactorAuth = new TwoFactorAuth($db);
        $this->twoFactorService = new TwoFactorService($this->twoFactorAuth, $this->rateLimiter);
    }

    // =========================================================================
    // ERROR HANDLING
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
    // SESSION MANAGEMENT
    // =========================================================================
    public function boot(): void {
        if (isset($_SESSION['user_id'])) {
            return;
        }

        $remembered = $this->rememberMe->loginFromCookie();

        if ($remembered === null) {
            return;
        }

        $user = $this->users->findById($remembered['user_id']);

        if ($user === false) {
            $this->rememberMe->forget();
            return;
        }

        try {
            $loginSessionId = $this->establishAuthenticatedSession($user);

            $this->rememberMe->rotate((int) $remembered['token_id'], (int) $user['id'],
                $loginSessionId
            );

            $this->users->updateLastLogin((int) $user['id']);
        } catch (\Throwable) {
            $this->rememberMe->forget();
            unset($_SESSION['user_id'], $_SESSION['role']);
            throw new \RuntimeException('Unable to restore authenticated session.');
        }
    }

    public function check(): bool {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $sessionId = session_id();

        if ($sessionId === '') {
            $this->clearAuthenticatedState();
            return false;
        }

        $sessionIdHash = hash('sha256', $sessionId);
        $loginSession = $this->loginSessions->findBySessionHash($sessionIdHash);

        if ($loginSession === false) {
            $this->clearAuthenticatedState();
            return false;
        }

        if ((int) $loginSession['user_id'] !== (int) $_SESSION['user_id']) {
            $this->loginSessions->revoke((int) $loginSession['id'], (int) $_SESSION['user_id']);
            $this->clearAuthenticatedState();
            return false;
        }

        if ($loginSession['revoked_at'] !== null) {
            $this->clearAuthenticatedState();
            return false;
        }

        $user = $this->users->findById((int) $_SESSION['user_id']);

        if ($user === false) {
            $this->clearAuthenticatedState();
            return false;
        }

        $currentAuthSessionVersion = (int) ($user['auth_session_version'] ?? 1);
        $loginSessionVersion = (int) ($loginSession['auth_session_version'] ?? 1);

        if ($loginSessionVersion !== $currentAuthSessionVersion) {
            $this->loginSessions->revoke((int) $loginSession['id'], (int) $_SESSION['user_id']);
            $this->clearAuthenticatedState();
            return false;
        }

        $lastActivityAt = $loginSession['last_activity_at'] ?? null;
        $shouldTouch = $lastActivityAt === null
            || strtotime($lastActivityAt) <= time() - self::LOGIN_SESSION_TOUCH_INTERVAL;

        if ($shouldTouch) {
            $this->loginSessions->touch((int) $loginSession['id']);
        }

        return true;
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

    public function currentLoginSession(): ?array {
        if ($this->guest()) {
            return null;
        }

        $sessionId = session_id();

        if ($sessionId === '') {
            return null;
        }

        $sessionIdHash = hash('sha256', $sessionId);
        $session = $this->loginSessions->findBySessionHash($sessionIdHash);
        return $session ?: null;
    }

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

    public function logout(): void {
        $currentSession = $this->currentLoginSession();

        if ($currentSession !== null) {
            $userId = $this->id();

            if ($userId !== null) {
                $this->loginSessions->revoke((int) $currentSession['id'], $userId);
            }
        }

        $this->rememberMe->forget();
        $this->destroyPhpSession();
    }

    private function clearAuthenticatedState(): void {
        unset($_SESSION['user_id'], $_SESSION['role']);
    }

    private function establishAuthenticatedSession(array $user): int {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['role'] = $user['role'];
        $device = Device::detect();
        $deviceIdentifier = DeviceIdentifier::get();
        $deviceIdentifierHash = DeviceIdentifier::hash($deviceIdentifier);
        $sessionIdHash = hash('sha256', session_id());

        $loginSessionId = $this->loginSessions->create([
            'user_id'                => (int) $user['id'],
            'session_id_hash'        => $sessionIdHash,
            'device_identifier_hash' => $deviceIdentifierHash,
            'auth_session_version'   => (int) ($user['auth_session_version'] ?? 1),
            'device_type'            => $device['device_type'] ?? null,
            'brand'                  => $device['brand'] ?? null,
            'model'                  => $device['model'] ?? null,
            'os'                     => $device['os'] ?? null,
            'os_version'             => $device['os_version'] ?? null,
            'browser'                => $device['browser'] ?? null,
            'browser_version'        => $device['browser_version'] ?? null,
            'is_bot'                 => !empty($device['is_bot']),
            'ip_address'             => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent'             => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        if ($loginSessionId === false) {
            unset($_SESSION['user_id'], $_SESSION['role']);
            throw new \RuntimeException('Unable to create authenticated login session.');
        }

        return $loginSessionId;
    }

    private function destroyPhpSession(): void {
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
    // REGISTRATION
    // =========================================================================
    public function register(array $data): array|false {
        $this->resetState();
        
        if ($this->isRateLimited('register')) {
            return false;
        }

        $fullname = trim($data['fullname'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $confirm = $data['confirm_password'] ?? '';
        $provider = $data['provider'] ?? 'local';
        $acceptedTerms = !empty($data['terms']);

        if (!in_array($provider, ['local', 'google'], true)) {
            $provider = 'local';
        }

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

    public function completeRegistration(string $username): array|false {
        $this->resetState();
        $userId = $this->pendingUsernameUserId();

        if ($userId === null) {
            $this->errorType = 'auth';
            $this->addError('general', 'Your registration session is invalid or has expired.');
            return false;
        }

        if (!$this->checkUsername($username)) {
            return false;
        }

        $user = $this->users->findById($userId);

        if ($user === false) {
            unset($_SESSION['pending_username_user_id']);
            $this->addError('general', 'Unable to complete your registration.');
            return false;
        }

        if (!empty($user['username'])) {
            unset($_SESSION['pending_username_user_id']);
            $this->addError('general', 'Your registration has already been completed.');
            return false;
        }

        if (!$this->users->updateUsername($userId, trim($username))) {
            $this->addError('general', 'Unable to complete your registration.');
            return false;
        }

        $user = $this->users->findById($userId);

        if ($user === false) {
            $this->addError('general', 'Unable to complete your registration.');
            return false;
        }

        unset($_SESSION['pending_username_user_id']);
        return $this->authenticateUser($user, true);
    }

    private function pendingUsernameUserId(): ?int {
        if (!isset($_SESSION['pending_username_user_id'])) {
            return null;
        }

        return (int) $_SESSION['pending_username_user_id'];
    }

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

    private function createUserAccount(string $fullname, string $email, string $password, string $provider): array|false {
        $verificationToken = bin2hex(random_bytes(32));
        $verificationTokenHash = hash('sha256', $verificationToken);
        $verificationExpiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $hashedPassword = ($provider === 'local') 
            ? password_hash($password, PASSWORD_DEFAULT) : null;

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
    // EMAIL VERIFICATION
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

    // =========================================================================
    // LOGIN
    // =========================================================================
    public function login(array $data): array|false {
        $this->resetState();

        if ($this->isRateLimited('login')) {
            return false;
        }

        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $remember = !empty($data['remember']);

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

        $user = $this->users->findByEmail($email);

        if ($user === false) {
            return $this->failedLogin('Invalid email or password.');
        }

        if (empty($user['password'])) {
            return $this->failedLogin('This account uses Google Sign-In. Please continue with Google.');
        }

        if (!$this->users->verifyPassword($user, $password)) {
            return $this->failedLogin('Invalid email or password.');
        }

        if ($user['email_verified_at'] === null) {
            $this->errorType = 'auth';
            $this->addError('general', 'Please verify your email before signing in.');
            return false;
        }

        return $this->authenticateUser($user, $remember);
    }

    public function authenticateUser(array $user, bool $remember = false): array {
        if (empty($user['username'])) {
            session_regenerate_id(true);
            $_SESSION['pending_username_user_id'] = (int) $user['id'];
            return ['redirect' => '/auth/username'];
        }

        if ($this->twoFactorAuth->isEnabled((int) $user['id'])) {
            session_regenerate_id(true);
            $_SESSION['pending_2fa_user_id'] = (int) $user['id'];
            $_SESSION['pending_2fa_remember'] = $remember;
            $_SESSION['pending_2fa_started_at'] = time();
            return ['redirect' => '/auth/2fa'];
        }

        return $this->completeLogin($user, $remember);
    }

    private function failedLogin(string $message): false {
        $this->rateLimiter->hit('login');
        $this->errorType = 'auth';
        $this->addError('general', $message);
        return false;
    }

    private function completeLogin(array $user, bool $remember): array {
        $loginSessionId = $this->establishAuthenticatedSession($user);
        $this->users->updateLastLogin((int) $user['id']);
        $this->rateLimiter->clear('login');

        if ($remember) {
            $this->rememberMe->create(
                (int) $user['id'],
                $loginSessionId
            );
        }

        return [
            'redirect' => $this->getDashboardRedirect($user),
        ];
    }

    // =========================================================================
    // TWO-FACTOR AUTHENTICATION
    // =========================================================================
    public function hasPendingTwoFactor(): bool {
        return isset($_SESSION['pending_2fa_user_id']);
    }

    public function pendingTwoFactorUserId(): ?int {
        if (!isset($_SESSION['pending_2fa_user_id'])) {
            return null;
        }

        return (int) $_SESSION['pending_2fa_user_id'];
    }

    public function verifyTwoFactor(string $code): array|false {
        $this->resetState();
        $this->errorType = 'auth';

        if ($this->isRateLimited('two_factor')) {
            return false;
        }

        $userId = $this->pendingTwoFactorUserId();

        if ($userId === null) {
            $this->addError('general', 'Your two-factor authentication session is invalid.');
            return false;
        }

        if (!$this->isTwoFactorSessionValid()) {
            return false;
        }

        $setup = $this->twoFactorAuth->findByUserId($userId);

        if ($setup === false || $setup['enabled_at'] === null) {
            $this->clearPendingTwoFactor();
            $this->addError('general', 'Two-factor authentication is not enabled for this account.');
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
            $this->rateLimiter->hit('two_factor');
            $this->addError('code', 'Invalid authentication code.');
            return false;
        }

        $this->rateLimiter->clear('two_factor');
        return $this->completePendingTwoFactorLogin();
    }

    public function verifyTwoFactorRecoveryCode(string $code): array|false {
        $this->resetState();
        $this->errorType = 'auth';

        if ($this->isRateLimited('two_factor')) {
            return false;
        }

        $userId = $this->pendingTwoFactorUserId();

        if ($userId === null) {
            $this->addError('general', 'Your two-factor authentication session is invalid.');
            return false;
        }

        if (!$this->isTwoFactorSessionValid()) {
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
            $this->rateLimiter->hit('two_factor');
            $this->addError('code', 'Invalid recovery code.');
            return false;
        }

        $remainingCodesJson = json_encode($remainingCodes, JSON_THROW_ON_ERROR);

        if (!$this->twoFactorAuth->updateRecoveryCodes($userId, $remainingCodesJson)) {
            $this->addError('general', 'Unable to update your recovery codes.');
            return false;
        }

        $this->rateLimiter->clear('two_factor');
        return $this->completePendingTwoFactorLogin();
    }

    private function isTwoFactorSessionValid(): bool {
        $startedAt = $_SESSION['pending_2fa_started_at'] ?? null;

        if (!is_int($startedAt) && !ctype_digit((string) $startedAt)) {
            $this->clearPendingTwoFactor();
            $this->addError('general', 'Your two-factor authentication session has expired.');
            return false;
        }

        if (time() - (int) $startedAt > self::TWO_FACTOR_TIMEOUT) {
            $this->clearPendingTwoFactor();
            $this->addError('general', 'Your two-factor authentication session has expired.');
            return false;
        }

        return true;
    }

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

    private function clearPendingTwoFactor(): void {
        unset($_SESSION['pending_2fa_user_id'], $_SESSION['pending_2fa_remember'],
            $_SESSION['pending_2fa_started_at']
        );
    }

    // =========================================================================
    // PASSWORD RESET
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

    private function executePasswordReset(int $userId, int $recordId, string $password): bool {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->db->beginTransaction();

        try {
            if (!$this->users->updatePassword($userId, $hash)) {
                throw new \RuntimeException('Unable to update password.');
            }

            if (!$this->users->incrementAuthSessionVersion($userId)) {
                throw new \RuntimeException('Unable to invalidate existing authentication sessions.');
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
    // UTILITIES
    // =========================================================================
    private function getDashboardRedirect(array $user): string {
        return match ($user['role']) {
            'admin', 'moderator' => '/home',
            'member' => '/dashboard',
            default => '/dashboard',
        };
    }
}