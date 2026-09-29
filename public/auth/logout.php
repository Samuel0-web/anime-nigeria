<?php
require_once __DIR__ . '/../../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

\App\Http\Middleware\VerifyCsrf::handle();

$auth->logout();
header('Location: /login');
exit;