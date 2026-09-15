<?php
/**
 * @var array<int,array<string,mixed>> $relatedArticles
 * @var array<string,mixed> $article
 */
$relatedHeadingCategory = $article['category'];

if (!empty($relatedArticles)): ?>
<section class="akd-post-related" aria-labelledby="akdRelatedHeading">
    <h2 class="akd-blog__section-title" id="akdRelatedHeading">More from <?= htmlspecialchars($relatedHeadingCategory) ?></h2>
    <div class="akd-blog__grid akd-blog__grid--compact">
        <?php foreach ($relatedArticles as $article): $cardVariant = 'compact'; ?>
            <?php require __DIR__ . '/article-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>