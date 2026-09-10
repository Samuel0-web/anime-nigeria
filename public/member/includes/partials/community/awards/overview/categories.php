<?php
/**
 * Expects the following to be defined by the parent view (overview.php):
 *
 * @var array{
 *     categories: list<array{slug: string, name: string, blurb: string, accent: string}>,
 * } $ancaOverview
 */

// Preview slice: the Overview always shows the first seven entries from
// the full category dataset. The dataset itself can grow beyond seven
// later (for Nominations/Voting) without this slice changing.
$categories = array_slice($ancaOverview['categories'], 0, 7);
?>
<section class="akd-anca-section" aria-labelledby="anca-categories-heading">
    <header class="akd-anca-section__header">
        <p class="akd-anca-section__eyebrow">The Categories</p>
        <h2 class="akd-anca-section__title" id="anca-categories-heading">Recognition across the community</h2>
        <p class="akd-anca-section__desc">Categories built around participation, personality, and the everyday contributions that keep this community running.</p>
    </header>

    <div class="akd-anca-categories">
        <?php foreach ($categories as $category): ?>
            <div class="akd-anca-category akd-anca-category--<?= htmlspecialchars($category['accent']) ?>">
                <span class="akd-anca-category__name"><?= htmlspecialchars($category['name']) ?></span>
                <span class="akd-anca-category__blurb"><?= htmlspecialchars($category['blurb']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>