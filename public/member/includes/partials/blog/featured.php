<?php
/** @var array<int,array<string,mixed>> $blogFeatured published featured articles, ordered by position (max 3) */
if (!empty($blogFeatured)):
    $featuredHero = $blogFeatured[0];
    $featuredRest = array_slice($blogFeatured, 1);
?>
<section class="akd-blog__featured" aria-labelledby="akdBlogFeaturedHeading">
    <h2 class="visually-hidden" id="akdBlogFeaturedHeading">Featured stories</h2>

    <?php $cardVariant = 'hero'; $article = $featuredHero; ?>
    <?php require __DIR__ . '/article-card.php'; ?>

    <?php if (!empty($featuredRest)): ?>
        <div class="akd-blog__grid akd-blog__grid--latest">
            <?php foreach ($featuredRest as $article): $cardVariant = 'grid'; ?>
                <?php require __DIR__ . '/article-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php endif; ?>