<?php
declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';

use App\Security\Headers;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure'   => !empty($_SERVER['HTTPS']),
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

$db = App\Database\Database::connection();

$mail = new App\Mail\Mail(
    new App\Mail\SmtpMailer(),
    $_ENV['APP_URL']
);

$auth = new App\Auth\Auth($db, $mail);
$auth->boot();
// Headers::send();