<?php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Core\Logger;

header('Content-Type: application/json');

$method = $_POST['method'] ?? '';
$code   = $_POST['code'] ?? '';

if (!in_array($method, ['totp', 'recovery'], true)) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'type'    => 'validation',
        'errors'  => ['method' => 'Invalid verification method.'],
    ]);
    exit;
}

try {
    $result = $method === 'totp'
        ? $auth->verifyTwoFactor($code)
        : $auth->verifyTwoFactorRecoveryCode($code);

    if ($result === false) {
        http_response_code(422);

        $response = [
            'success' => false,
            'errors'  => $auth->errors(),
            'type'    => $auth->errorType(),
            'message' => $auth->error('general'),
        ];

        if ($auth->meta()) {
            $response['meta'] = $auth->meta();
        }

        echo json_encode($response);
        exit;
    }

    echo json_encode([
        'success'  => true,
        'redirect' => $result['redirect'],
    ]);

} catch (Throwable $e) {
    Logger::error($e);
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Something went wrong.',
    ]);
}