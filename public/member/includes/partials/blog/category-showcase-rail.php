<?php
/** @var array{category: array{id:int,slug:string,label:string}, articles: array<int,array<string,mixed>>} $showcase */
$showcaseHeading = 'akdBlogShowcase' . (int) $showcase['category']['id'];
?>
<section class="akd-blog__section akd-blog__anime-culture" aria-labelledby="<?= $showcaseHeading ?>">
    <div class="akd-blog__section-head">
        <h2 class="akd-blog__section-title" id="<?= $showcaseHeading ?>"><?= htmlspecialchars($showcase['category']['label']) ?></h2>
        <a href="/member/blog/category/<?= htmlspecialchars($showcase['category']['slug']) ?>" class="akd-blog__view-all">View all</a>
    </div>

    <div class="akd-blog__rail">
        <?php foreach ($showcase['articles'] as $article): $cardVariant = 'rail-wide'; ?>
            <?php require __DIR__ . '/article-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>