<?php
use App\Core\Logger;
use App\Services\AnnouncementService;

header('Content-Type: application/json');

$respond = static function (int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    $respond(405, ['success' => false, 'message' => 'Method not allowed.']);
}

if (!empty($apiPath)) {
    $respond(404, ['success' => false, 'message' => 'API endpoint not found.']);
}

if ($auth->guest()) {
    $respond(401, ['success' => false, 'message' => 'Unauthorized.']);
}

// ---- Input ----------------------------------------------------------------
$categoryId = null;
$rawCategory = $_GET['category'] ?? null;

if ($rawCategory !== null && $rawCategory !== '' && $rawCategory !== 'all') {
    if (!is_string($rawCategory) || !ctype_digit($rawCategory) || (int) $rawCategory < 1) {
        $respond(400, ['success' => false, 'message' => 'Invalid category.']);
    }

    $categoryId = (int) $rawCategory;
}

$cursor = $_GET['cursor'] ?? null;

if ($cursor !== null && (!is_string($cursor) || AnnouncementService::parseCursor($cursor) === false)) {
    $respond(400, ['success' => false, 'message' => 'Invalid cursor.']);
}

// ---- Batch ----------------------------------------------------------------
$bufferLevel = ob_get_level();

try {
    $batch = AnnouncementService::make()->memberBatch($categoryId, $cursor);

    require_once PUBLIC_PATH . '/member/includes/data/announcements-support.php';

    unset($index); // row.php: no position given, so images use native lazy loading
    $html = '';

    foreach ($batch['items'] as $item) {
        ob_start();
        require PUBLIC_PATH . '/member/includes/partials/announcements/row.php';
        $html .= (string) ob_get_clean();
    }

    $respond(200, [
        'success' => true,
        'html' => $html,
        'count' => count($batch['items']),
        'has_more' => $batch['has_more'],
        'next_cursor' => $batch['next_cursor'],
    ]);
} catch (\Throwable $e) {
    while (ob_get_level() > $bufferLevel) {
        ob_end_clean();
    }

    Logger::error($e);
    $respond(500, ['success' => false, 'message' => 'Something went wrong. Please try again.']);
}