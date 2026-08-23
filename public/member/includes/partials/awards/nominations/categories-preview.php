<?php
/**
 * Expects the following to be defined by the parent view (nominations.php):
 *
 * @var array{
 *     categories: list<array{slug: string, name: string, count: int, accent: string, image: string|null, featured?: bool}>,
 * } $awardsOverview
 */

$categories = $awardsOverview['categories'];
?>
<section class="akd-award-section" aria-labelledby="anaa-categories-preview-heading">
    <header class="akd-award-section__header">
        <div class="akd-award-section__heading-group">
            <p class="akd-award-section__eyebrow">What's Up For Nomination</p>
            <h2 class="akd-award-section__title" id="anaa-categories-preview-heading">Categories open for community nominations</h2>
            <p class="akd-award-section__desc">A preview of every category the community will help decide this season.</p>
        </div>
    </header>

    <div class="akd-award-categories akd-award-categories--compact">
        <?php foreach ($categories as $category): ?>
            <div class="akd-award-category akd-award-category--<?= htmlspecialchars($category['accent']) ?> akd-award-category--disabled">
                <?php if (!empty($category['image'])): ?>
                    <img src="<?= htmlspecialchars($category['image']) ?>" alt="" class="akd-award-category__img" aria-hidden="true">
                <?php endif; ?>
                <span class="akd-award-category__gradient" aria-hidden="true"></span>
                <span class="akd-award-category__body">
                    <span class="akd-award-category__count"><?= htmlspecialchars((string) $category['count']) ?> nominees</span>
                    <span class="akd-award-category__name"><?= htmlspecialchars($category['name']) ?></span>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</section>