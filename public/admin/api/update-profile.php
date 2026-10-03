<?php
use App\Database\Database;
use App\Models\User;
use App\Models\LoginSession;
use App\Auth\RememberMe;
use App\Services\ProfileService;

header('Content-Type: application/json');

/*
|--------------------------------------------------------------------------
| Auth check
|--------------------------------------------------------------------------
*/

$currentLoginSession = $auth->currentLoginSession();

if ($currentLoginSession === null) {
    http_response_code(401);

    echo json_encode([
        'message' => 'Unauthorized.',
    ]);

    exit;
}

$db = Database::connection();
$users = new User($db);
$currentUser = $users->findById((int) $auth->id());

if ($currentUser === false) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized.']);
    exit;
}

$service = new ProfileService($users, $db, new LoginSession($db), new RememberMe($db));
$result = $service->update($currentUser, $_POST, $_FILES, (int) $currentLoginSession['id']);

if (!$result['success']) {
    http_response_code(422);
    echo json_encode($result);

    exit;
}

echo json_encode($result);