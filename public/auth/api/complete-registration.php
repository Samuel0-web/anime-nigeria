<?php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Auth;
use App\Database\Database;
use App\Mail\Mail;
use App\Mail\SmtpMailer;
header('Content-Type: application/json');

if (!isset($_SESSION['pending_username_user_id'])) {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Registration session has expired.'
    ]);

    exit;
}

$db = Database::connection();

$mail = new Mail(
    new SmtpMailer(),
    $_ENV['APP_URL']
);

$auth = new Auth($db, $mail);
$userId = (int) $_SESSION['pending_username_user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');

if ($auth->completeRegistration($userId, $username)) {
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