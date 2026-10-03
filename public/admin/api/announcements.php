<?php
use App\Core\Logger;
use App\Services\AnnouncementService;

header('Content-Type: application/json');

$respond = static function (int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
};

/*
|--------------------------------------------------------------------------
| Authentication + role (enforced here, not just hidden in the UI)
|--------------------------------------------------------------------------
| The router has already verified the CSRF token for non-GET methods.
*/
$user = $auth->guest() ? null : $auth->user();

if ($user === null) {
    $respond(401, ['success' => false, 'message' => 'Unauthorized.']);
}

$role = (string) ($user['role'] ?? '');

if (!in_array($role, ['admin', 'moderator'], true)) {
    $respond(403, ['success' => false,
        'message' => 'You do not have permission to manage announcements.',
    ]);
}

$isAdmin = $role === 'admin';

$requireAdmin = static function () use ($isAdmin, $respond): void {
    if (!$isAdmin) {
        $respond(403, ['success' => false,
            'message' => 'Only administrators can delete announcements or categories.',
        ]);
    }
};

$readJson = static function (): array {
    $raw = file_get_contents('php://input');

    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
};

$send = static function (array $result) use ($respond): never {
    if (!empty($result['success'])) {
        $respond(200, $result);
    }

    $respond((int) ($result['status'] ?? 422), $result);
};

/*
|--------------------------------------------------------------------------
| Routing ($apiPath is provided by public/router.php)
|--------------------------------------------------------------------------
*/
$method = $_SERVER['REQUEST_METHOD'];
$segments = $apiPath ?? [];
$notFound = static fn () => $respond(404, ['success' => false, 'message' => 'API endpoint not found.']);

try {
    $service = AnnouncementService::make();

    // ---- /categories ------------------------------------------------------
    if (($segments[0] ?? null) === 'categories') {
        $id = $segments[1] ?? null;

        if (count($segments) > 2 || ($id !== null && !ctype_digit($id))) {
            $notFound();
        }

        if ($id === null) {
            if ($method === 'GET') {
                $respond(200, ['success' => true, 'items' => $service->listCategories()]);
            }

            if ($method === 'POST') {
                $send($service->createCategory($readJson()));
            }

            if ($method === 'DELETE') {
                $requireAdmin();
                $body = $readJson();
                $send($service->deleteCategories($body['ids'] ?? [], $body['reassign_to'] ?? null));
            }
        } elseif ($method === 'PUT') {
            $send($service->updateCategory((int) $id, $readJson()));
        }

        $notFound();
    }

    // ---- /announcements ---------------------------------------------------
    if (count($segments) > 1 || (isset($segments[0]) && !ctype_digit($segments[0]))) {
        $notFound();
    }

    $id = $segments[0] ?? null;

    if ($id === null) {
        if ($method === 'GET') {
            $respond(200, ['success' => true] + $service->listAnnouncements(
                (int) ($_GET['offset'] ?? 0), (int) ($_GET['limit'] ?? 20)
            ));
        }

        if ($method === 'POST') {
            // Upload bigger than post_max_size: PHP discards the whole body.
            if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
                $respond(413, ['success' => false, 'message' => 'The upload is too large.']);
            }

            $send($service->createAnnouncement($_POST, $_FILES));
        }

        if ($method === 'DELETE') {
            $requireAdmin();
            $body = $readJson();
            $send($service->deleteAnnouncements($body['ids'] ?? []));
        }
    } elseif ($method === 'POST') {
        if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $respond(413, ['success' => false, 'message' => 'The upload is too large.']);
        }

        $send($service->updateAnnouncement((int) $id, $_POST, $_FILES));
    }

    $notFound();
} catch (\Throwable $e) {
    Logger::error($e);
    $respond(500, ['success' => false, 'message' => 'Something went wrong. Please try again.']);
}