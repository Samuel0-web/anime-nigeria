<?php
/**
 * Expects the following to be defined by the parent view (winners.php):
 *
 * @var array{
 *     phase: string,
 *     year: int,
 *     hero: array{image: string, imageAlt: string},
 * } $awardsOverview
 * @var array{statusLabel: string, heading: string, description: string, revealWinners: bool, ctaLabel: string|null, ctaLink: string|null} $winnersPage
 */

$phaseKey = $awardsOverview['phase'];
$hero     = $awardsOverview['hero'];
$year     = $awardsOverview['year'];
?>
<section class="akd-award-hero akd-award-hero--winners" aria-labelledby="anaa-winners-hero-heading">
    <div class="akd-award-hero__art" aria-hidden="true">
        <img src="<?= htmlspecialchars($hero['image']) ?>" alt="" class="akd-award-hero__art-img">
        <div class="akd-award-hero__art-fade"></div>
    </div>
    
    <div class="akd-award-hero__grain" aria-hidden="true"></div>

    <div class="akd-award-hero__content">
        <div class="akd-award-hero__top">
            <p class="akd-award-hero__kicker">Anime Nigeria Anime Awards</p>
            <span class="akd-award-hero__badge akd-award-hero__badge--<?= htmlspecialchars($phaseKey) ?>">
                <span class="akd-award-hero__badge-dot"></span>
                <?= htmlspecialchars($winnersPage['statusLabel']) ?>
            </span>
        </div>

        <h1 class="akd-award-hero__headline" id="anaa-winners-hero-heading">
            ANAA <?= htmlspecialchars((string) $year) ?><br>The Winners
        </h1>

        <p class="akd-award-hero__desc"><?= htmlspecialchars($winnersPage['description']) ?></p>

        <?php if (!empty($winnersPage['ctaLabel']) && !empty($winnersPage['ctaLink'])): ?>
            <div class="akd-award-hero__actions">
                <a href="<?= htmlspecialchars($winnersPage['ctaLink']) ?>" class="akd-award-btn akd-award-btn--primary">
                    <?= htmlspecialchars($winnersPage['ctaLabel']) ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>