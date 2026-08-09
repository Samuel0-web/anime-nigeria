<?php
use App\Database\Database;
use App\Models\User;
use App\Services\ProfileService;

header('Content-Type: application/json');

/*
|--------------------------------------------------------------------------
| Auth check
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);

    echo json_encode([
        'message' => 'Unauthorized.',
    ]);

    exit;
}

$db = Database::connection();
$users = new User($db);
$currentUser = $users->findById($_SESSION['user_id']);
$service = new ProfileService($users);
$result = $service->update($currentUser, $_POST, $_FILES);

if (!$result['success']) {
    http_response_code(422);
    echo json_encode($result);

    exit;
}

echo json_encode($result);