<?php
/**
 * Expects the following to be defined by the parent view (awards.php):
 *
 * @var array{
 *     phase: string,
 *     phases: array<string, array{ctaHeading: string, ctaDescription: string, ctaButtonLabel: string, ctaButtonLink: string}>,
 * } $awardsOverview
 */

$phaseKey = $awardsOverview['phase'];
$phase    = akd_award_phase_config($awardsOverview['phases'], $phaseKey);
?>
<section class="akd-award-cta" aria-labelledby="anaa-cta-heading">
    <div class="akd-award-cta__grain" aria-hidden="true"></div>
    <div class="akd-award-cta__content">
        <h2 class="akd-award-cta__heading" id="anaa-cta-heading"><?= htmlspecialchars($phase['ctaHeading']) ?></h2>
        <p class="akd-award-cta__desc"><?= htmlspecialchars($phase['ctaDescription']) ?></p>
        <a href="<?= htmlspecialchars($phase['ctaButtonLink']) ?>" class="akd-award-btn akd-award-btn--primary akd-award-btn--lg">
            <?= htmlspecialchars($phase['ctaButtonLabel']) ?>
        </a>
    </div>
</section>