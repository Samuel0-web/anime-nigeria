<?php
namespace App\Http\Middleware;
use App\Security\Csrf;

class VerifyCsrf {
    public static function handle(): void {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_token'] ?? '';

        if (!Csrf::verify($token)) {
            if (!Csrf::verify($token)) {
                http_response_code(403);

                header('Content-Type: application/json');

                echo json_encode([
                    'success' => false,
                    'message' => 'Your session has expired. Please refresh the page and try again.'
                ]);

                exit;
            }

            exit;
        }
    }
}