<?php
/**
 * Data for the member Announcements page, from the database via
 * AnnouncementService (cached). No SQL lives in the page or its partials.
 *
 * Provides:
 *  - $announcements        first batch (max AnnouncementService::MEMBER_PAGE_SIZE rows)
 *  - $announceCategories   categories that currently have announcements
 *  - $announceHasMore      whether another batch exists
 *  - $announceNextCursor   cursor for the next batch, or null
 *  - $announceLoadError    true when loading failed
 *
 * Row fields: id, category_id, category (name), accent, date, title, excerpt,
 * cta, url, featured, image, image_alt.
 */

use App\Core\Logger;
use App\Services\AnnouncementService;

$announcements = [];
$announceCategories = [];
$announceHasMore = false;
$announceNextCursor = null;
$announceLoadError = false;

try {
    $announceService = AnnouncementService::make();
    $announceCategories = $announceService->memberCategories();
    $announceBatch = $announceService->memberBatch(null, null);
    $announcements = $announceBatch['items'];
    $announceHasMore = $announceBatch['has_more'];
    $announceNextCursor = $announceBatch['next_cursor'];
} catch (\Throwable $e) {
    Logger::error($e);
    $announceLoadError = true;
}