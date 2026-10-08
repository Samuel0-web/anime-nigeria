<?php
use App\Core\Logger;

require_once __DIR__ . '/../includes/data/blog-support.php';
require_once __DIR__ . '/../includes/data/blog-data.php';

$pageData = ['items' => [], 'has_more' => false, 'page' => 1];
$blogTotal = 0;

if ($blogService !== null) {
    try {
        $pageData = $blogService->list(null, akd_blog_current_page());
        $blogTotal = array_sum(array_column($blogService->categories(), 'count'));
    } catch (\Throwable $e) {
        Logger::error($e);
        $blogLoadError = true;
    }
}

$pageArticles = $pageData['items'];
$paginationCurrentPage = $pageData['page'];
$paginationHasMore = $pageData['has_more'];
$paginationBaseUrl = '/member/blog/posts';

$page_title = 'All Articles';
$page_description = 'Browse every story, guide and update from the Anime Nigeria Blog.';

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Blog', 'url' => '/member/blog'],
    ['label' => 'All Articles', 'url' => null],
];

require_once __DIR__ . '/../includes/header.php';
?>

<main class="akd-content">
    <div class="akd-blog akd-blog-archive-page">
        <section class="akd-blog-archive-page__intro">
            <?php require __DIR__ . '/../includes/partials/blog/back-link.php'; ?>

            <div class="akd-blog-archive-page__header">
                <div>
                    <h2 class="akd-blog__title">All Articles</h2>
                    <p class="akd-blog__subtitle">Every story, guide and update from the Anime Nigeria Blog.</p>
                </div>
                <span class="akd-blog-archive-page__count">
                    <?= (int) $blogTotal ?> <?= $blogTotal === 1 ? 'article' : 'articles' ?>
                </span>
            </div>
        </section>

        <?php if ($blogLoadError): ?>
            <?php require __DIR__ . '/../includes/partials/blog/load-error.php'; ?>
        <?php elseif (empty($pageArticles)): ?>
            <div class="akd-blog-empty">
                <p class="akd-blog-empty__title">No articles here yet.</p>
                <p class="akd-blog-empty__body">There are no published articles right now.</p>
            </div>
        <?php else: ?>
            <div class="akd-blog__grid akd-blog__grid--latest">
                <?php foreach ($pageArticles as $article): $cardVariant = 'grid'; ?>
                    <?php require __DIR__ . '/../includes/partials/blog/article-card.php'; ?>
                <?php endforeach; ?>
            </div>

            <?php require __DIR__ . '/../includes/partials/blog/pagination.php'; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>