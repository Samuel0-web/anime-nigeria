<?php
require_once __DIR__ . '/../../../bootstrap.php';

header('Content-Type: application/json');

if (!isset($_SESSION['pending_username_user_id'])) {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Registration session has expired.'
    ]);

    exit;
}

$userId = (int) $_SESSION['pending_username_user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');

if ($auth->completeRegistration($username)) {
    echo json_encode([
        'success' => true,
        'redirect' => '/dashboard'
    ]);

    exit;
}

echo json_encode([
    'success' => false,
    'errors' => $auth->errors()
]);