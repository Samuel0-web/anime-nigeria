<?php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Auth;
use App\Database\Database;
use App\Mail\Mail;
use App\Mail\SmtpMailer;
header('Content-Type: application/json');

$db = Database::connection();

$mail = new Mail(
    new SmtpMailer(),
    $_ENV['APP_URL']
);

$auth = new Auth($db, $mail);

$userId = $auth->register([
    'fullname'         => $_POST['fullname'] ?? '',
    'email'            => $_POST['email'] ?? '',
    'password'         => $_POST['password'] ?? '',
    'confirm_password' => $_POST['confirm_password'] ?? '',
    'terms'            => $_POST['terms'] ?? '',
    'provider'         => 'local',
]);

if ($userId !== false) {
    echo json_encode([
        'success' => true,
        'email' => $userId['email'],
        'resend_after' => 60,
    ]);

    exit;
}

echo json_encode([
    'success' => false,
    'errors' => $auth->errors(),
]);