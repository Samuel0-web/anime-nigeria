<?php
/** @var array<int,array<string,mixed>> $blogCommunity */
if (!empty($blogCommunity)): ?>
<section class="akd-blog__section akd-blog__community" aria-labelledby="akdBlogCommunityHeading">
    <div class="akd-blog__section-head">
        <h2 class="akd-blog__section-title" id="akdBlogCommunityHeading">From the Community</h2>
    </div>

    <div class="akd-blog__editorial akd-blog__editorial--community">
        <?php foreach ($blogCommunity as $index => $article): $cardVariant = $index === 0 ? 'feature-secondary' : 'rail'; ?>
            <?php require __DIR__ . '/article-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>