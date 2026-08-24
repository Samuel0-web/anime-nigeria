<?php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Core\Logger;
header('Content-Type: application/json');

try {
    $user = $auth->register([
        'fullname'         => $_POST['fullname'] ?? '',
        'email'            => $_POST['email'] ?? '',
        'password'         => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'terms'            => $_POST['terms'] ?? '',
        'provider'         => 'local',
    ]);

    if ($user === false) {
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

    http_response_code(201);

    echo json_encode([
        'success' => true,
        'email' => $user['email'],
        'resend_after' => 60,
    ]);
} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Something went wrong. Please try again.',
    ]);

    // log the real error
    Logger::error($e);
}