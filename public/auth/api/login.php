<?php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Auth;
use App\Database\Database;
use App\Mail\Mail;
use App\Mail\SmtpMailer;
use App\Core\Logger;
header('Content-Type: application/json');

try {
    $db = Database::connection();

    $mail = new Mail(
        new SmtpMailer(),
        $_ENV['APP_URL']
    );

    $auth = new Auth($db, $mail);

    $result = $auth->login([
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'remember' => !empty($_POST['remember']),
    ]);

    if ($result === false) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'type'    => $auth->errorType(),
            'errors'  => $auth->errors(),
            'message' => $auth->error('general'),
        ]);

        exit;
    }

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