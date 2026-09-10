<?php
/**
 * Expects the following to be defined by the parent view (overview.php):
 *
 * @var array{
 *     phase: string,
 *     phases: array<string, array{statusLabel: string, statusNote: string, primaryLabel: string|null, primaryLink: string|null}>,
 *     edition: int,
 *     hero: array{kicker: string, tagline: string, image: string, imageAlt: string},
 * } $ancaOverview
 */

$phaseKey = $ancaOverview['phase'];
$phase    = akd_anca_phase_config($ancaOverview['phases'], $phaseKey);
$hero     = $ancaOverview['hero'];
?>
<section class="akd-anca-hero" aria-labelledby="anca-hero-heading">
    <div class="akd-anca-hero__art">
        <img
            src="<?= htmlspecialchars($hero['image']) ?>"
            alt="<?= htmlspecialchars($hero['imageAlt']) ?>"
            class="akd-anca-hero__art-img"
        >
        <div class="akd-anca-hero__art-scrim" aria-hidden="true"></div>
    </div>

    <div class="akd-anca-hero__grain" aria-hidden="true"></div>

    <div class="akd-anca-hero__content">
        <p class="akd-anca-hero__kicker"><?= htmlspecialchars($hero['kicker']) ?></p>

        <h1 class="akd-anca-hero__headline" id="anca-hero-heading">
            ANCA <?= htmlspecialchars((string) $ancaOverview['edition']) ?>
        </h1>

        <p class="akd-anca-hero__tagline"><?= htmlspecialchars($hero['tagline']) ?></p>

        <div class="akd-anca-hero__meta">
            <span class="akd-anca-status akd-anca-status--<?= htmlspecialchars($phaseKey) ?>">
                <span class="akd-anca-status__dot"></span>
                <?= htmlspecialchars($phase['statusLabel']) ?>
            </span>
            <span class="akd-anca-hero__note"><?= htmlspecialchars($phase['statusNote']) ?></span>
        </div>

        <?php if (!empty($phase['primaryLabel']) && !empty($phase['primaryLink'])): ?>
            <div class="akd-anca-hero__actions">
                <a href="<?= htmlspecialchars($phase['primaryLink']) ?>" class="akd-award-btn akd-award-btn--primary">
                    <?= htmlspecialchars($phase['primaryLabel']) ?>
                </a>
                <a href="#anca-philosophy" class="akd-award-btn akd-award-btn--ghost">
                    Explore ANCA
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>