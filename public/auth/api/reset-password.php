<?php
require_once __DIR__.'/../../../bootstrap.php';
header('Content-Type: application/json');
$result = $auth->resetPassword($_POST['token'] ?? '', $_POST);

if (!$result) {
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
]);