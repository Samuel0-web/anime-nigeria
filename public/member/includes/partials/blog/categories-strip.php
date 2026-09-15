<?php
/** @var array<int,array<string,mixed>> $blogCategories */
if (!empty($blogCategories)): ?>
<section class="akd-blog__categories" aria-labelledby="akdBlogCategoriesHeading">
    <div class="akd-blog__section-head">
        <h2 class="akd-blog__section-title akd-blog__section-title--compact" id="akdBlogCategoriesHeading">Explore categories</h2>
        <a href="/member/blog/category" class="akd-blog__view-all">View all categories</a>
    </div>

    <div class="akd-blog-categories" role="list">
        <?php foreach (array_slice($blogCategories, 0, 6) as $category): ?>
            <a href="/member/blog/category/<?= htmlspecialchars($category['slug']) ?>" class="akd-blog-categories__pill" role="listitem">
                <?= htmlspecialchars($category['label']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>