<?php
use App\Core\Logger;
use App\Services\BlogCommentService;
use App\Services\BlogService;

header('Content-Type: application/json');
header('Cache-Control: no-store');

$respond = static function (int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
};

$user = $auth->guest() ? null : $auth->user();

if ($user === null) {
    $respond(401, ['success' => false, 'message' => 'Unauthorized.']);
}

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

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$segments = $apiPath ?? [];
$first = $segments[0] ?? null;
$notFound = static fn () => $respond(404, ['success' => false, 'message' => 'API endpoint not found.']);

try {
    // GET /member/api/blog/search?q=
    if ($first === 'search' && count($segments) === 1) {
        if ($method !== 'GET') {
            header('Allow: GET');
            $respond(405, ['success' => false, 'message' => 'Method not allowed.']);
        }

        $query = $_GET['q'] ?? '';
        $query = is_string($query) ? mb_substr(trim($query), 0, 100) : '';

        if (mb_strlen($query) < 2) {
            $respond(200, ['success' => true, 'items' => []]);
        }

        $respond(200, ['success' => true, 'items' => BlogService::make()->suggest($query)]);
    }

    // /member/api/blog/comments[/{id}[/replies]]
    if ($first === 'comments') {
        $id = $segments[1] ?? null;
        $sub = $segments[2] ?? null;

        if (count($segments) > 3 || ($id !== null && !ctype_digit($id))) {
            $notFound();
        }

        $comments = BlogCommentService::make();

        if ($id === null) {
            if ($method === 'GET') {
                $send($comments->listForArticle((string) ($_GET['article'] ?? ''), $_GET['before'] ?? null));
            }

            if ($method === 'POST') {
                $body = $readJson();
                $send($comments->create($user, (string) ($body['article'] ?? ''),
                    $body['content'] ?? '', $body['target_id'] ?? null));
            }
        } elseif ($sub === 'replies' && $method === 'GET') {
            $send($comments->replies((int) $id, $_GET['after'] ?? null));
        } elseif ($sub === null && $method === 'DELETE') {
            $send($comments->delete($user, (int) $id));
        }
    }

    $notFound();
} catch (\Throwable $e) {
    Logger::error($e);
    $respond(500, ['success' => false, 'message' => 'Something went wrong. Please try again.']);
}