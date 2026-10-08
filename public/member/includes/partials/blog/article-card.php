<?php
/**
 * Renders one article card.
 *
 * @var array<string,mixed> $article  a BlogService::card()
 * @var string|null $cardVariant
 */
$cardVariant ??= 'grid';
$hasImage = !empty($article['image']);
?>
<article class="akd-blog-card akd-blog-card--<?= htmlspecialchars($cardVariant) ?>">
    <a href="<?= htmlspecialchars($article['url']) ?>" class="akd-blog-card__link">
        <div class="akd-blog-card__media">
            <span class="akd-blog-card__placeholder" aria-hidden="true">
                <i class="fa-solid fa-newspaper"></i>
            </span>

            <?php if ($hasImage): ?>
                <img
                    src="<?= htmlspecialchars($article['image']) ?>"
                    alt="<?= htmlspecialchars($article['title']) ?>"
                    class="akd-blog-card__image"
                    loading="lazy"
                >
            <?php endif; ?>
        </div>

        <div class="akd-blog-card__body">
            <span class="akd-blog-card__category"><?= htmlspecialchars(strtoupper($article['category'])) ?></span>
            <?php /* title_html is escaped by BlogTitle::html(); the only markup is <em> */ ?>
            <h3 class="akd-blog-card__title"><?= $article['title_html'] ?></h3>
            <p class="akd-blog-card__excerpt"><?= htmlspecialchars($article['excerpt']) ?></p>

            <div class="akd-blog-card__meta">
                <span><?= htmlspecialchars(akd_blog_format_date($article['published_at'])) ?></span>
                <?php if (!empty($article['reading_time'])): ?>
                    <span class="akd-blog-card__dot" aria-hidden="true">&bull;</span>
                    <span><?= htmlspecialchars($article['reading_time']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </a>
</article>