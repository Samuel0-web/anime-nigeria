<?php
require_once __DIR__.'/../../../bootstrap.php';
header('Content-Type: application/json');
$result = $auth->resetPassword($_POST['token'] ?? '', $_POST);

if (!$result) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'errors' => $auth->errors(),
    ]);

    exit;
}

echo json_encode([
    'success' => true,
]);