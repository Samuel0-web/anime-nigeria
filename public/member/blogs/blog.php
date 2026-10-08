<?php
use App\Core\Logger;

$page_title       = "Blog";
$page_description = "Stories, news, guides and community moments from Anime Nigeria.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Blog', 'url' => null],
];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/data/blog-support.php';
require_once __DIR__ . '/../includes/data/blog-data.php';

$home = ['featured' => [], 'latest' => [], 'showcases' => [], 'more' => []];

if ($blogService !== null) {
    try {
        $home = $blogService->homepage();
    } catch (\Throwable $e) {
        Logger::error($e);
        $blogLoadError = true;
    }
}

$blogFeatured  = $home['featured'];
$blogLatest    = $home['latest'];
$blogShowcases = $home['showcases'];
$blogMore      = $home['more'];
?>

<main class="akd-content">
    <div class="akd-blog">
        <?php require __DIR__ . '/../includes/partials/blog/header-search.php'; ?>

        <?php if ($blogLoadError): ?>
            <?php require __DIR__ . '/../includes/partials/blog/load-error.php'; ?>
        <?php elseif (empty($blogFeatured) && empty($blogLatest)): ?>
            <div class="akd-blog-empty">
                <p class="akd-blog-empty__title">No articles yet.</p>
                <p class="akd-blog-empty__body">New stories will appear here as soon as they are published.</p>
            </div>
        <?php else: ?>
            <?php require __DIR__ . '/../includes/partials/blog/featured.php'; ?>
            <?php require __DIR__ . '/../includes/partials/blog/latest.php'; ?>
            <?php require __DIR__ . '/../includes/partials/blog/categories-strip.php'; ?>

            <?php foreach ($blogShowcases as $showcaseIndex => $showcase): ?>
                <?php /* Category A uses layout 1, category B uses layout 2. */ ?>
                <?php require __DIR__ . '/../includes/partials/blog/category-showcase-' . ($showcaseIndex === 0 ? 'feature' : 'rail') . '.php'; ?>
            <?php endforeach; ?>

            <?php require __DIR__ . '/../includes/partials/blog/more-to-explore.php'; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>