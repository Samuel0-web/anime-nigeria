<?php
$page_title       = "Blog";
$page_description = "Stories, news, guides and community moments from Anime Nigeria.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Blog', 'url' => null],
];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/data/blog-data.php';
require_once __DIR__ . '/../includes/data/blog-support.php';

$blogFeatured = akd_blog_find_featured($blogArticles);
$usedArticleIds = $blogFeatured ? [$blogFeatured['id']] : [];

$blogLatest    = akd_blog_pick($blogArticles, $usedArticleIds, 4);
$blogCommunity = akd_blog_pick($blogArticles, $usedArticleIds, 3, 'Community');
$blogAnime     = akd_blog_pick($blogArticles, $usedArticleIds, 3, 'Anime');
$blogMore      = akd_blog_pick($blogArticles, $usedArticleIds, 3);
?>

<main class="akd-content">
    <div class="akd-blog">
        <?php require __DIR__ . '/../includes/partials/blog/header-search.php'; ?>
        <?php require __DIR__ . '/../includes/partials/blog/featured.php'; ?>
        <?php require __DIR__ . '/../includes/partials/blog/latest.php'; ?>
        <?php require __DIR__ . '/../includes/partials/blog/categories-strip.php'; ?>
        <?php require __DIR__ . '/../includes/partials/blog/community.php'; ?>
        <?php require __DIR__ . '/../includes/partials/blog/anime-culture.php'; ?>
        <?php require __DIR__ . '/../includes/partials/blog/more-to-explore.php'; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>