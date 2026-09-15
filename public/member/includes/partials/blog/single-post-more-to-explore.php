<?php
/** @var array<int,array<string,mixed>> $moreToExplore */
if (!empty($moreToExplore)): ?>
<section class="akd-post-related" aria-labelledby="akdMoreExploreHeading">
    <h2 class="akd-blog__section-title" id="akdMoreExploreHeading">More to Explore</h2>
    <div class="akd-blog__rail">
        <?php foreach ($moreToExplore as $article): $cardVariant = 'rail-wide'; ?>
            <?php require __DIR__ . '/article-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>