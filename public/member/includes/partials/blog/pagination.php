<?php
/**
 * Presentational pagination control shared by every Blog archive page
 * (All Articles, Categories, Search results). Expects the caller to
 * have already prepared:
 *
 * @var int $paginationCurrentPage
 * @var int $paginationTotalPages
 * @var string $paginationBaseUrl
 * @var array<string,string>|null $paginationQueryParams Optional. Extra
 *      query parameters (such as the search term) to preserve on every
 *      page link. Defaults to none.
 */
$paginationQueryParams ??= [];

if ($paginationTotalPages <= 1) {
    return;
}

$paginationPages = akd_blog_pagination_pages($paginationCurrentPage, $paginationTotalPages);

$akdBlogPageUrl = static function (int $page) use ($paginationBaseUrl, $paginationQueryParams): string {
    $params = $paginationQueryParams;

    if ($page > 1) {
        $params['page'] = (string) $page;
    }

    return empty($params) ? $paginationBaseUrl : $paginationBaseUrl . '?' . http_build_query($params);
};
?>

<nav class="akd-blog-pagination" aria-label="Pagination">
    <?php if ($paginationCurrentPage > 1): ?>
        <a href="<?= htmlspecialchars($akdBlogPageUrl($paginationCurrentPage - 1)) ?>" class="akd-blog-pagination__btn akd-blog-pagination__btn--prev" aria-label="Previous page">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </a>
    <?php else: ?>
        <span class="akd-blog-pagination__btn akd-blog-pagination__btn--prev is-disabled" aria-disabled="true">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </span>
    <?php endif; ?>

    <div class="akd-blog-pagination__pages">
        <?php foreach ($paginationPages as $paginationPage): ?>
            <?php if ($paginationPage === 'ellipsis'): ?>
                <span class="akd-blog-pagination__ellipsis" aria-hidden="true">&hellip;</span>
            <?php elseif ($paginationPage === $paginationCurrentPage): ?>
                <span class="akd-blog-pagination__page is-active" aria-current="page"><?= (int) $paginationPage ?></span>
            <?php else: ?>
                <a href="<?= htmlspecialchars($akdBlogPageUrl((int) $paginationPage)) ?>" class="akd-blog-pagination__page"><?= (int) $paginationPage ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php if ($paginationCurrentPage < $paginationTotalPages): ?>
        <a href="<?= htmlspecialchars($akdBlogPageUrl($paginationCurrentPage + 1)) ?>" class="akd-blog-pagination__btn akd-blog-pagination__btn--next" aria-label="Next page">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </a>
    <?php else: ?>
        <span class="akd-blog-pagination__btn akd-blog-pagination__btn--next is-disabled" aria-disabled="true">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </span>
    <?php endif; ?>
</nav>