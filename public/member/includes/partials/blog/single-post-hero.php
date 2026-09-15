<?php
/** @var array<string,mixed> $article */
$hasImage = !empty($article['image']);
?>
<div class="akd-post__hero">
    <div class="akd-blog-card__media">
        <span class="akd-blog-card__placeholder" aria-hidden="true">
            <i class="fa-solid fa-newspaper"></i>
        </span>

        <?php if ($hasImage): ?>
            <img
                src="<?= htmlspecialchars($article['image']) ?>"
                alt="<?= htmlspecialchars($article['title']) ?>"
                class="akd-blog-card__image"
                data-post-lightbox-image
                data-lightbox-caption="<?= htmlspecialchars($article['title']) ?>"
            >
        <?php endif; ?>
    </div>
</div>