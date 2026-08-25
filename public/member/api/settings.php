<?php
declare(strict_types=1);

use App\Database\Database;
use App\Models\User;
use App\Services\RateLimiter;
use App\Models\TwoFactorAuth;
use App\Services\TwoFactorService;
use App\Core\Logger;
use App\Models\LoginSession;
use App\Services\LoginSessionService;

header('Content-Type: application/json');

/*
|--------------------------------------------------------------------------
| Auth check
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized.',
    ]);

    exit;
}

$userId = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

$db = Database::connection();
$users = new User($db);
$currentUser = $users->findById($userId);

if ($currentUser === false) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized.',
    ]);

    exit;
}

$twoFactorAuth = new TwoFactorAuth($db);
$rateLimiter = new RateLimiter($db, $userId);
$twoFactorService = new TwoFactorService($twoFactorAuth, $rateLimiter);
$loginSessions = new LoginSession($db);
$loginSessionService = new LoginSessionService($loginSessions);

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Build the current 2FA state.
 */
$buildTwoFactorState = static function () use ($currentUser,
    $twoFactorAuth,
    $userId
): array {
    return [
        'enabled' => $twoFactorAuth->isEnabled($userId),
        'managed_externally' => ($currentUser['auth_provider'] ?? 'local') === 'google',
    ];
};

/**
 * Format a raw login_sessions row for the frontend. Only ever exposes
 * the auto-increment row id (already ownership-checked on every use),
 * never session_id_hash, device_identifier_hash, or the raw user_agent.
 */
$formatSession = static function (array $session, ?int $currentSessionId): array {
    return [
        'id' => (int) $session['id'],
        'is_current' => $currentSessionId !== null
            && (int) $session['id'] === $currentSessionId,
        'browser' => [
            'name' => $session['browser'] ?? null,
            'version' => $session['browser_version'] ?? null,
        ],
        'os' => [
            'name' => $session['os'] ?? null,
            'version' => $session['os_version'] ?? null,
        ],
        'device' => [
            'type' => $session['device_type'] ?? null,
            'brand' => $session['brand'] ?? null,
            'model' => $session['model'] ?? null,
        ],
        'ip_address' => $session['ip_address'] ?? null,
        'created_at' => $session['created_at'] ?? null,
        'last_active_at' => $session['last_activity_at'] ?? null,
    ];
};

/**
 * Current session first, then everything else by most-recently-active.
 * Always re-derived from the DB — never trust cached state.
 */
$buildSessionsList = static function () use (
    $loginSessionService, $formatSession, $userId, $auth
): array {
    $currentSession = $auth->currentLoginSession();
    $currentSessionId = $currentSession !== null ? (int) $currentSession['id'] : null;

    $formatted = array_map(static fn (array $s) => $formatSession($s, $currentSessionId),
        $loginSessionService->getActiveSessions($userId)
    );

    usort($formatted, static function (array $a, array $b) {
        if ($a['is_current'] !== $b['is_current']) {
            return $a['is_current'] ? -1 : 1;
        }

        return strcmp((string) $b['last_active_at'], (string) $a['last_active_at']);
    });

    return $formatted;
};

/**
 * Read request body.
 *
 * Supports:
 * - application/json
 * - application/x-www-form-urlencoded
 * - multipart/form-data
 */
$input = static function (): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (str_contains($contentType, 'application/json')) {
        $raw = file_get_contents('php://input');

        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    return $_POST;
};

/*
|--------------------------------------------------------------------------
| Request
|--------------------------------------------------------------------------
*/

$resource = $apiPath[0] ?? null;
$subResource = $apiPath[1] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

try {
    /*
    |--------------------------------------------------------------------------
    | /api/settings
    |--------------------------------------------------------------------------
    |
    | GET /api/settings
    |
    */

    if ($resource === null) {
        if ($method !== 'GET') {
            http_response_code(405);
            header('Allow: GET');

            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed.',
            ]);

            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => [
                '2fa' => $buildTwoFactorState(),
            ],
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | /api/settings/2fa
    |--------------------------------------------------------------------------
    */

    if ($resource === '2fa' && $subResource === null) {
        /*
        |--------------------------------------------------------------------------
        | GET /api/settings/2fa
        |
        | Get current 2FA state.
        |--------------------------------------------------------------------------
        */

        if ($method === 'GET') {
            echo json_encode([
                'success' => true,
                'data' => $buildTwoFactorState(),
            ]);

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE /api/settings/2fa
        |
        | Disable 2FA.
        |--------------------------------------------------------------------------
        */

        if ($method === 'DELETE') {
            $data = $input();
            $password = (string) ($data['password'] ?? '');

            /*
            |--------------------------------------------------------------------------
            | Validate password
            |--------------------------------------------------------------------------
            */

            if (trim($password) === '') {
                http_response_code(422);

                echo json_encode([
                    'success' => false,
                    'errors' => [
                        'password' => 'Enter your password to continue.',
                    ],
                ]);

                exit;
            }

            /*
            |--------------------------------------------------------------------------
            | Rate limiting
            |--------------------------------------------------------------------------
            */

            if ($rateLimiter->tooManyAttempts('two_factor_disable')) {
                http_response_code(429);

                echo json_encode([
                    'success' => false,
                    'message' => 'Too many incorrect attempts. Please try again later.',
                    'retry_after' => $rateLimiter->remainingSeconds('two_factor_disable'),
                ]);

                exit;
            }

            /*
            |--------------------------------------------------------------------------
            | Password verification
            |--------------------------------------------------------------------------
            */

            if (!$auth->verifyPassword($password)) {
                $rateLimiter->hit('two_factor_disable');
                http_response_code(422);

                echo json_encode([
                    'success' => false,
                    'errors' => [
                        'password' => 'Incorrect password.',
                    ],
                ]);

                exit;
            }

            $rateLimiter->clear('two_factor_disable');

            /*
            |--------------------------------------------------------------------------
            | Ensure 2FA is enabled
            |--------------------------------------------------------------------------
            */

            if (!$twoFactorAuth->isEnabled($userId)) {
                http_response_code(422);

                echo json_encode([
                    'success' => false,
                    'message' => 'Two-factor authentication is not enabled.',
                ]);

                exit;
            }

            /*
            |--------------------------------------------------------------------------
            | Disable 2FA
            |--------------------------------------------------------------------------
            */

            if (!$twoFactorAuth->delete($userId)) {
                http_response_code(500);

                echo json_encode([
                    'success' => false,
                    'message' => 'Unable to disable two-factor authentication.',
                ]);

                exit;
            }

            echo json_encode([
                'success' => true,
                'data' => $buildTwoFactorState(),
            ]);

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Unsupported method
        |--------------------------------------------------------------------------
        */

        http_response_code(405);
        header('Allow: GET, DELETE');

        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed.',
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | /api/settings/2fa/setup
    |--------------------------------------------------------------------------
    */

    if ($resource === '2fa' && $subResource === 'setup') {

        /*
        |--------------------------------------------------------------------------
        | POST /api/settings/2fa/setup
        |
        | Start 2FA setup.
        |--------------------------------------------------------------------------
        */

        if ($method === 'POST') {
            if ($twoFactorAuth->isEnabled($userId)) {
                http_response_code(422);

                echo json_encode([
                    'success' => false,
                    'message' => 'Two-factor authentication is already enabled.',
                ]);

                exit;
            }

            $setup = $twoFactorService->startSetup($userId, $currentUser['email']);

            echo json_encode([
                'success' => true,
                'data' => $setup,
            ]);

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | PUT /api/settings/2fa/setup
        |
        | Verify OTP and complete setup.
        |--------------------------------------------------------------------------
        */

        if ($method === 'PUT') {
            $data = $input();
            $code = trim((string) ($data['code'] ?? ''));

            if (!preg_match('/^\d{6}$/', $code)) {
                http_response_code(422);

                echo json_encode([
                    'success' => false,
                    'errors' => [
                        'code' => 'Enter the 6-digit code from your app.',
                    ],
                ]);

                exit;
            }

            try {
                $recoveryCodes = $twoFactorService->completeSetup($userId, $code);
            } catch (\RuntimeException $e) {
                http_response_code(422);

                echo json_encode([
                    'success' => false,
                    'errors' => [
                        'code' => $e->getMessage(),
                    ],
                ]);

                exit;
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'recovery_codes' => $recoveryCodes,
                    '2fa' => $buildTwoFactorState(),
                ],
            ]);

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE /api/settings/2fa/setup
        |
        | Cancel unfinished setup.
        |--------------------------------------------------------------------------
        */

        if ($method === 'DELETE') {
            $pendingSetup = $twoFactorAuth->findByUserId($userId);

            /*
            |--------------------------------------------------------------------------
            | Nothing to cancel
            |--------------------------------------------------------------------------
            */

            if ($pendingSetup === false) {
                echo json_encode([
                    'success' => true,
                    'data' => $buildTwoFactorState(),
                ]);

                exit;
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent deleting an enabled configuration
            |--------------------------------------------------------------------------
            */

            if ($pendingSetup['enabled_at'] !== null) {
                http_response_code(422);

                echo json_encode([
                    'success' => false,
                    'message' =>
                        'Two-factor authentication is already enabled and cannot be 
                        cancelled this way.',
                ]);

                exit;
            }

            /*
            |--------------------------------------------------------------------------
            | Cancel setup
            |--------------------------------------------------------------------------
            */

            if (!$twoFactorAuth->deletePendingSetup($userId)) {
                http_response_code(500);

                echo json_encode([
                    'success' => false,
                    'message' => 'Unable to cancel two-factor authentication setup.',
                ]);

                exit;
            }

            echo json_encode([
                'success' => true,
                'data' => $buildTwoFactorState(),
            ]);

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Unsupported method
        |--------------------------------------------------------------------------
        */

        http_response_code(405);
        header('Allow: POST, PUT, DELETE');

        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed.',
        ]);

        exit;
    }

        /*
    |--------------------------------------------------------------------------
    | /api/settings/sessions
    |--------------------------------------------------------------------------
    |
    | GET    /api/settings/sessions                 List active sessions.
    | DELETE /api/settings/sessions/{id}             Revoke one session.
    | POST   /api/settings/sessions/revoke-others    Revoke all other sessions.
    |--------------------------------------------------------------------------
    */

    if ($resource === 'sessions') {

        /*
        |--------------------------------------------------------------------------
        | GET /api/settings/sessions
        |--------------------------------------------------------------------------
        */

        if ($subResource === null && $method === 'GET') {
            echo json_encode([
                'success' => true,
                'data' => [
                    'sessions' => $buildSessionsList(),
                ],
            ]);

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | POST /api/settings/sessions/revoke-others
        |--------------------------------------------------------------------------
        */

        if ($subResource === 'revoke-others' && $method === 'POST') {
            if (!$loginSessionService->revokeOthers($userId)) {
                http_response_code(500);

                echo json_encode([
                    'success' => false,
                    'message' => 'Unable to sign out your other sessions.',
                ]);

                exit;
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'sessions' => $buildSessionsList(),
                ],
            ]);

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE /api/settings/sessions/{id}
        |--------------------------------------------------------------------------
        */

        if ($subResource !== null && $subResource !== 'revoke-others' && 
            $method === 'DELETE'
        ) {
            if (!ctype_digit($subResource)) {
                http_response_code(422);

                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid session.',
                ]);

                exit;
            }

            $targetSessionId = (int) $subResource;
            $currentSession = $auth->currentLoginSession();

            if ($currentSession !== null && (int) $currentSession['id'] === 
                $targetSessionId
            ) {
                http_response_code(422);

                echo json_encode([
                    'success' => false,
                    'message' => "You can't sign out your current session from here.",
                ]);

                exit;
            }

            if (!$loginSessionService->revoke($userId, $targetSessionId)) {
                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'That session was not found, or has already been 
                        signed out.',
                ]);

                exit;
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'sessions' => $buildSessionsList(),
                ],
            ]);

            exit;
        }

        http_response_code(405);
        header('Allow: GET, DELETE, POST');

        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed.',
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Unknown settings resource
    |--------------------------------------------------------------------------
    */

    http_response_code(404);

    echo json_encode([
        'success' => false,
        'message' => 'Settings resource not found.',
    ]);

} catch (Throwable $e) {
    Logger::error($e);
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Something went wrong.',
    ]);
}