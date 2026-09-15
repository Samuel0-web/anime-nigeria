<?php
require_once __DIR__ . '/../includes/data/blog-data.php';
require_once __DIR__ . '/../includes/data/blog-support.php';

$searchQuery = akd_blog_search_query();
$hasQuery = $searchQuery !== '';

$matchedArticles = $hasQuery
    ? akd_blog_sort_by_date_desc(akd_blog_search($blogArticles, $searchQuery))
    : [];

$currentPage = akd_blog_current_page();
$pagination = akd_blog_paginate($matchedArticles, $currentPage, AKD_BLOG_PER_PAGE);

$pageArticles = $pagination['items'];
$paginationCurrentPage = $pagination['current_page'];
$paginationTotalPages = $pagination['total_pages'];
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

        <?php if ($hasQuery): ?>
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
            <?php endif; ?>
        <?php endif; ?>

        <?php require __DIR__ . '/../includes/partials/blog/pagination.php'; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>