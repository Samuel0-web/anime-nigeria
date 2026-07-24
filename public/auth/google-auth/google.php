<?php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Auth\GoogleAuth;
use App\Auth\GoogleClient;
use App\Database\Database;
$db = Database::connection();
$google = new GoogleAuth($db, new GoogleClient());
$google->redirect();