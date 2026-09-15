<?php
/** @var array<string,mixed> $article */
$hasImage = !empty($article['image']);
?>
<div class="akd-post__hero">
    <div class="akd-post-hero__media">
        <?php if ($hasImage): ?>
            <img
                src="<?= htmlspecialchars($article['image']) ?>"
                alt="<?= htmlspecialchars($article['title']) ?>"
                class="akd-post-hero__image"
                data-post-lightbox-image
                data-lightbox-caption="<?= htmlspecialchars($article['title']) ?>"
            >
        <?php else: ?>
            <span class="akd-post-hero__placeholder" aria-hidden="true">
                <i class="fa-solid fa-newspaper"></i>
            </span>
        <?php endif; ?>
    </div>
</div>