<?php
/** @var array<int,array<string,mixed>> $pageArticles */
?>

<?php if (empty($pageArticles)): ?>
    <div class="akd-blog-empty">
        <p class="akd-blog-empty__title">No articles here yet.</p>
        <p class="akd-blog-empty__body">There are no published articles in this category right now.</p>
    </div>
<?php else: ?>
    <div class="akd-blog__grid akd-blog__grid--latest">
        <?php foreach ($pageArticles as $article): $cardVariant = 'grid'; ?>
            <?php require __DIR__ . '/article-card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>