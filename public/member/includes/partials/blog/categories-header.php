<?php
/**
 * @var array|null $activeCategory
 * @var array{items: array, current_page: int, per_page: int, total_items: int, total_pages: int} $pagination
 */
$categoryHeading = $activeCategory ? $activeCategory['label'] : 'All';
$categoryCopy = $activeCategory
    ? ($activeCategory['description'] ?? '')
    : 'Every story, guide and update from the Anime Nigeria Blog.';
$categoryArticleCount = $pagination['total_items'];
?>
<?php require __DIR__ . '/back-link.php'; ?>

<section class="akd-blog-categories-page__intro">
    <div>
        <h2 class="akd-blog__title akd-blog-categories-page__title"><?= htmlspecialchars($categoryHeading) ?></h2>
        <p class="akd-blog__subtitle"><?= htmlspecialchars($categoryCopy) ?></p>
    </div>
    <span class="akd-blog-categories-page__count">
        <?= (int) $categoryArticleCount ?> <?= $categoryArticleCount === 1 ? 'article' : 'articles' ?>
    </span>
</section>