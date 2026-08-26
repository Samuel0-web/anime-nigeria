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

/**
 * Settings API Endpoint
 * 
 * Routes:
 * - GET    /api/settings                          Get settings overview
 * - GET    /api/settings/2fa                      Get 2FA status
 * - POST   /api/settings/2fa/setup                Start 2FA setup
 * - PUT    /api/settings/2fa/setup                Complete 2FA setup
 * - DELETE /api/settings/2fa/setup                Cancel 2FA setup
 * - DELETE /api/settings/2fa                      Disable 2FA
 * - GET    /api/settings/sessions                 List active sessions
 * - POST   /api/settings/sessions/revoke-others   Revoke all other sessions
 * - DELETE /api/settings/sessions/{id}            Revoke specific session
 */

header('Content-Type: application/json');

// =========================================================================
// AUTHENTICATION CHECK
// =========================================================================

if (!$auth->check()) {
    respond(401, [
        'success' => false,
        'message' => 'Unauthorized.',
    ]);
}

$userId = (int) $auth->id();

// =========================================================================
// DEPENDENCIES
// =========================================================================

$db = Database::connection();
$users = new User($db);
$currentUser = $users->findById($userId);

if ($currentUser === false) {
    respond(401, ['success' => false, 'message' => 'Unauthorized.']);
}

$twoFactorAuth = new TwoFactorAuth($db);
$rateLimiter = new RateLimiter($db, $userId);
$twoFactorService = new TwoFactorService($twoFactorAuth, $rateLimiter);
$loginSessions = new LoginSession($db);
$loginSessionService = new LoginSessionService($loginSessions);

// =========================================================================
// HELPER FUNCTIONS
// =========================================================================

function respond(int $status, array $data): void {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function respondError(int $status, string $message): void {
    respond($status, ['success' => false, 'message' => $message]);
}

function respondValidationError(string $field, string $message): void {
    respond(422, [
        'success' => false,
        'errors' => [$field => $message],
    ]);
}

function respondMethodNotAllowed(array $allowedMethods): void {
    header('Allow: ' . implode(', ', $allowedMethods));
    respondError(405, 'Method not allowed.');
}

function getInput(): array {
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
}

$buildTwoFactorState = static function () use ($currentUser, $twoFactorAuth, $userId): array {
    return [
        'enabled' => $twoFactorAuth->isEnabled($userId),
        'managed_externally' => ($currentUser['auth_provider'] ?? 'local') === 'google',
    ];
};

$formatSession = static function (array $session, ?int $currentSessionId): array {
    return [
        'id' => (int) $session['id'],
        'is_current' => $currentSessionId !== null && (int) $session['id'] === $currentSessionId,
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

$buildSessionsList = static function () use ($loginSessionService, $formatSession, $userId, $auth): array {
    $currentSession = $auth->currentLoginSession();
    $currentSessionId = $currentSession !== null ? (int) $currentSession['id'] : null;

    $formatted = array_map(
        static fn (array $s) => $formatSession($s, $currentSessionId),
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

// =========================================================================
// ROUTING
// =========================================================================

$resource = $apiPath[0] ?? null;
$subResource = $apiPath[1] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

try {
    // =========================================================================
    // GET /api/settings
    // =========================================================================
    if ($resource === null) {
        if ($method !== 'GET') {
            respondMethodNotAllowed(['GET']);
        }

        respond(200, [
            'success' => true,
            'data' => [
                '2fa' => $buildTwoFactorState(),
            ],
        ]);
    }

    // =========================================================================
    // /api/settings/2fa
    // =========================================================================
    if ($resource === '2fa' && $subResource === null) {
        handleTwoFactorSettings();
    }

    // =========================================================================
    // /api/settings/2fa/setup
    // =========================================================================
    if ($resource === '2fa' && $subResource === 'setup') {
        handleTwoFactorSetup();
    }

    // =========================================================================
    // /api/settings/sessions
    // =========================================================================
    if ($resource === 'sessions') {
        handleSessions();
    }

    // =========================================================================
    // Unknown resource
    // =========================================================================
    respondError(404, 'Settings resource not found.');

} catch (Throwable $e) {
    Logger::error($e);
    respondError(500, 'Something went wrong.');
}

// =========================================================================
// ROUTE HANDLERS
// =========================================================================
function handleTwoFactorSettings(): void {
    global $method, $twoFactorAuth, $userId, $rateLimiter, $auth, $buildTwoFactorState;

    // GET /api/settings/2fa
    if ($method === 'GET') {
        respond(200, [
            'success' => true,
            'data' => $buildTwoFactorState(),
        ]);
    }

    // DELETE /api/settings/2fa
    if ($method === 'DELETE') {
        disableTwoFactor();
    }

    respondMethodNotAllowed(['GET', 'DELETE']);
}

function handleTwoFactorSetup(): void {
    global $method;

    // POST /api/settings/2fa/setup
    if ($method === 'POST') {
        startTwoFactorSetup();
    }

    // PUT /api/settings/2fa/setup
    if ($method === 'PUT') {
        completeTwoFactorSetup();
    }

    // DELETE /api/settings/2fa/setup
    if ($method === 'DELETE') {
        cancelTwoFactorSetup();
    }

    respondMethodNotAllowed(['POST', 'PUT', 'DELETE']);
}

function handleSessions(): void {
    global $subResource, $method, $userId, $auth, $loginSessionService, $buildSessionsList;

    // GET /api/settings/sessions
    if ($subResource === null && $method === 'GET') {
        respond(200, [
            'success' => true,
            'data' => [
                'sessions' => $buildSessionsList(),
            ],
        ]);
    }

    // POST /api/settings/sessions/revoke-others
    if ($subResource === 'revoke-others' && $method === 'POST') {
        revokeOtherSessions();
    }

    // DELETE /api/settings/sessions/{id}
    if ($subResource !== null && $subResource !== 'revoke-others' && $method === 'DELETE') {
        revokeSpecificSession();
    }

    respondMethodNotAllowed(['GET', 'DELETE', 'POST']);
}

// =========================================================================
// 2FA OPERATIONS
// =========================================================================
function startTwoFactorSetup(): void {
    global $twoFactorAuth, $userId, $currentUser, $twoFactorService;

    if ($twoFactorAuth->isEnabled($userId)) {
        respondValidationError('general', 'Two-factor authentication is already enabled.');
    }

    $setup = $twoFactorService->startSetup($userId, $currentUser['email']);

    respond(200, [
        'success' => true,
        'data' => $setup,
    ]);
}

function completeTwoFactorSetup(): void {
    global $twoFactorService, $userId, $buildTwoFactorState;
    $data = getInput();
    $code = trim((string) ($data['code'] ?? ''));

    if (!preg_match('/^\d{6}$/', $code)) {
        respondValidationError('code', 'Enter the 6-digit code from your app.');
    }

    $recoveryCodes = [];

    try {
        $recoveryCodes = $twoFactorService->completeSetup($userId, $code);
    } catch (\RuntimeException $e) {
        respondValidationError('code', $e->getMessage());
    }

    respond(200, [
        'success' => true,
        'data' => [
            'recovery_codes' => $recoveryCodes,
            '2fa' => $buildTwoFactorState(),
        ],
    ]);
}

function cancelTwoFactorSetup(): void {
    global $twoFactorAuth, $userId, $buildTwoFactorState;
    $pendingSetup = $twoFactorAuth->findByUserId($userId);

    if ($pendingSetup === false) {
        respond(200, [
            'success' => true,
            'data' => $buildTwoFactorState(),
        ]);
    }

    if ($pendingSetup['enabled_at'] !== null) {
        respondValidationError('general', 'Two-factor authentication is already enabled and cannot be cancelled this way.');
    }

    if (!$twoFactorAuth->deletePendingSetup($userId)) {
        respondError(500, 'Unable to cancel two-factor authentication setup.');
    }

    respond(200, [
        'success' => true,
        'data' => $buildTwoFactorState(),
    ]);
}

function disableTwoFactor(): void {
    global $rateLimiter, $auth, $twoFactorAuth, $userId, $buildTwoFactorState;
    $data = getInput();
    $password = (string) ($data['password'] ?? '');

    if (trim($password) === '') {
        respondValidationError('password', 'Enter your password to continue.');
    }

    if ($rateLimiter->tooManyAttempts('two_factor_disable')) {
        respond(429, [
            'success' => false,
            'message' => 'Too many incorrect attempts. Please try again later.',
            'retry_after' => $rateLimiter->remainingSeconds('two_factor_disable'),
        ]);
    }

    if (!$auth->verifyPassword($password)) {
        $rateLimiter->hit('two_factor_disable');
        respondValidationError('password', 'Incorrect password.');
    }

    $rateLimiter->clear('two_factor_disable');

    if (!$twoFactorAuth->isEnabled($userId)) {
        respondError(422, 'Two-factor authentication is not enabled.');
    }

    if (!$twoFactorAuth->delete($userId)) {
        respondError(500, 'Unable to disable two-factor authentication.');
    }

    respond(200, [
        'success' => true,
        'data' => $buildTwoFactorState(),
    ]);
}

// =========================================================================
// SESSION OPERATIONS
// =========================================================================
function revokeOtherSessions(): void {
    global $loginSessionService, $userId, $buildSessionsList;

    if (!$loginSessionService->revokeOthers($userId)) {
        respondError(500, 'Unable to sign out your other sessions.');
    }

    respond(200, [
        'success' => true,
        'data' => [
            'sessions' => $buildSessionsList(),
        ],
    ]);
}

function revokeSpecificSession(): void {
    global $subResource, $userId, $auth, $loginSessionService, $buildSessionsList;

    if (!ctype_digit($subResource)) {
        respondValidationError('session', 'Invalid session.');
    }

    $targetSessionId = (int) $subResource;
    $currentSession = $auth->currentLoginSession();

    if ($currentSession !== null && (int) $currentSession['id'] === $targetSessionId) {
        respondValidationError('session', "You can't sign out your current session from here.");
    }

    if (!$loginSessionService->revoke($userId, $targetSessionId)) {
        respondError(404, 'That session was not found, or has already been signed out.');
    }

    respond(200, [
        'success' => true,
        'data' => [
            'sessions' => $buildSessionsList(),
        ],
    ]);
}