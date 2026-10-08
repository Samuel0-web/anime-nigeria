<?php
use App\Core\Logger;
use App\Services\BlogAdminService;
use App\Services\BlogBannedWordService;

header('Content-Type: application/json');

$respond = static function (int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
};

// ---- authentication + role: enforced here, never only in the UI --------------
$user = $auth->guest() ? null : $auth->user();

if ($user === null) {
    $respond(401, ['success' => false, 'message' => 'Unauthorized.']);
}

$role = (string) ($user['role'] ?? '');

if (!in_array($role, ['admin', 'moderator'], true)) {
    $respond(403, ['success' => false, 'message' => 'You do not have permission to manage the blog.']);
}

$isAdmin = $role === 'admin';

$requireAdmin = static function () use ($isAdmin, $respond): void {
    if (!$isAdmin) {
        $respond(403, ['success' => false,
            'message' => 'Only administrators can archive, restore or delete.']);
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

$method = $_SERVER['REQUEST_METHOD'];
$segments = $apiPath ?? [];
$first = $segments[0] ?? null;
$notFound = static fn () => $respond(404, ['success' => false, 'message' => 'API endpoint not found.']);

try {
    $svc = BlogAdminService::make();
    $userId = (int) $user['id'];

    if ($first === 'categories') {
        $id = $segments[1] ?? null;

        if (count($segments) > 2 || ($id !== null && !ctype_digit($id))) {
            $notFound();
        }

        if ($id === null) {
            if ($method === 'GET') {
                $respond(200, ['success' => true, 'items' => $svc->listCategories()]);
            }

            if ($method === 'POST') {
                $send($svc->createCategory($readJson()));
            }

            if ($method === 'DELETE') {
                $requireAdmin();
                $body = $readJson();
                $send($svc->deleteCategories($body['ids'] ?? [], $body['reassign_to'] ?? null));
            }
        } elseif ($method === 'PUT') {
            $send($svc->updateCategory((int) $id, $readJson()));
        }

        $notFound();
    }

    if ($first === 'banned-words') {
        $words = BlogBannedWordService::make();
        $id = $segments[1] ?? null;

        if (count($segments) > 2 || ($id !== null && !ctype_digit($id))) {
            $notFound();
        }

        if ($id === null) {
            if ($method === 'GET') {
                $respond(200, ['success' => true, 'items' => $words->list()]);
            }

            if ($method === 'POST') {
                $send($words->create($readJson()));
            }

            if ($method === 'DELETE') {
                $requireAdmin(); // same rule as categories: moderators add/edit, admins delete
                $send($words->deleteMany($readJson()['ids'] ?? []));
            }
        } elseif ($method === 'PUT') {
            $send($words->update((int) $id, $readJson()));
        }

        $notFound();
    }

    if ($first === 'picker' && count($segments) === 1 && $method === 'GET') {
        $send($svc->pickerList($_GET['q'] ?? ''));
    }

    if ($first === 'upload' && count($segments) === 1 && $method === 'POST') {
        if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $respond(413, ['success' => false, 'message' => 'The upload is too large.']);
        }

        $send($svc->uploadImage($_FILES));
    }

    if ($first === 'featured') {
        if (count($segments) === 1 && $method === 'GET') {
            $respond(200, ['success' => true, 'featured' => $svc->featuredList()]);
        }

        if (($segments[1] ?? null) === 'reorder' && count($segments) === 2 && $method === 'POST') {
            $send($svc->reorderFeatured($readJson()['ids'] ?? []));
        }

        $notFound();
    }

    if (($first === 'archive' || $first === 'restore') && count($segments) === 1 && $method === 'POST') {
        $requireAdmin();
        $ids = $readJson()['ids'] ?? [];
        $send($first === 'archive' ? $svc->archive($ids) : $svc->restore($ids));
    }

    if ($first === null) {
        if ($method === 'GET') {
            $respond(200, $svc->list($_GET));
        }

        if ($method === 'POST') {
            $send($svc->create($readJson(), $userId));
        }

        if ($method === 'DELETE') {
            $requireAdmin();
            $send($svc->deletePermanent($readJson()['ids'] ?? []));
        }

        $notFound();
    }

    if (ctype_digit($first) && count($segments) <= 2) {
        $id = (int) $first;
        $action = $segments[1] ?? null;

        if ($action === null) {
            if ($method === 'GET') {
                $send($svc->get($id));
            }

            if ($method === 'PATCH') {
                $send($svc->save($id, $readJson(), 'autosave'));
            }

            if ($method === 'PUT') {
                $send($svc->save($id, $readJson(), 'save'));
            }
        } elseif ($method === 'POST') {
            $body = $readJson();

            match ($action) {
                'publish' => $send($svc->publish($id)),
                'schedule' => $send($svc->schedule($id, $body['scheduled_at'] ?? null)),
                'unschedule' => $send($svc->unschedule($id)),
                'featured' => $send($svc->setFeatured($id, filter_var($body['featured'] ?? false, FILTER_VALIDATE_BOOLEAN))),
                default => $notFound(),
            };
        }
    }

    $notFound();
} catch (\Throwable $e) {
    Logger::error($e);
    $respond(500, ['success' => false, 'message' => 'Something went wrong. Please try again.']);
}