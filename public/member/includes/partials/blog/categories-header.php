<?php
/**
 * @var array|null $activeCategory
 * @var int $categoryTotal published articles in the active category (or in all of them)
 */
$categoryHeading = $activeCategory ? $activeCategory['label'] : 'All';
$categoryCopy = $activeCategory
    ? ($activeCategory['description'] ?? '')
    : 'Every story, guide and update from the Anime Nigeria Blog.';

$categoryArticleCount = (int) ($categoryTotal ?? 0);
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