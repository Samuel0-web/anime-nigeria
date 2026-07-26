<?php
require_once __DIR__ . '/../../../bootstrap.php';
header('Content-Type: application/json');
$email = $_SESSION['verification_email'] ?? '';

if ($email === '') {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Verification session expired.',
    ]);

    exit;
}

$result = $auth->resendVerification($email);

if ($result === false) {
    http_response_code(422);

    $response = [
        'success' => false,
        'errors'  => $auth->errors(),
        'type'    => $auth->errorType(),
    ];

    if ($auth->meta()) {
        $response['meta'] = $auth->meta();
    }

    echo json_encode($response);
    exit;
}

echo json_encode([
    'success' => true,
    'resend_after' => $result['resend_after'],
]);