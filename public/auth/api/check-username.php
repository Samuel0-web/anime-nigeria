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
$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');

if ($auth->checkUsername($username)) {
    echo json_encode([
        'success' => true,
        'available' => true,
    ]);

    exit;
}

echo json_encode([
    'success' => false,
    'available' => false,
    'errors' => $auth->errors(),
]);