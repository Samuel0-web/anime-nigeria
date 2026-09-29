<?php
declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';

use App\Security\Headers;
use App\Services\IpGeolocationService;
use GuzzleHttp\Client;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$executingScript = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') ?: '';
$routerScript = realpath(__DIR__ . '/public/router.php') ?: '';

if (preg_match('#(?:^|/)api(?:/|$)#', $requestPath)
    && $executingScript !== $routerScript
) {
    http_response_code(404);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_name(App\Core\Config::sessionName());
    $sessionCookieOptions = App\Core\Config::cookieOptions();
    unset($sessionCookieOptions['expires']);
    $sessionCookieOptions['lifetime'] = (int) App\Core\Config::get('SESSION_COOKIE_LIFETIME', 0);
    session_set_cookie_params($sessionCookieOptions);
    session_start([
        'use_strict_mode' => true,
    ]);
}

$db = App\Database\Database::connection();
$mail = new App\Mail\Mail(new App\Mail\SmtpMailer(), App\Core\Config::get('APP_URL'));
$ipGeolocation = new IpGeolocationService(new Client());
$auth = new App\Auth\Auth($db, $mail, $ipGeolocation);
$auth->boot();
Headers::send();

define('ROOT_PATH', __DIR__);
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');