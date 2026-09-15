<?php
/**
 * @var array|null $activeCategory
 * @var array<int,array<string,mixed>> $blogCategories
 * @var array<string,int> $categoryCounts
 */
?>

<nav class="akd-blog-categories-nav" aria-label="Browse categories">
    <a href="/member/blog/category"
        class="akd-blog-categories-nav__item<?= $activeCategory === null ? ' is-active' : '' ?>"
        <?= $activeCategory === null ? 'aria-current="page"' : '' ?>
    >
        All
    </a>
    <?php foreach ($blogCategories as $navCategory): ?>
        
        <a href="/member/blog/category/<?= htmlspecialchars($navCategory['slug']) ?>"
            class="akd-blog-categories-nav__item<?= ($activeCategory && $activeCategory['slug'] === $navCategory['slug']) ? ' is-active' : '' ?>"
            <?= ($activeCategory && $activeCategory['slug'] === $navCategory['slug']) ? 'aria-current="page"' : '' ?>
        >
            <?= htmlspecialchars($navCategory['label']) ?>
            <span class="akd-blog-categories-nav__count"><?= (int) ($categoryCounts[$navCategory['slug']] ?? 0) ?></span>
        </a>
    <?php endforeach; ?>
</nav>