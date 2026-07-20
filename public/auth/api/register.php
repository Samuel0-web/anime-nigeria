<?php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Auth;
use App\Database\Database;
use App\Mail\Mail;
use App\Mail\SmtpMailer;
header('Content-Type: application/json');

try {
    $db = Database::connection();

    $mail = new Mail(
        new SmtpMailer(),
        $_ENV['APP_URL']
    );

    $auth = new Auth($db, $mail);

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

        echo json_encode([
            'success' => false,
            'errors' => $auth->errors(),
        ]);
        
        exit;
    }

    echo json_encode([
        'success' => true,
        'email' => $user['email'],
        'resend_after' => 60,
    ]);

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Something went wrong. Please try again later.',
    ]);

    // log the real error
    error_log($e);
}