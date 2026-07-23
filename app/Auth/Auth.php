<?php
namespace App\Auth;
use App\Mail\Mail;
use App\Models\User;
use App\Auth\RememberMe;
use App\Models\PasswordResetToken;
use PDO;

class Auth {
    private User $users;
    private PDO $db;
    private Mail $mail;
    private RememberMe $rememberMe;
    private PasswordResetToken $passwordResetTokens;
    private const PASSWORD_RESET_RESEND_AFTER = 60;

    /**
     * Validation / auth errors
     */
    private array $errors = [];
    private string $errorType = 'validation';

    public function __construct(PDO $db, Mail $mail) {
        $this->db = $db;
        $this->users = new User($db);
        $this->mail = $mail;
        $this->rememberMe = new RememberMe($db);
        $this->passwordResetTokens = new PasswordResetToken($db);
    }

    /**
     * Return all validation errors
     */
    public function errors(): array {
        return $this->errors;
    }

    public function errorType(): string {
        return $this->errorType;
    }

    /**
     * Return first error for a field
     */
    public function error(string $field): ?string {
        return $this->errors[$field] ?? null;
    }

    private function addError(string $field, string $message): void {
        $this->errors[$field] = $message;
    }

    public function hasErrors(): bool {
        return !empty($this->errors);
    }

    /**
     * Register a user
     */
    public function register(array $data): array|false {
        $this->errors = [];

        // Clean input
        $fullname = trim($data['fullname'] ?? '');
        $email    = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        $provider = $data['provider'] ?? 'local';
        $acceptedTerms = !empty($data['terms']);

        if (!in_array($provider, ['local', 'google'], true)) {
            $provider = 'local';
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

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
            $existingUser = $this->users->findByEmail($email);

            if ($existingUser !== false) {

                // Email verified, but onboarding not finished.
                if ($existingUser['email_verified_at'] !== null && empty($existingUser['username']) ) {
                    $this->addError(
                        'email',
                        'This email has already been verified. Sign in to continue.'
                    );

                // Email still waiting for verification.
                } elseif ($existingUser['email_verified_at'] === null) {
                    $this->addError(
                        'email',
                        'An account with this email already exists. Please verify your email first.'
                    );

                // Fully registered.
                } else {
                    $this->addError(
                        'email',
                        'An account with this email already exists.'
                    );
                }
            }
        }

        if ($provider === 'local') {
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

            if ($confirmPassword === '') {
                $this->addError('confirm_password', 'Please confirm your password.');
            } elseif ($password !== $confirmPassword) {
                $this->addError('confirm_password', 'Passwords do not match.');
            }
        }

        if (!$acceptedTerms) {
            $this->addError('terms', 'You must agree to the Terms and Privacy Policy.');
        }

        if (!empty($this->errors)) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Create account
        |--------------------------------------------------------------------------
        */
        $verificationToken = bin2hex(random_bytes(32));
        $verificationTokenHash = hash('sha256', $verificationToken);
        $verificationExpiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $hashedPassword = null;

        if ($provider === 'local') {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->db->beginTransaction();

        try {
            $userId = $this->users->create([
                'fullname' => $fullname,
                'email'    => $email,
                'password' => $hashedPassword,
                'provider' => $provider,
                'verification_token' => $verificationTokenHash,
                'verification_expires_at' => $verificationExpiresAt,
            ]);

            if ($userId === false) {
                $this->db->rollBack();
                return false;
            }

            $this->mail->sendVerificationEmail(
                $email,
                $fullname,
                $verificationToken
            );

            $this->db->commit();

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

    private function sendPasswordReset(string $email): array|false {
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

        /*
        |--------------------------------------------------------------------------
        | Never reveal whether the email exists.
        |--------------------------------------------------------------------------
        */
        if ($user === false) {
            return [
                'email' => $email,
                'resend_after' => self::PASSWORD_RESET_RESEND_AFTER,
            ];
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $this->passwordResetTokens->create((int) $user['id'], $tokenHash, $expiresAt);
        $_SESSION['password_reset_email'] = $user['email'];
        $this->mail->sendPasswordResetEmail($user['email'], $user['fullname'], $token);

        return [
            'email' => $user['email'],
            'resend_after' => self::PASSWORD_RESET_RESEND_AFTER,
        ];
    }

    public function forgotPassword(string $email): array|false {
        return $this->sendPasswordReset($email);
    }

    public function resendPasswordReset(string $email): array|false {
        return $this->sendPasswordReset($email);
    }

    public function resetPassword(string $token, array $data): bool {
        $this->errors = [];
        $password = $data['password'] ?? '';
        $confirm = $data['confirm_password'] ?? '';
        $token = trim($token);

        if ($token === '') {
            $this->addError('general', 'Invalid password reset link.');
            return false;
        }

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
            $this->addError('confirm_password', 'Confirm your password.');
        } elseif ($password !== $confirm) {
            $this->addError('confirm_password', 'Passwords do not match.');
        }

        if ($this->hasErrors()) {
            return false;
        }

        $record = $this->passwordResetTokens->findByToken($token);

        if (!$record) {
            $this->addError('general', 'This password reset link is invalid.');
            return false;
        }

        if (strtotime($record['expires_at']) < time()) {
            $this->passwordResetTokens->deleteByUser((int)$record['user_id']);
            unset($_SESSION['password_reset_email']);
            $this->addError('general', 'This password reset link has expired.');
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->db->beginTransaction();

        try {
            if (!$this->users->updatePassword((int)$record['user_id'], $hash)) {
                throw new \RuntimeException('Unable to update password.');
            }

            $this->passwordResetTokens->delete((int)$record['id']);
            $this->rememberMe->deleteAllForUser((int)$record['user_id']);
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

    /**
     * Verify a user's email address.
     */
    public function verifyEmail(string $token): array|false {
        $this->errors = [];
        $token = trim($token);

        if ($token === '') {
            $this->addError('token', 'Verification token is missing.');
            return false;
        }

        $user = $this->users->findByVerificationToken($token);

        if ($user === false) {
            $this->addError(
                'token', 'This verification link is invalid, has already been used, or has expired.'
            );

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

        return [
            'status' => 'verified',
            'user' => $user,
        ];
    }

    /**
     * Validate and check username availability.
     */
    public function checkUsername(string $username): bool {
        $this->errors = [];
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

        // User is now fully onboarded.
        unset($_SESSION['pending_username_user_id']);
        $_SESSION['user_id'] = $userId;
        session_regenerate_id(true);
        return true;
    }

    public function login(array $data): array|false {
        $this->errors = [];
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
            return false;
        }

        $user = $this->users->findByEmail($email);

        if ($user === false) {
            $this->errorType = 'auth';
            $this->addError('general', 'Invalid email or password.');
            return false;
        }

        if (!$this->users->verifyPassword($user, $password)) {
            $this->errorType = 'auth';
            $this->addError('general', 'Invalid email or password.');
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | CASE 3
        | Email not verified
        |--------------------------------------------------------------------------
        */
        if ($user['email_verified_at'] === null) {
            $this->errorType = 'auth';
            $this->addError('general', 'Please verify your email before signing in.');
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | CASE 2
        | Verified but username not chosen
        |--------------------------------------------------------------------------
        */
        if (empty($user['username'])) {
            $_SESSION['pending_username_user_id'] = $user['id'];

            return [
                'redirect' => '/auth/username',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | CASE 1
        | Fully registered
        |--------------------------------------------------------------------------
        */
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];

        if ($remember) {
            $this->rememberMe->create((int)$user['id']);
        }

        return [
            'redirect' => '/dashboard',
        ];
    }

    public function logout(): void {
        $this->rememberMe->forget();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires'  => time() - 3600,
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Strict',
                ]
            );
        }

        session_destroy();
    }

    /**
     * Check if the current user is authenticated.
     */
    public function check(): bool {
        return isset($_SESSION['user_id']);
    }

    /**
     * Check if the current visitor is a guest.
     */
    public function guest(): bool {
        return !$this->check();
    }

    /**
     * Get the authenticated user's ID.
     */
    public function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Redirect guests to the login page.
     */
    public function requireAuth(string $redirect = '/login'): void {
        if ($this->guest()) {
            header("Location: {$redirect}");
            exit;
        }
    }

    /**
     * Redirect authenticated users away from guest-only pages.
     */
    public function requireGuest(string $redirect = '/dashboard'): void {
        if ($this->check()) {
            header("Location: {$redirect}");
            exit;
        }
    }

    public function boot(): void {
        $this->rememberMe->loginFromCookie();
    }
}