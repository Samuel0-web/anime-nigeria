<?php
/**
 * @var bool $hasQuery
 * @var string $searchQuery
 * @var array{items: array, current_page: int, per_page: int, total_items: int, total_pages: int} $pagination
 * @var array<int,array<string,mixed>> $blogArticles
 */
$searchResultCount = $pagination['total_items'];
$searchBoxValue = $searchQuery;
?>
<?php require __DIR__ . '/back-link.php'; ?>

<section class="akd-blog__intro">
    <div>
        <h2 class="akd-blog__title"><?= $hasQuery ? 'Search results' : 'Search the Blog' ?></h2>
        <p class="akd-blog__subtitle">
            <?php if ($hasQuery): ?>
                Results for "<?= htmlspecialchars($searchQuery) ?>"<?php if ($searchResultCount > 0): ?> &middot; <?= (int) $searchResultCount ?> <?= $searchResultCount === 1 ? 'article' : 'articles' ?> found<?php endif; ?>
            <?php else: ?>
                Enter a search term to find articles about anime, community events, news, guides and more.
            <?php endif; ?>
        </p>
    </div>

    <?php require __DIR__ . '/search-box.php'; ?>
</section>