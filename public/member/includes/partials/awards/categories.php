<?php
/**
 * Expects the following to be defined by the parent view (awards.php):
 *
 * @var array{
 *     phase: string,
 *     phases: array<string, array{categoryCtaLabel: string, categoryLink: string|null}>,
 *     categories: list<array{
 *         slug: string, name: string, blurb: string, count: int,
 *         accent: string, image: string|null, featured?: bool,
 *     }>,
 * } $awardsOverview
 */

$phaseKey   = $awardsOverview['phase'];
$phase      = akd_award_phase_config($awardsOverview['phases'], $phaseKey);
$categories = $awardsOverview['categories'];
$actionable = !empty($phase['categoryLink']);
?>
<section class="akd-award-section" aria-labelledby="anaa-categories-heading">
    <header class="akd-award-section__header">
        <div class="akd-award-section__heading-group">
            <p class="akd-award-section__eyebrow">Featured Categories</p>
            <h2 class="akd-award-section__title" id="anaa-categories-heading">The 2026 ANAA</h2>
            <p class="akd-award-section__desc">Explore the categories and discover this year's community picks.</p>
        </div>
    </header>

    <div class="akd-award-categories">
        <?php foreach ($categories as $category): ?>
            <a
                <?php if ($actionable): ?>
                    href="<?= htmlspecialchars($phase['categoryLink']) ?>"
                <?php else: ?>
                    aria-disabled="true"
                    tabindex="-1"
                <?php endif; ?>
                class="akd-award-category akd-award-category--<?= htmlspecialchars($category['accent']) ?><?= !empty($category['featured']) ? ' akd-award-category--featured' : '' ?><?= $actionable ? '' : ' akd-award-category--disabled' ?>"
            >
                <?php if (!empty($category['image'])): ?>
                    <img
                        src="<?= htmlspecialchars($category['image']) ?>"
                        alt=""
                        class="akd-award-category__img"
                        aria-hidden="true"
                    >
                <?php endif; ?>

                <span class="akd-award-category__gradient" aria-hidden="true"></span>

                <span class="akd-award-category__body">
                    <span class="akd-award-category__count">
                        <?= htmlspecialchars((string) $category['count']) ?> nominees
                    </span>

                    <span class="akd-award-category__name">
                        <?= htmlspecialchars($category['name']) ?>
                    </span>

                    <span class="akd-award-category__blurb">
                        <?= htmlspecialchars($category['blurb']) ?>
                    </span>

                    <span class="akd-award-category__link">
                        <?= htmlspecialchars($phase['categoryCtaLabel']) ?>
                        <?php if ($actionable): ?>
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        <?php endif; ?>
                    </span>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</section>