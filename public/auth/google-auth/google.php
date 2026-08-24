<?php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Auth\GoogleAuth;
use App\Auth\GoogleClient;
$google = new GoogleAuth($db, new GoogleClient(), $auth);
$google->redirect();