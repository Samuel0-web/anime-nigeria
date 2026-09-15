<?php
/** @var array<string,mixed>|null $blogFeatured */
if ($blogFeatured): $cardVariant = 'hero'; $article = $blogFeatured; ?>
<section class="akd-blog__featured" aria-labelledby="akdBlogFeaturedHeading">
    <h2 class="visually-hidden" id="akdBlogFeaturedHeading">Featured story</h2>
    <?php require __DIR__ . '/article-card.php'; ?>
</section>
<?php endif; ?>