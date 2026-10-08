<?php
/** @var string|null $category route parameter (category slug) */
use App\Core\Logger;

require_once __DIR__ . '/../includes/data/blog-support.php';
require_once __DIR__ . '/../includes/data/blog-data.php';

$categorySlug = $category ?? null;
$allCategories = [];
$activeCategory = null;
$pageData = ['items' => [], 'has_more' => false, 'page' => 1];

if ($blogService !== null) {
    try {
        $allCategories = $blogService->categories();
        $activeCategory = $categorySlug !== null ? $blogService->categoryBySlug($categorySlug) : null;

        if ($categorySlug !== null && $activeCategory === null) {
            http_response_code(404);
            require __DIR__ . '/../404.php';
            exit;
        }

        // The category filter is applied in SQL, with the same LIMIT + 1 look-ahead as every list.
        $pageData = $blogService->list($activeCategory['id'] ?? null, akd_blog_current_page());
    } catch (\Throwable $e) {
        Logger::error($e);
        $blogLoadError = true;
    }
}

// Nav lists categories that have articles, plus the one being viewed.
$blogCategories = array_values(array_filter($allCategories, static fn (array $c): bool =>
    $c['count'] > 0 || ($activeCategory !== null && $c['id'] === $activeCategory['id'])));
$categoryCounts = array_column($allCategories, 'count', 'slug');
$categoryTotal = $activeCategory !== null ? $activeCategory['count'] : array_sum($categoryCounts);

$pageArticles = $pageData['items'];
$paginationCurrentPage = $pageData['page'];
$paginationHasMore = $pageData['has_more'];
$paginationBaseUrl = $activeCategory ? '/member/blog/category/' . $activeCategory['slug'] : '/member/blog/category';

$page_title = $activeCategory ? $activeCategory['label'] : 'Categories';
$page_description = $activeCategory
    ? ($activeCategory['description'] ?: 'Stories from the ' . $activeCategory['label'] . ' category.')
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

        <?php if ($blogLoadError): ?>
            <?php require __DIR__ . '/../includes/partials/blog/load-error.php'; ?>
        <?php else: ?>
            <?php require __DIR__ . '/../includes/partials/blog/categories-list.php'; ?>
            <?php require __DIR__ . '/../includes/partials/blog/pagination.php'; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>