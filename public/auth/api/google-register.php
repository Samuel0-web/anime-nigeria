<?php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Auth\GoogleAuth;
use App\Auth\GoogleClient;
use App\Database\Database;
use App\Core\Logger;
header('Content-Type: application/json');

try {
    $db = Database::connection();
    $google = new GoogleAuth($db, new GoogleClient());

    $result = $google->completeRegistration([
        'terms' => $_POST['terms'] ?? '',
    ]);

    if ($result === false) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'errors' => [
                'terms' => $google->error(),
            ],
        ]);

        exit;
    }

    http_response_code(201);
    
    echo json_encode([
        'success' => true,
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