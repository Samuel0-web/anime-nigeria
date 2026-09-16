<?php
/** @var array<string,mixed> $article */
$hasImage = !empty($article['image']);
?>
<div class="akd-post__hero">
    <div class="akd-post-hero__media">
        <?php if ($hasImage): ?>
            <a
                href="<?= htmlspecialchars($article['image']) ?>"
                data-fancybox
                data-caption="<?= htmlspecialchars($article['title'], ENT_QUOTES) ?>"
            >
                <img
                    src="<?= htmlspecialchars($article['image']) ?>"
                    alt="<?= htmlspecialchars($article['title']) ?>"
                    class="akd-post-hero__image"
                >
            </a>
        <?php else: ?>
            <span class="akd-post-hero__placeholder" aria-hidden="true">
                <i class="fa-solid fa-newspaper"></i>
            </span>
        <?php endif; ?>
    </div>
</div>