<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');

try {
    $count = App\Services\BlogAdminService::make()->publishDue();
    fwrite(STDOUT, "Published {$count} scheduled article(s).\n");
} catch (Throwable $e) {
    App\Core\Logger::error($e);
    fwrite(STDERR, "Scheduled publishing failed: {$e->getMessage()}\n");
    exit(1);
}