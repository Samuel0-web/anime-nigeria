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

if ($auth->completeRegistration($userId, $_POST['username'] ?? '')) {
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