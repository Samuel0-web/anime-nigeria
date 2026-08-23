<?php
/**
 * Expects the following to be defined by the parent view (voting.php):
 *
 * @var array{
 *     phase: string,
 *     phases: array<string, array<string, mixed>>,
 * } $awardsOverview
 */

$phaseKey = $awardsOverview['phase'];
$page     = akd_award_voting_page_config($awardsOverview['phases'], $phaseKey);
?>
<section class="akd-award-status" aria-labelledby="anaa-voting-heading">
    <div class="akd-award-status__grain" aria-hidden="true"></div>
    <div class="akd-award-status__content">
        <div class="akd-award-status__top">
            <p class="akd-award-status__kicker">ANAA 2026</p>
            <span class="akd-award-hero__badge akd-award-hero__badge--<?= htmlspecialchars($phaseKey) ?>">
                <span class="akd-award-hero__badge-dot"></span>
                <?= htmlspecialchars($page['statusLabel']) ?>
            </span>
        </div>

        <h1 class="akd-award-status__heading" id="anaa-voting-heading">
            <?= htmlspecialchars($page['heading']) ?>
        </h1>

        <p class="akd-award-status__desc"><?= htmlspecialchars($page['description']) ?></p>

        <div class="akd-award-status__actions">
            <?php if (!empty($page['ctaLabel']) && !empty($page['ctaLink'])): ?>
                <a href="<?= htmlspecialchars($page['ctaLink']) ?>" class="akd-award-btn akd-award-btn--primary">
                    <?= htmlspecialchars($page['ctaLabel']) ?>
                </a>
            <?php endif; ?>
            <a href="/member/awards/overview" class="akd-award-btn akd-award-btn--ghost">ANAA Overview</a>
        </div>
    </div>
</section>