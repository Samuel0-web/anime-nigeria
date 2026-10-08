<?php
/**
 * Previous / Next control driven by the look-ahead row (no COUNT query).
 *
 * @var int $paginationCurrentPage
 * @var bool $paginationHasMore
 * @var string $paginationBaseUrl
 * @var array<string,string>|null $paginationQueryParams extra params kept on every link (e.g. q)
 */
$paginationQueryParams ??= [];

if ($paginationCurrentPage <= 1 && !$paginationHasMore) {
    return;
}

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
        <span class="akd-blog-pagination__page is-active" aria-current="page">Page <?= (int) $paginationCurrentPage ?></span>
    </div>

    <?php if ($paginationHasMore): ?>
        <a href="<?= htmlspecialchars($akdBlogPageUrl($paginationCurrentPage + 1)) ?>" class="akd-blog-pagination__btn akd-blog-pagination__btn--next" aria-label="Next page">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </a>
    <?php else: ?>
        <span class="akd-blog-pagination__btn akd-blog-pagination__btn--next is-disabled" aria-disabled="true">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </span>
    <?php endif; ?>
</nav>