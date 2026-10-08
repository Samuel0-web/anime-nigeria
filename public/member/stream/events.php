<?php
use App\Core\Config;
use App\Core\Logger;
use App\Services\AnnouncementReadService;

/**
 * Member event stream (Server-Sent Events).
 *
 *   event: unread   data: {"count": N, "at": <server ms>}      default topic
 *   event: clock    data: {"bucket": N, "at": <server ms>}     opt-in: ?topics=clock
 *
 * "clock" has no database access. `bucket` is floor(unix time / 180): it changes once every three
 * minutes by the wall clock, so the cadence survives the 50-second reconnects. A connection sends
 * the current bucket first, then only when it changes. Pages use it to refresh relative timestamps.
 *
 * - The member is always the authenticated session user; nothing is read from the client.
 * - It holds one PHP worker for up to STREAM_SECONDS; the browser then reconnects.
 * - Set SSE_ENABLED=0 in .env to switch it off (the endpoint answers 204, which
 *   makes EventSource stop reconnecting).
 * - No ?topics= means "unread" only, exactly as before.
 */
const STREAM_SECONDS = 50;       // then the browser reconnects (EventSource does this itself)
const TICK_SECONDS = 3;          // how often the member's count is re-read
const CLOCK_TICK_SECONDS = 10;   // clock-only streams do no work, so they tick (and detect a closed tab) less often
const STATE_REFRESH = 15;        // resend unchanged state this often (heartbeat that also self-heals)
const CLOCK_BUCKET_SECONDS = 180;

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

if (!$auth->check()) {
    http_response_code(401);
    exit;
}

$requested = isset($_GET['topics']) && is_string($_GET['topics']) ? explode(',', $_GET['topics']) : ['unread'];
$topics = array_values(array_intersect(['unread', 'clock'], array_map('trim', $requested)));

if (!$topics) {
    http_response_code(400);
    exit;
}

$wantUnread = in_array('unread', $topics, true);
$wantClock = in_array('clock', $topics, true);

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

$service = $wantUnread ? AnnouncementReadService::make() : null;
$tick = $wantUnread ? TICK_SECONDS : CLOCK_TICK_SECONDS;
$deadline = time() + STREAM_SECONDS;
$lastCount = null;
$lastSent = 0;
$lastBucket = null;

while (time() < $deadline) {
    // Taken BEFORE the query: the page render stamps its own time AFTER its count,
    // so the client can discard any event that was computed before the page rendered.
    $at = (int) round(microtime(true) * 1000);
    $sent = false;
    $ok = true;

    if ($wantUnread) {
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
            $sent = true;
        }
    }

    if ($ok && $wantClock) {
        $bucket = intdiv(time(), CLOCK_BUCKET_SECONDS);

        if ($bucket !== $lastBucket) {
            $ok = $write("event: clock\ndata: " . json_encode(['bucket' => $bucket, 'at' => $at]) . "\n\n");
            $lastBucket = $bucket;
            $sent = true;
        }
    }

    if ($ok && !$sent) {
        $ok = $write(": keep-alive\n\n"); // also how a closed tab is detected
    }

    if (!$ok) {
        break;
    }

    sleep($tick);
}