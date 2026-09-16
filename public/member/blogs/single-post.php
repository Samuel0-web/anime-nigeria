<?php
require_once __DIR__ . '/../includes/data/blog-data.php';
require_once __DIR__ . '/../includes/data/blog-support.php';

/** @var string|null $slug */
$article = akd_blog_find_by_slug($blogArticles, $slug ?? '');

if ($article === null) {
    http_response_code(404);
    require __DIR__ . '/../404.php';
    exit;
}

$articleCategory = akd_blog_category_by_label($blogCategories, $article['category']);
$tags = $article['tags'] ?? [];
$relatedArticles = akd_blog_related_articles($blogArticles, $article, 3);
$moreToExploreExcludeIds = array_merge([$article['id']], array_column($relatedArticles, 'id'));
$moreToExplore = akd_blog_more_to_explore($blogArticles, $article, $moreToExploreExcludeIds, 3);
$prepared = akd_blog_prepare_content($article['content'] ?? []);
$contentBlocks = $prepared['blocks'];
$tocItems = $prepared['toc'];
$hydratedComments = akd_blog_comments_for_article($blogComments ?? [], $article['id']);
$totalCommentCount = akd_blog_count_comments_with_replies($hydratedComments);
$page_title = $article['title'];
$navTitle = 'Article';
$page_description = $article['excerpt'];

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Blog', 'url' => '/member/blog'],
    ['label' => $article['title'], 'url' => null],
];

require_once __DIR__ . '/../includes/header.php';

$publicShareUrl = akd_blog_public_url($article, $canonicalUrl);
?>

<main class="akd-content">
    <div class="akd-blog akd-post">
        <?php require __DIR__ . '/../includes/partials/blog/single-post-header.php'; ?>
        <?php require __DIR__ . '/../includes/partials/blog/single-post-hero.php'; ?>

        <div class="akd-post__layout">
            <div class="akd-post__main">
                <?php require __DIR__ . '/../includes/partials/blog/single-post-content.php'; ?>
                <?php require __DIR__ . '/../includes/partials/blog/single-post-tags.php'; ?>
            </div>

            <?php require __DIR__ . '/../includes/partials/blog/single-post-sidebar.php'; ?>
        </div>

        <?php require __DIR__ . '/../includes/partials/blog/comments-section.php'; ?>
        <?php require __DIR__ . '/../includes/partials/blog/related-articles.php'; ?>
        <?php require __DIR__ . '/../includes/partials/blog/single-post-more-to-explore.php'; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>