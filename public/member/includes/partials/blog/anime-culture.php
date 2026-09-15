<?php
/** @var array<int,array<string,mixed>> $blogAnime */
if (!empty($blogAnime)): ?>
<section class="akd-blog__section akd-blog__anime-culture" aria-labelledby="akdBlogAnimeHeading">
    <div class="akd-blog__section-head">
        <h2 class="akd-blog__section-title" id="akdBlogAnimeHeading">Anime & Culture</h2>
    </div>

    <div class="akd-blog__rail">
        <?php foreach ($blogAnime as $article): $cardVariant = 'rail-wide'; ?>
            <?php require __DIR__ . '/article-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>