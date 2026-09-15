<?php
/** @var string|null $category */

require_once __DIR__ . '/../includes/data/blog-data.php';
require_once __DIR__ . '/../includes/data/blog-support.php';

$categorySlug = $category ?? null;
$activeCategory = $categorySlug !== null
    ? akd_blog_category_by_slug($blogCategories, $categorySlug)
    : null;

if ($categorySlug !== null && $activeCategory === null) {
    http_response_code(404);
    require __DIR__ . '/../404.php';
    exit;
}

$categoryArticles = $activeCategory
    ? akd_blog_articles_by_category($blogArticles, $activeCategory['label'])
    : $blogArticles;
$categoryArticles = akd_blog_sort_by_date_desc($categoryArticles);

$currentPage = akd_blog_current_page();
$pagination = akd_blog_paginate($categoryArticles, $currentPage, AKD_BLOG_PER_PAGE);

$pageArticles = $pagination['items'];
$paginationCurrentPage = $pagination['current_page'];
$paginationTotalPages = $pagination['total_pages'];
$paginationBaseUrl = $activeCategory
    ? '/member/blog/category/' . $activeCategory['slug']
    : '/member/blog/category';

$categoryCounts = akd_blog_category_counts($blogArticles, $blogCategories);

$page_title = $activeCategory ? $activeCategory['label'] : 'Categories';
$page_description = $activeCategory
    ? ($activeCategory['description'] ?? 'Stories from the ' . $activeCategory['label'] . ' category.')
    : 'Browse every story, guide and update from the Anime Nigeria Blog.';

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Blog', 'url' => '/member/blog'],
    ['label' => $activeCategory ? $activeCategory['label'] : 'All Articles', 'url' => null],
];

require_once __DIR__ . '/../includes/header.php';
?>

<main class="akd-content">
    <div class="akd-blog akd-blog-categories-page">
        <?php require __DIR__ . '/../includes/partials/blog/categories-header.php'; ?>
        <?php require __DIR__ . '/../includes/partials/blog/categories-nav.php'; ?>
        <?php require __DIR__ . '/../includes/partials/blog/categories-list.php'; ?>
        <?php require __DIR__ . '/../includes/partials/blog/pagination.php'; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>