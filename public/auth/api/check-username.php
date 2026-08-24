<?php
require_once __DIR__ . '/../../../bootstrap.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');

if ($auth->checkUsername($username)) {
    echo json_encode([
        'success' => true,
        'available' => true,
    ]);

    exit;
}

$response = [
    'success' => false,
    'available' => false,
    'errors' => $auth->errors(),
    'type'     => $auth->errorType(),
];

if ($auth->meta()) {
    $response['meta'] = $auth->meta();
}

echo json_encode($response);