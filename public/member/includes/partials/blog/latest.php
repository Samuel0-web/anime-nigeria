<?php
/** @var array<int,array<string,mixed>> $blogLatest */
if (!empty($blogLatest)): ?>
<section class="akd-blog__section akd-blog__latest" aria-labelledby="akdBlogLatestHeading">
    <div class="akd-blog__section-head">
        <h2 class="akd-blog__section-title" id="akdBlogLatestHeading">Latest Articles</h2>
        <a href="/member/blog/posts" class="akd-blog__view-all">View all</a>
    </div>

    <div class="akd-blog__grid akd-blog__grid--latest">
        <?php foreach ($blogLatest as $article): $cardVariant = 'grid'; ?>
            <?php require __DIR__ . '/article-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>