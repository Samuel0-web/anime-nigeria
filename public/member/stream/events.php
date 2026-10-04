<?php
use App\Core\Config;
use App\Core\Logger;
use App\Services\AnnouncementReadService;

/**
 * Member event stream (Server-Sent Events). Currently one event:
 *   event: unread   data: {"count": N, "at": <server ms>}
 *
 * - The member is always the authenticated session user; nothing is read from the client.
 * - It holds one PHP worker for up to STREAM_SECONDS; the browser then reconnects.
 * - Set SSE_ENABLED=0 in .env to switch it off (the endpoint answers 204, which
 *   makes EventSource stop reconnecting).
 */
const STREAM_SECONDS = 50;   // then the browser reconnects (EventSource does this itself)
const TICK_SECONDS = 3;      // how often the member's count is re-read
const STATE_REFRESH = 15;    // resend unchanged state this often (heartbeat that also self-heals)

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit;
}

if (!empty($apiPath)) {
    http_response_code(404);
    exit;
}

if ((string) Config::get('SSE_ENABLED', '1') === '0') {
    http_response_code(204);
    exit;
}

// check(false): validates the session (and the idle timeout) without renewing it.
if (!$auth->check(false)) {
    http_response_code(401);
    exit;
}

$userId = (int) $auth->id();

// PHP holds a lock on the session file for the whole request. Without releasing it,
// this stream would block every other request from the same browser.
session_write_close();

header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache, no-transform');
header('X-Accel-Buffering: no'); // nginx: do not buffer the stream

@ini_set('zlib.output_compression', '0');
while (ob_get_level() > 0) {
    @ob_end_flush();
}

set_time_limit(0);

$write = static function (string $chunk): bool {
    echo $chunk;
    @flush();

    return connection_aborted() === 0;
};

if (!$write("retry: 5000\n\n")) {
    exit;
}

$service = AnnouncementReadService::make();
$deadline = time() + STREAM_SECONDS;
$lastCount = null;
$lastSent = 0;

while (time() < $deadline) {
    // Taken BEFORE the query: the page render stamps its own time AFTER its count,
    // so the client can discard any event that was computed before the page rendered.
    $at = (int) round(microtime(true) * 1000);

    try {
        $count = $service->unreadCount($userId);
    } catch (\Throwable $e) {
        Logger::error($e);
        break;
    }

    if ($count !== $lastCount || time() - $lastSent >= STATE_REFRESH) {
        $ok = $write("event: unread\ndata: " . json_encode(['count' => $count, 'at' => $at]) . "\n\n");
        $lastCount = $count;
        $lastSent = time();
    } else {
        $ok = $write(": keep-alive\n\n"); // also how a closed tab is detected
    }

    if (!$ok) {
        break;
    }

    sleep(TICK_SECONDS);
}