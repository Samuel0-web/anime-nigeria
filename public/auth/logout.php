<?php
require_once __DIR__ . '/../../bootstrap.php';
use App\Auth\Auth;
use App\Database\Database;
use App\Mail\Mail;
use App\Mail\SmtpMailer;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$db = Database::connection();

$mail = new Mail(
    new SmtpMailer(),
    $_ENV['APP_URL']
);

$auth = new Auth($db, $mail);
$auth->logout();
header('Location: /login');
exit;