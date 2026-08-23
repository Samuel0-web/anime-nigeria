<?php
/**
 * Expects the following to be defined by the parent view (nominations.php):
 *
 * @var array{
 *     phase: string,
 *     phases: array<string, array<string, mixed>>,
 *     year: int,
 *     nomination: array{opensAt: string, closesAt: string, limitPerCategory: int},
 * } $awardsOverview
 */

$phaseKey = $awardsOverview['phase'];
$page     = akd_award_nominations_page_config($awardsOverview['phases'], $phaseKey);
$year     = $awardsOverview['year'];
$deadline = akd_award_format_date($awardsOverview['nomination']['closesAt']);
$limit    = $awardsOverview['nomination']['limitPerCategory'];
?>
<section class="akd-award-status" aria-labelledby="anaa-nominations-heading">
    <div class="akd-award-status__grain" aria-hidden="true"></div>
    <div class="akd-award-status__content">
        <div class="akd-award-status__top">
            <p class="akd-award-status__kicker">ANAA <?= htmlspecialchars((string) $year) ?></p>
            <span class="akd-award-hero__badge akd-award-hero__badge--<?= htmlspecialchars($phaseKey) ?>">
                <span class="akd-award-hero__badge-dot"></span>
                <?= htmlspecialchars($page['statusLabel']) ?>
            </span>
        </div>

        <h1 class="akd-award-status__heading" id="anaa-nominations-heading">
            <?= htmlspecialchars($page['heading']) ?>
        </h1>

        <p class="akd-award-status__desc"><?= htmlspecialchars($page['description']) ?></p>

        <?php if (!empty($page['formActive'])): ?>
            <div class="akd-award-status__meta">
                <div class="akd-award-status__meta-item">
                    <span class="akd-award-status__meta-label">Nominations close</span>
                    <span class="akd-award-status__meta-value"><?= htmlspecialchars($deadline) ?></span>
                </div>
                <div class="akd-award-status__meta-item">
                    <span class="akd-award-status__meta-label">Per category</span>
                    <span class="akd-award-status__meta-value"><?= htmlspecialchars((string) $limit) ?> nomination</span>
                </div>
            </div>
        <?php else: ?>
            <div class="akd-award-status__actions">
                <?php if (!empty($page['ctaLabel']) && !empty($page['ctaLink'])): ?>
                    <a href="<?= htmlspecialchars($page['ctaLink']) ?>" class="akd-award-btn akd-award-btn--primary">
                        <?= htmlspecialchars($page['ctaLabel']) ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>