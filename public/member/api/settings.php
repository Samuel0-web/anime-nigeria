<?php
use App\Database\Database;
use App\Models\User;
use App\Models\TwoFactorAuth;
use App\Services\TwoFactorService;
use App\Core\Logger;

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
$twoFactorService = new TwoFactorService($twoFactorAuth);

/*
|--------------------------------------------------------------------------
| Small local helper — builds the 2FA slice of the settings response.
| Kept as a closure (not a global function) so this file stays safe
| to require multiple times within a single process.
|--------------------------------------------------------------------------
*/
$buildTwoFactorState = static function () use ($currentUser, $twoFactorAuth, $userId): array {
    return [
        'enabled' => $twoFactorAuth->isEnabled($userId),
        'managed_externally' => ($currentUser['auth_provider'] ?? 'local') === 'google',
    ];
};

try {
    $method = $_SERVER['REQUEST_METHOD'];

    /*
    |----------------------------------------------------------------
    | GET — current settings state
    |----------------------------------------------------------------
    */
    if ($method === 'GET') {
        echo json_encode([
            'success' => true,
            'settings' => [
                '2fa' => $buildTwoFactorState(),
            ],
        ]);
        exit;
    }

    /*
    |----------------------------------------------------------------
    | POST — settings actions
    |----------------------------------------------------------------
    */
    if ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        switch ($action) {

            // ------------------------------------------------------
            // Start 2FA setup
            // ------------------------------------------------------
            case '2fa.setup':
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
                    'setup' => $setup,
                ]);
                break;

            // ------------------------------------------------------
            // Verify the OTP and complete setup
            // ------------------------------------------------------
            case '2fa.verify':
                $code = trim($_POST['code'] ?? '');

                if (!preg_match('/^\d{6}$/', $code)) {
                    http_response_code(422);

                    echo json_encode([
                        'success' => false,
                        'errors' => ['code' => 'Enter the 6-digit code from your app.'],
                    ]);
                    exit;
                }

                try {
                    $recoveryCodes = $twoFactorService->completeSetup($userId, $code);
                } catch (\RuntimeException $e) {
                    http_response_code(422);

                    echo json_encode([
                        'success' => false,
                        'errors' => ['code' => $e->getMessage()],
                    ]);

                    exit;
                }

                echo json_encode([
                    'success' => true,
                    'recovery_codes' => $recoveryCodes,
                    'settings' => [
                        '2fa' => $buildTwoFactorState(),
                    ],
                ]);
                break;

            // ------------------------------------------------------
            // Disable 2FA (requires password confirmation)
            // ------------------------------------------------------
            case '2fa.disable':
                $password = (string) ($_POST['password'] ?? '');

                if (trim($password) === '') {
                    http_response_code(422);

                    echo json_encode([
                        'success' => false,
                        'errors' => ['password' => 'Enter your password to continue.'],
                    ]);
                    exit;
                }

                if (!$auth->verifyPassword($password)) {
                    http_response_code(422);

                    echo json_encode([
                        'success' => false,
                        'errors' => ['password' => 'Incorrect password.'],
                    ]);
                    exit;
                }

                if (!$twoFactorAuth->isEnabled($userId)) {
                    http_response_code(422);

                    echo json_encode([
                        'success' => false,
                        'message' => 'Two-factor authentication is not enabled.',
                    ]);
                    exit;
                }

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
                    'settings' => [
                        '2fa' => $buildTwoFactorState(),
                    ],
                ]);
                break;

            default:
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => 'Unknown settings action.',
                ]);
        }

        exit;
    }

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.',
    ]);

} catch (Throwable $e) {
    Logger::error($e);
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Something went wrong.',
    ]);
}