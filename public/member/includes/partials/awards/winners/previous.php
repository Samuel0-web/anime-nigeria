<?php
/**
 * A quieter look back at ANAA 2025, separate from the current year's
 * results above. No link back to this page since the user is already
 * here (the existing 'View all winners' link on the Overview page's
 * own previous-winners section points here for that reason).
 *
 * Expects the following to be defined by the parent view (winners.php):
 *
 * @var array{
 *     previousWinners: list<array{category: string, title: string, accent: string, image: string|null}>,
 * } $awardsOverview
 */

$winners = $awardsOverview['previousWinners'];
?>
<section class="akd-award-section akd-award-section--winners" aria-labelledby="anaa-2025-winners-heading">
    <header class="akd-award-section__header">
        <div class="akd-award-section__heading-group">
            <p class="akd-award-section__eyebrow">Award History</p>
            <h2 class="akd-award-section__title" id="anaa-2025-winners-heading">ANAA 2025</h2>
            <p class="akd-award-section__desc">A look back at last year's winners, for continuity between seasons.</p>
        </div>
    </header>

    <div class="akd-award-winners">
        <?php foreach ($winners as $winner): ?>
            <div class="akd-award-winner akd-award-winner--<?= htmlspecialchars($winner['accent']) ?>">
                <?php if (!empty($winner['image'])): ?>
                    <img src="<?= htmlspecialchars($winner['image']) ?>" alt="" class="akd-award-winner__img" aria-hidden="true">
                <?php endif; ?>
                <span class="akd-award-winner__gradient" aria-hidden="true"></span>
                <i class="fa-solid fa-trophy akd-award-winner__icon" aria-hidden="true"></i>
                <span class="akd-award-winner__category"><?= htmlspecialchars($winner['category']) ?></span>
                <span class="akd-award-winner__title"><?= htmlspecialchars($winner['title']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>