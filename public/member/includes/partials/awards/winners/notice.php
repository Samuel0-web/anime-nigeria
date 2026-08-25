<?php
/**
 * Shown when the current phase does not yet reveal winners. The status
 * message itself lives in hero.php; this partial keeps the page
 * populated with a category preview rather than an empty gap.
 *
 * Expects the following to be defined by the parent view (winners.php):
 *
 * @var array{
 *     categories: list<array{name: string, accent: string, image: string|null}>,
 * } $awardsOverview
 */

$categories = $awardsOverview['categories'];
?>
<section class="akd-award-section" aria-labelledby="anaa-winners-preview-heading">
    <header class="akd-award-section__header">
        <div class="akd-award-section__heading-group">
            <p class="akd-award-section__eyebrow">The Nine Categories</p>
            <h2 class="akd-award-section__title" id="anaa-winners-preview-heading">What's up for the win</h2>
            <p class="akd-award-section__desc">Winners are revealed once the award season reaches its final stage.</p>
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
                    <span class="akd-award-category__name"><?= htmlspecialchars($category['name']) ?></span>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</section>