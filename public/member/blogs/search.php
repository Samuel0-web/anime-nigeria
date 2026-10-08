<?php
use App\Core\Logger;

require_once __DIR__ . '/../includes/data/blog-support.php';
require_once __DIR__ . '/../includes/data/blog-data.php';

$searchQuery = akd_blog_search_query();
$hasQuery = $searchQuery !== '';
$pageData = ['items' => [], 'has_more' => false, 'page' => 1];

if ($hasQuery && $blogService !== null) {
    try {
        // Database search, paginated with the same look-ahead. Nothing is filtered in PHP or JS.
        $pageData = $blogService->search($searchQuery, akd_blog_current_page());
    } catch (\Throwable $e) {
        Logger::error($e);
        $blogLoadError = true;
    }
}

$pageArticles = $pageData['items'];
$paginationCurrentPage = $pageData['page'];
$paginationHasMore = $pageData['has_more'];
$paginationBaseUrl = '/member/blog/search';
$paginationQueryParams = $hasQuery ? ['q' => $searchQuery] : [];

$page_title = $hasQuery ? 'Search: ' . $searchQuery : 'Search';
$page_description = $hasQuery
    ? 'Search results for "' . $searchQuery . '" on the Anime Nigeria Blog.'
    : 'Search the Anime Nigeria Blog for stories, guides and community updates.';

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Blog', 'url' => '/member/blog'],
    ['label' => $hasQuery ? 'Search results' : 'Search', 'url' => null],
];

require_once __DIR__ . '/../includes/header.php';
?>

<main class="akd-content">
    <div class="akd-blog akd-blog-search-page">
        <?php require __DIR__ . '/../includes/partials/blog/search-header.php'; ?>

        <?php if ($blogLoadError): ?>
            <?php require __DIR__ . '/../includes/partials/blog/load-error.php'; ?>
        <?php elseif ($hasQuery): ?>
            <?php if (empty($pageArticles)): ?>
                <div class="akd-blog-empty">
                    <p class="akd-blog-empty__title">No articles found.</p>
                    <p class="akd-blog-empty__body">We could not find any articles matching "<?= htmlspecialchars($searchQuery) ?>". Try a different search term.</p>
                </div>
            <?php else: ?>
                <div class="akd-blog__grid akd-blog__grid--latest">
                    <?php foreach ($pageArticles as $article): $cardVariant = 'grid'; ?>
                        <?php require __DIR__ . '/../includes/partials/blog/article-card.php'; ?>
                    <?php endforeach; ?>
                </div>

                <?php require __DIR__ . '/../includes/partials/blog/pagination.php'; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>