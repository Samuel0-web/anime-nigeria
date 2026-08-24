<?php
namespace App\Auth;

use App\Models\User;
use PDO;

class GoogleAuth {
    // =========================================================================
    // CONSTANTS
    // =========================================================================
    private const STATE_TIMEOUT = 600; // 10 minutes

    // =========================================================================
    // PROPERTIES
    // =========================================================================
    private PDO $db;
    private User $users;
    private GoogleClient $google;
    private Auth $auth;
    private array $errors = [];

    // =========================================================================
    // CONSTRUCTOR
    // =========================================================================
    public function __construct(PDO $db, GoogleClient $google, Auth $auth) {
        $this->db = $db;
        $this->users = new User($db);
        $this->google = $google;
        $this->auth = $auth;
    }

    // =========================================================================
    // PUBLIC API - Error Handling
    // =========================================================================
    
    public function errors(): array {
        return $this->errors;
    }

    public function error(): ?string {
        return $this->errors[0] ?? null;
    }

    // =========================================================================
    // PUBLIC API - OAuth Flow
    // =========================================================================
    
    /**
     * Redirect the user to Google's OAuth screen.
     */
    public function redirect(): void {
        $state = bin2hex(random_bytes(32));
        
        $_SESSION['google_oauth'] = [
            'state' => $state,
            'created_at' => time(),
        ];

        header('Location: ' . $this->google->getAuthorizationUrl($state));
        exit;
    }

    /**
     * Handle Google's OAuth callback.
     */
    public function callback(?string $code, ?string $state, ?string $error = null): array|false {
        $this->errors = [];

        // Handle OAuth errors
        if ($this->hasOAuthError($error)) {
            return false;
        }

        // Validate state
        if (!$this->validateOAuthState($state)) {
            return false;
        }

        // Validate code
        if (empty($code)) {
            $this->addError('Google sign-in could not be completed. Please try again.');
            return false;
        }

        // Exchange code for user info
        $googleUser = $this->getGoogleUser($code);
        if ($googleUser === false) {
            return false;
        }

        // Validate Google user data
        if (!$this->validateGoogleUser($googleUser)) {
            return false;
        }

        // Handle existing Google account
        $user = $this->users->findByGoogleId($googleUser['sub']);
        if ($user !== false) {
            return $this->handleExistingGoogleAccount($user, $googleUser);
        }

        // Handle existing email or new account
        return $this->handleEmailMatch($googleUser);
    }

    /**
     * Complete Google registration after accepting Terms.
     */
    public function completeRegistration(array $data): array|false {
        $this->errors = [];

        if (empty($_SESSION['google_register'])) {
            $this->addError('Registration session expired.');
            return false;
        }

        if (empty($data['terms'])) {
            $this->addError('You must agree to the Terms and Privacy Policy.');
            return false;
        }

        return $this->createGoogleAccount($_SESSION['google_register']);
    }

    // =========================================================================
    // PRIVATE - Error Handling
    // =========================================================================
    
    private function addError(string $message): void {
        $this->errors[] = $message;
    }

    private function hasOAuthError(?string $error): bool {
        if (!empty($error)) {
            $this->addError('Google sign in was cancelled.');
            return true;
        }
        return false;
    }

    // =========================================================================
    // PRIVATE - OAuth State Validation
    // =========================================================================
    
    private function validateOAuthState(?string $state): bool {
        // Check session exists
        if (empty($_SESSION['google_oauth']) || 
            empty($_SESSION['google_oauth']['state']) ||
            empty($_SESSION['google_oauth']['created_at'])) {
            $this->addError('Your Google sign-in session has expired. Please try again.');
            return false;
        }

        $oauth = $_SESSION['google_oauth'];

        // Check timeout
        if (time() - $oauth['created_at'] > self::STATE_TIMEOUT) {
            unset($_SESSION['google_oauth']);
            $this->addError('Your Google sign-in session has expired. Please try again.');
            return false;
        }

        // Verify state matches
        if (empty($state) || !hash_equals($oauth['state'], $state)) {
            unset($_SESSION['google_oauth']);
            $this->addError('Your Google sign-in session has expired. Please try again.');
            return false;
        }

        unset($_SESSION['google_oauth']);
        return true;
    }

    // =========================================================================
    // PRIVATE - Google API Interaction
    // =========================================================================
    
    private function getGoogleUser(string $code): array|false {
        try {
            $token = $this->google->exchangeCode($code);
            $googleUser = $this->google->getUser($token['access_token']);

            if (empty($googleUser['email_verified']) || $googleUser['email_verified'] !== true) {
                $this->addError('Please verify your Google email address before signing in.');
                return false;
            }

            return $googleUser;
        } catch (\Throwable) {
            $this->addError('We could not sign you in with Google. Please try again.');
            return false;
        }
    }

    private function validateGoogleUser(array $googleUser): bool {
        if (empty($googleUser['sub']) || empty($googleUser['email']) 
            || empty($googleUser['name'])
        ) {
            $this->addError('We could not retrieve your Google account information.');
            return false;
        }
        return true;
    }

    // =========================================================================
    // PRIVATE - Account Handling
    // =========================================================================
    
    private function handleExistingGoogleAccount(array $user,
        array $googleUser
    ): array|false {
        // Verify email matches.
        if (strtolower($user['email']) !== strtolower($googleUser['email'])) {
            $this->addError(
                'We could not verify your Google account. Please try again.'
            );

            return false;
        }

        // Update avatar.
        $this->users->updateAvatar((int) $user['id'], $googleUser['picture'] ?? null);
        $user['avatar'] = $googleUser['picture'] ?? null;
        return $this->auth->authenticateUser($user, true);
    }

    private function handleEmailMatch(array $googleUser): array|false {
        $user = $this->users->findByEmail(strtolower($googleUser['email']));

        // Existing email - link accounts
        if ($user !== false) {
            return $this->linkAccount($user, $googleUser);
        }

        // Brand new user - store for registration
        $_SESSION['google_register'] = [
            'fullname' => $googleUser['name'],
            'email' => strtolower($googleUser['email']),
            'google_id' => $googleUser['sub'],
            'avatar' => $googleUser['picture'] ?? null,
        ];

        return [
            'redirect' => '/join/google',
        ];
    }

    // =========================================================================
    // PRIVATE - Account Linking
    // =========================================================================
    private function linkAccount(array $user, array $googleUser): array|false {
        // Email must be verified.
        if (empty($user['email_verified_at'])) {
            $this->addError('Please verify your email before signing in with Google.');
            return false;
        }

        /*
        * Never replace an existing Google identity with a different one.
        */
        $currentGoogleId = trim((string) ($user['google_id'] ?? ''));

        if ($currentGoogleId !== '' &&
            !hash_equals($currentGoogleId, $googleUser['sub'])
        ) {
            $this->addError(
                'This account is already linked to a different Google account.'
            );

            return false;
        }

        // Check whether this Google identity belongs to another user.
        $existing = $this->users->findByGoogleId($googleUser['sub']);

        if ($existing !== false && (int) $existing['id'] !== (int) $user['id']) {
            $this->addError('This Google account is already linked to another user.');
            return false;
        }

        $this->db->beginTransaction();

        try {
            if (!$this->users->linkGoogleAccount((int) $user['id'], $googleUser['sub'],
                $googleUser['picture'] ?? null
            )) {
                throw new \RuntimeException('Unable to link Google account.');
            }

            $this->db->commit();
            $user = $this->users->findById((int) $user['id']);

            if ($user === false) {
                throw new \RuntimeException('Unable to reload linked account.');
            }

            return $this->auth->authenticateUser($user, true);
        } catch (\Throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            $this->addError('Unable to link your Google account.');
            return false;
        }
    }

    // =========================================================================
    // PRIVATE - Account Creation
    // =========================================================================
    private function createGoogleAccount(array $googleUser): array|false {
        $this->db->beginTransaction();
        try {
            $userId = $this->users->create([
                'fullname' => $googleUser['fullname'],
                'email' => strtolower($googleUser['email']),
                'provider' => 'google',
                'google_id' => $googleUser['google_id'],
                'avatar' => $googleUser['avatar'] ?? null,
                'email_verified_at' => date('Y-m-d H:i:s'),
            ]);

            if ($userId === false) {
                throw new \RuntimeException('Unable to create Google account.');
            }

            $this->db->commit();
            unset($_SESSION['google_oauth'], $_SESSION['google_register']);
            
            $_SESSION['pending_username_user_id'] = $userId;
            session_regenerate_id(true);

            return [
                'redirect' => '/auth/username',
            ];
        } catch (\Throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->addError('Unable to create your account.');
            return false;
        }
    }
}