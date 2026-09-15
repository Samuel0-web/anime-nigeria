<?php
require_once __DIR__ . '/../includes/data/blog-data.php';
require_once __DIR__ . '/../includes/data/blog-support.php';

$sortedArticles = akd_blog_sort_by_date_desc($blogArticles);
$currentPage = akd_blog_current_page();
$pagination = akd_blog_paginate($sortedArticles, $currentPage, AKD_BLOG_PER_PAGE);

$pageArticles = $pagination['items'];
$paginationCurrentPage = $pagination['current_page'];
$paginationTotalPages = $pagination['total_pages'];
$paginationBaseUrl = '/member/blog/post';

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
                    <?= (int) $pagination['total_items'] ?> <?= $pagination['total_items'] === 1 ? 'article' : 'articles' ?>
                </span>
            </div>
        </section>

        <?php if (empty($pageArticles)): ?>
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
        <?php endif; ?>

        <?php require __DIR__ . '/../includes/partials/blog/pagination.php'; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>