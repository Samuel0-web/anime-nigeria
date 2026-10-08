<?php
declare(strict_types=1);

// Usage: php database/seeders/import-blog-mock.php
// Expects the ORIGINAL mock file at database/seeders/data/blog-mock.php (copy of the old blog-data.php).

require_once __DIR__ . '/../../vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__ . '/../..')->safeLoad();

define('ROOT_PATH', dirname(__DIR__, 2));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');

require __DIR__ . '/data/blog-mock.php'; // defines $blogCategories, $blogArticles

use App\Database\Database;
use App\Models\BlogCategory;
use App\Services\BlogAdminService;

$db = Database::connection();
$categories = new BlogCategory($db);
$service = BlogAdminService::make();

$e = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$toHtml = static function (array $blocks) use ($e): string {
    $html = '';

    foreach ($blocks as $b) {
        switch ($b['type'] ?? '') {
            case 'paragraph':
                $html .= '<p>' . $b['text'] . '</p>'; // mock paragraphs allow inline markup; the sanitiser cleans it
                break;
            case 'heading':
                $html .= '<h2>' . $e($b['text']) . '</h2>';
                break;
            case 'list':
                $tag = ($b['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul';
                $html .= "<$tag>" . implode('', array_map(static fn ($i) => '<li><p>' . $i . '</p></li>', $b['items'])) . "</$tag>";
                break;
            case 'quote':
                $html .= '<blockquote><p>' . $b['text'] . '</p>'
                    . (!empty($b['cite']) ? '<p>' . $e('— ' . $b['cite']) . '</p>' : '') . '</blockquote>';
                break;
            case 'image':
                $html .= '<figure><img src="' . $e($b['src']) . '" alt="' . $e($b['alt'] ?? '') . '">'
                    . (!empty($b['caption']) ? '<figcaption>' . $e($b['caption']) . '</figcaption>' : '') . '</figure>';
                break;
            case 'separator':
                $html .= '<hr>';
                break;
        }
    }

    return $html;
};

$byLabel = [];

foreach ($blogCategories as $c) {
    $found = $categories->findByLabel($c['label']);
    $id = $found !== false ? (int) $found['id'] : $categories->create($c['slug'], $c['label'], $c['description']);
    $byLabel[$c['label']] = $id;
    echo ($found !== false ? 'Category exists: ' : 'Category created: ') . $c['label'] . PHP_EOL;
}

$featuredPlaced = false;
$exists = $db->prepare('SELECT 1 FROM blog_articles WHERE title = ? LIMIT 1');

foreach ($blogArticles as $a) {
    $exists->execute([$a['title']]);

    if ($exists->fetchColumn() !== false) {
        echo 'Skipped (already imported): ' . $a['title'] . PHP_EOL;
        continue;
    }

    $position = null;

    if (!empty($a['featured']) && !$featuredPlaced) {
        $position = 1;
        $featuredPlaced = true;
    }

    $service->importPublished([
        'title' => $a['title'],
        'excerpt' => $a['excerpt'],
        'category_id' => $byLabel[$a['category']] ?? null,
        'tags' => $a['tags'] ?? [],
        'cover_image' => $a['image'] ?? null,
        'content_html' => $toHtml($a['content'] ?? []),
        'published_at' => $a['published_at'] . ' 09:00:00',
        'featured_position' => $position,
    ]);

    echo 'Imported: ' . $a['title'] . PHP_EOL;
}

echo 'Done.' . PHP_EOL;