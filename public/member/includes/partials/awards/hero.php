<?php
/**
 * Expects the following to be defined by the parent view (awards.php):
 *
 * @var array{
 *     phase: string,
 *     phases: array<string, array{
 *         badge: string,
 *         heroDescription: string,
 *         primaryCtaLabel: string,
 *         primaryCtaLink: string,
 *         secondaryCtaLabel: string|null,
 *         secondaryCtaLink: string|null,
 *     }>,
 *     hero: array{kicker: string, headline: string, image: string, imageAlt: string},
 *     stats: array{categories: int, nominees: int},
 *     year: int,
 * } $awardsOverview
 */

$hero     = $awardsOverview['hero'];
$stats    = $awardsOverview['stats'];
$year     = $awardsOverview['year'];
$phaseKey = $awardsOverview['phase'];
$phase    = akd_award_phase_config($awardsOverview['phases'], $phaseKey);
?>
<section class="akd-award-hero" aria-labelledby="anaa-hero-heading">
    <div class="akd-award-hero__art" aria-hidden="true">
        <img src="<?= htmlspecialchars($hero['image']) ?>" alt="" class="akd-award-hero__art-img">
        <div class="akd-award-hero__art-fade"></div>
        <div class="akd-award-hero__art-glow"></div>
    </div>
    <div class="akd-award-hero__grain" aria-hidden="true"></div>

    <div class="akd-award-hero__content">
        <div class="akd-award-hero__top">
            <p class="akd-award-hero__kicker"><?= htmlspecialchars($hero['kicker']) ?></p>
            <span class="akd-award-hero__badge akd-award-hero__badge--<?= htmlspecialchars($phaseKey) ?>">
                <span class="akd-award-hero__badge-dot"></span>
                <?= htmlspecialchars($phase['badge']) ?>
            </span>
        </div>

        <h1 class="akd-award-hero__headline" id="anaa-hero-heading">
            <?= htmlspecialchars($hero['headline']) ?>
        </h1>

        <p class="akd-award-hero__desc"><?= htmlspecialchars($phase['heroDescription']) ?></p>

        <div class="akd-award-hero__actions">
            <a href="<?= htmlspecialchars($phase['primaryCtaLink']) ?>" class="akd-award-btn akd-award-btn--primary">
                <?= htmlspecialchars($phase['primaryCtaLabel']) ?>
            </a>
            <?php if (!empty($phase['secondaryCtaLabel']) && !empty($phase['secondaryCtaLink'])): ?>
                <a href="<?= htmlspecialchars($phase['secondaryCtaLink']) ?>" class="akd-award-btn akd-award-btn--ghost">
                    <?= htmlspecialchars($phase['secondaryCtaLabel']) ?>
                </a>
            <?php endif; ?>
        </div>

        <dl class="akd-award-hero__stats">
            <div class="akd-award-hero__stat">
                <dt>Season</dt>
                <dd><?= htmlspecialchars((string) $year) ?></dd>
            </div>
            <div class="akd-award-hero__stat">
                <dt>Categories</dt>
                <dd><?= htmlspecialchars((string) $stats['categories']) ?></dd>
            </div>
            <div class="akd-award-hero__stat">
                <dt>Nominees</dt>
                <dd><?= htmlspecialchars((string) $stats['nominees']) ?></dd>
            </div>
        </dl>
    </div>
</section>