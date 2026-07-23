<?php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Database\Database;
header('Content-Type: application/json');
$email = $_POST['email'] ?? '';
$result = $auth->forgotPassword($email);

if ($result === false) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'errors' => $auth->errors(),
    ]);

    exit;
}

echo json_encode([
    'success' => true,
    'email' => $result['email'],
    'resend_after' => $result['resend_after'],
]);