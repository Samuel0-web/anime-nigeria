<?php
use App\Core\Logger;
use App\Services\BlogCommentService;

require_once __DIR__ . '/../includes/data/blog-support.php';
require_once __DIR__ . '/../includes/data/blog-data.php';

/** @var string|null $slug route parameter: "{slug}-{publicId}" */
$notFound = static function (): never {
    http_response_code(404);
    require __DIR__ . '/../404.php';
    exit;
};

$article = null;
$urlParts = [];

if ($blogService !== null && preg_match('/^(.+)-([a-z0-9]{8})$/', (string) ($slug ?? ''), $urlParts)) {
    try {
        $article = $blogService->article($urlParts[2]);
    } catch (\Throwable $e) {
        Logger::error($e);
        $blogLoadError = true;
    }
}

if ($article === null && !$blogLoadError) {
    $notFound();
}

if ($article !== null && $blogService !== null) {
    // Identity is the public ID. The slug is just the readable part, so a renamed slug redirects.
    if (($urlParts[1] ?? '') !== $article['slug']) {
        header('Location: ' . $article['url'], true, 301);
        exit;
    }

    $articleCategory = $article['category_slug']
        ? ['slug' => $article['category_slug'], 'label' => $article['category']]
        : null;
    $tags = $article['tags'];
    $tocItems = $article['toc'];
    $relatedArticles = [];
    $moreToExplore = [];

    // A discovery failure must not take the whole article down.
    try {
        $relatedArticles = $blogService->moreFromCategory($article);
        $moreToExplore = $blogService->moreToExploreFor($article);
    } catch (\Throwable $e) {
        Logger::error($e);
    }

    // Comments: the first page comes from the database (cached per article); the rest load on demand.
    $commentsArticleId = $article['public_id'];
    $commentsPage = ['items' => [], 'has_more' => false, 'next_cursor' => null, 'total' => 0];
    $commentsLoadError = false;

    try {
        $commentsResult = BlogCommentService::make()->listForArticle($commentsArticleId, null);

        if (!empty($commentsResult['success'])) {
            $commentsPage = $commentsResult;
        } else {
            $commentsLoadError = true;
        }
    } catch (\Throwable $e) {
        Logger::error($e);
        $commentsLoadError = true;
    }

    $hydratedComments = $commentsPage['items'];
    $totalCommentCount = (int) $commentsPage['total'];
    $page_title = $article['title'];
    $page_description = $article['excerpt'];
} else {
    $page_title = 'Article';
    $page_description = '';
}

$navTitle = 'Article';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Blog', 'url' => '/member/blog'],
    ['label' => $page_title, 'url' => null],
];

$allowAdminBlogArticleView = $article !== null;
require_once __DIR__ . '/../includes/header.php';

$publicShareUrl = $article !== null ? akd_blog_public_url($article, $canonicalUrl) : '';
?>

<main class="akd-content">
    <div class="akd-blog akd-post">
        <?php if ($article === null): ?>
            <?php require __DIR__ . '/../includes/partials/blog/load-error.php'; ?>
        <?php else: ?>
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
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>