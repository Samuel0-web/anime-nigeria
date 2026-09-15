<?php
/**
 * @var array<string,mixed> $article
 * @var array<string,mixed>|null $articleCategory
 */
?>
<header class="akd-post__header">
    <?php require __DIR__ . '/back-link.php'; ?>

    <?php if ($articleCategory): ?>
        <a href="/member/blog/category/<?= htmlspecialchars($articleCategory['slug']) ?>" class="akd-post__category">
            <?= htmlspecialchars(strtoupper($articleCategory['label'])) ?>
        </a>
    <?php else: ?>
        <span class="akd-post__category"><?= htmlspecialchars(strtoupper($article['category'])) ?></span>
    <?php endif; ?>

    <h1 class="akd-post__title"><?= htmlspecialchars($article['title']) ?></h1>

    <div class="akd-post__meta">
        <span class="akd-post__author"><?= htmlspecialchars($article['author'] ?? 'Anime Nigeria Editorial') ?></span>
        <span class="akd-post__dot" aria-hidden="true">&bull;</span>
        <span><?= htmlspecialchars(akd_blog_format_date($article['published_at'])) ?></span>
        <?php if (!empty($article['updated_at'])): ?>
            <span class="akd-post__dot" aria-hidden="true">&bull;</span>
            <span>Updated <?= htmlspecialchars(akd_blog_format_date($article['updated_at'])) ?></span>
        <?php endif; ?>
        <?php if (!empty($article['reading_time'])): ?>
            <span class="akd-post__dot" aria-hidden="true">&bull;</span>
            <span><?= htmlspecialchars($article['reading_time']) ?></span>
        <?php endif; ?>
    </div>
</header>