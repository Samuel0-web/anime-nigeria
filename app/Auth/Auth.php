<?php
namespace App\Auth;
use App\Mail\Mail;
use App\Models\User;
use PDO;

class Auth {
    private User $users;
    private Mail $mail;

    /**
     * Validation / auth errors
     */
    private array $errors = [];

    public function __construct(PDO $db, Mail $mail) {
        $this->users = new User($db);
        $this->mail = $mail;
    }

    /**
     * Return all validation errors
     */
    public function errors(): array {
        return $this->errors;
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
        } elseif ($this->users->emailExists($email)) {
            $this->addError('email', 'Email is already registered.');
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

        $userId = $this->users->create([
            'fullname' => $fullname,
            'email'    => $email,
            'password' => $hashedPassword,
            'provider' => $provider,
            'verification_token' => $verificationTokenHash,
            'verification_expires_at' => $verificationExpiresAt,
        ]);

        if ($userId === false) {
            return false;
        }

        $sent = $this->mail->sendVerificationEmail(
            $email,
            $fullname,
            $verificationToken
        );

        if (! $sent) {
            $this->users->delete($userId);

            $this->addError(
                'general',
                'We were unable to send the verification email. Please try again.'
            );

            return false;
        }

        return [
            'id' => $userId,
            'email' => $email,
            'verification_token' => $verificationToken,
            'verification_expires_at' => $verificationExpiresAt,
            'verification_sent_at' => date('Y-m-d H:i:s'),
        ];
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

        if (!empty($user['email_verification_expires_at']) &&
            strtotime($user['email_verification_expires_at']) < time()) {
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
}