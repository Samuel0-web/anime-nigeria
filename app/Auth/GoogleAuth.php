<?php
namespace App\Auth;
use App\Models\User;
use PDO;

class GoogleAuth {
    private PDO $db;
    private User $users;
    private GoogleClient $google;
    private RememberMe $rememberMe;

    /**
     * OAuth / authentication errors.
     */
    private array $errors = [];

    public function __construct(PDO $db, GoogleClient $google) {
        $this->db = $db;
        $this->users = new User($db);
        $this->google = $google;
        $this->rememberMe = new RememberMe($db);
    }

    /**
     * Return validation / OAuth errors.
     */
    public function errors(): array {
        return $this->errors;
    }

    /**
     * Return the first error.
     */
    public function error(): ?string {
        return $this->errors[0] ?? null;
    }

    private function addError(string $message): void {
        $this->errors[] = $message;
    }

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

        if (!empty($error)) {
            $this->addError('Google sign in was cancelled.');
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Validate OAuth state
        |--------------------------------------------------------------------------
        */
        if (empty($_SESSION['google_oauth']) || empty($_SESSION['google_oauth']['state']) ||
            empty($_SESSION['google_oauth']['created_at'])
        ) {
            $this->addError('Invalid Google authentication request.');
            return false;
        }

        $oauth = $_SESSION['google_oauth'];

        if (time() - $oauth['created_at'] > 600) {
            unset($_SESSION['google_oauth']);
            $this->addError('Google authentication request expired.');
            return false;
        }

        if (empty($state) || !hash_equals($oauth['state'], $state)) {
            unset($_SESSION['google_oauth']);
            $this->addError('Invalid Google authentication request.');
            return false;
        }

        unset($_SESSION['google_oauth']);

        /*
        |--------------------------------------------------------------------------
        | Authorization code missing
        |--------------------------------------------------------------------------
        */
        if (empty($code)) {
            $this->addError('Google did not return an authorization code.');
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Exchange code for access token
        |--------------------------------------------------------------------------
        */
        try {
            $token = $this->google->exchangeCode($code);
            $googleUser = $this->google->getUser($token['access_token']);

            if (empty($googleUser['email_verified']) || $googleUser['email_verified'] !== true) {
                $this->addError('Google email address is not verified.');
                return false;
            }
        } catch (\Throwable) {
            $this->addError('Unable to authenticate with Google.');
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Google's response
        |--------------------------------------------------------------------------
        */
        if (empty($googleUser['sub']) || empty($googleUser['email']) || empty($googleUser['name'])) {
            $this->addError('Google returned an incomplete user profile.');
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | CASE 1
        | Existing Google account
        |--------------------------------------------------------------------------
        */
        $user = $this->users->findByGoogleId($googleUser['sub']);

        if ($user !== false) {
            if (strtolower($user['email']) !== strtolower($googleUser['email'])) {
                $this->addError('Google account information does not match.');
                return false;
            }

            $this->users->updateAvatar((int)$user['id'], $googleUser['picture'] ?? null);
            $user['avatar'] = $googleUser['picture'] ?? null;
            return $this->login($user);
        }

        /*
        |--------------------------------------------------------------------------
        | CASE 2 / CASE 3
        | Existing email OR brand-new account
        |--------------------------------------------------------------------------
        */
        $user = $this->users->findByEmail(strtolower($googleUser['email']));

        if ($user !== false) {
            return $this->linkAccount($user, $googleUser);
        }

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

    /**
     * Log a user into the application.
     */
    private function login(array $user): array {
        session_regenerate_id(true);

        if (empty($user['username'])) {
            $_SESSION['pending_username_user_id'] = $user['id'];

            return [
                'redirect' => '/auth/username',
            ];
        }

        unset($_SESSION['pending_username_user_id']);
        $_SESSION['user_id'] = $user['id'];
        $this->rememberMe->create((int)$user['id']);

        return [
            'redirect' => '/dashboard',
        ];
    }

    /**
     * Create a brand-new Google account.
     */
    public function createGoogleUser(array $googleUser): array|false {
        $this->db->beginTransaction();

        try {
            $userId = $this->users->create([
                'fullname' => $googleUser['fullname'],
                'email' => $googleUser['email'],
                'provider' => 'google',
                'google_id' => $googleUser['google_id'],
                'avatar' => $googleUser['picture'] ?? null,
                'email_verified_at' => date('Y-m-d H:i:s'),
            ]);

            if ($userId === false) {
                throw new \RuntimeException('Unable to create Google account.');
            }

            $this->db->commit();
            $user = $this->users->findById($userId);

            if ($user === false) {
                throw new \RuntimeException('Unable to load newly created account.');
            }

            return $this->login($user);
        } catch (\Throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            $this->addError('Unable to create your account.');
            return false;
        }
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

        $googleUser = $_SESSION['google_register'];
        $this->db->beginTransaction();

        try {
            $userId = $this->users->create([
                'fullname'          => $googleUser['name'],
                'email'             => strtolower($googleUser['email']),
                'provider'          => 'google',
                'google_id'         => $googleUser['sub'],
                'avatar'            => $googleUser['picture'] ?? null,
                'email_verified_at' => date('Y-m-d H:i:s'),
            ]);

            if ($userId === false) {
                throw new \RuntimeException('Unable to create Google account.');
            }

            $this->db->commit();
            unset($_SESSION['google_register']);
            $_SESSION['pending_username_user_id'] = $userId;

            return [
                'redirect' => '/auth/username',
            ];

        } catch (\Throwable $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            $this->addError('Unable to create your account.');
            return false;
        }
    }

    /**
     * Link Google to an existing account.
     */
    private function linkAccount(array $user, array $googleUser): array|false {
        /*
        |--------------------------------------------------------------------------
        | Email must already be verified.
        |--------------------------------------------------------------------------
        */
        if (empty($user['email_verified_at'])) {
            $this->addError('Please verify your email before signing in with Google.');
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Already linked elsewhere?
        |--------------------------------------------------------------------------
        */
        $existing = $this->users->findByGoogleId($googleUser['sub']);

        if ($existing !== false && $existing['id'] != $user['id']) {
            $this->addError('This Google account is already linked to another user.');
            return false;
        }

        $this->db->beginTransaction();

        try {
            if (!$this->users->linkGoogleAccount((int) $user['id'], $googleUser['sub'],
                $googleUser['picture'] ?? null)
            ) {
                throw new \RuntimeException('Unable to link Google account.');
            }

            $this->db->commit();
            $user = $this->users->findById((int)$user['id']);

            if ($user === false) {
                throw new \RuntimeException('Unable to reload linked account.');
            }

            return $this->login($user);
        } catch (\Throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            $this->addError('Unable to link your Google account.');
            return false;
        }
    }
}