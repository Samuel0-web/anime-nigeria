<?php
/** @var array<int,array<string,mixed>> $blogMore */
if (!empty($blogMore)): ?>
<section class="akd-blog__section akd-blog__more" aria-labelledby="akdBlogMoreHeading">
    <div class="akd-blog__section-head">
        <h2 class="akd-blog__section-title akd-blog__section-title--compact" id="akdBlogMoreHeading">More to Explore</h2>
    </div>

    <div class="akd-blog__grid akd-blog__grid--compact">
        <?php foreach ($blogMore as $article): $cardVariant = 'compact'; ?>
            <?php require __DIR__ . '/article-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>