<?php
/**
 * Expects the following to be defined by the parent view (awards.php):
 *
 * @var array{
 *     previousWinners: list<array{category: string, title: string, accent: string, image: string|null}>,
 * } $awardsOverview
 */

$winners = $awardsOverview['previousWinners'];
?>
<section class="akd-award-section akd-award-section--winners" aria-labelledby="anaa-winners-heading">
    <header class="akd-award-section__header">
        <div class="akd-award-section__heading-group">
            <p class="akd-award-section__eyebrow">Award History</p>
            <h2 class="akd-award-section__title" id="anaa-winners-heading">2025 Winners</h2>
        </div>
        <a href="/member/awards/winners" class="akd-award-section__link">
            View all winners <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </a>
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