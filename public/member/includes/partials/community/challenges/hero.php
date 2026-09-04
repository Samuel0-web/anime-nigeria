<?php

/**
 * Expects the following to be defined by the parent view (challenges.php):
 *
 * @var array{
 *     phase: string,
 *     phases: array<string, array{badge: string, ctaLabel: string|null, ctaTarget: string|null, note: string|null}>,
 *     title: string,
 *     tagline: string,
 *     image: string,
 *     imageAlt: string,
 *     communityLine: string,
 * } $challenge
 */

$phaseKey = $challenge['phase'];
$phase    = akd_challenge_phase_config($challenge['phases'], $phaseKey);
?>
<section class="akd-challenge-hero" aria-labelledby="challenge-hero-heading">
    <div class="akd-challenge-hero__art" aria-hidden="true">
        <img src="<?= htmlspecialchars($challenge['image']) ?>" alt="" class="akd-challenge-hero__art-img">
        <div class="akd-challenge-hero__art-fade"></div>
        <div class="akd-challenge-hero__art-glow"></div>
    </div>
    <div class="akd-challenge-hero__grain" aria-hidden="true"></div>

    <div class="akd-challenge-hero__content">
        <p class="akd-challenge-hero__kicker"><?= htmlspecialchars($challenge['communityLine']) ?></p>

        <span class="akd-challenge-hero__badge akd-challenge-hero__badge--<?= htmlspecialchars($phaseKey) ?>">
            <span class="akd-challenge-hero__badge-dot" aria-hidden="true"></span>
            <?= htmlspecialchars($phase['badge']) ?>
        </span>

        <h1 class="akd-challenge-hero__title" id="challenge-hero-heading">
            <?= htmlspecialchars($challenge['title']) ?>
        </h1>

        <p class="akd-challenge-hero__tagline"><?= htmlspecialchars($challenge['tagline']) ?></p>

        <?php if (!empty($phase['ctaLabel']) && !empty($phase['ctaTarget'])): ?>
            <div class="akd-challenge-hero__actions">
                <a href="<?= htmlspecialchars($phase['ctaTarget']) ?>" class="akd-challenge-btn akd-challenge-btn--primary">
                    <?= htmlspecialchars($phase['ctaLabel']) ?>
                </a>
            </div>
        <?php endif; ?>

        <?php if (!empty($phase['note'])): ?>
            <p class="akd-challenge-hero__note">
                <i class="fa-solid fa-clock" aria-hidden="true"></i>
                <?= htmlspecialchars($phase['note']) ?>
            </p>
        <?php endif; ?>
    </div>
</section>