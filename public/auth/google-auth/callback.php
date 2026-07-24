<?php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Auth\GoogleAuth;
use App\Auth\GoogleClient;
use App\Database\Database;
$db = Database::connection();
$google = new GoogleAuth($db, new GoogleClient());
$result = $google->callback($_GET['code'] ?? null, $_GET['state'] ?? null, $_GET['error'] ?? null);

if ($result === false) {
    $_SESSION['flash_error'] = $google->error();
    header('Location: /login');
    exit;
}

header('Location: ' . $result['redirect']);
exit;