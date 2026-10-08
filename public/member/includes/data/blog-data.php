<?php
/**
 * Entry point to the database-backed Blog for member pages. No SQL lives in pages or partials.
 *
 * Provides:
 *  - $blogService       App\Services\BlogService, or null when it could not be created
 *  - $blogCategories    categories that currently have at least one published article
 *  - $blogLoadError     true when loading failed
 */

use App\Core\Logger;
use App\Services\BlogService;

$blogLoadError = false;
$blogService = null;
$blogCategories = [];

try {
    $blogService = BlogService::make();
    $blogCategories = array_values(array_filter(
        $blogService->categories(),
        static fn (array $c): bool => $c['count'] > 0
    ));
} catch (\Throwable $e) {
    Logger::error($e);
    $blogLoadError = true;
}