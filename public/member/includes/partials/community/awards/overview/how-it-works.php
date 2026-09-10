<?php
/**
 * Expects the following to be defined by the parent view (overview.php):
 *
 * @var array{
 *     phase: string,
 *     howItWorks: list<array{key: string, label: string, description: string, link: string, cta: string}>,
 * } $ancaOverview
 */

$currentPhase = $ancaOverview['phase'];
$steps        = $ancaOverview['howItWorks'];
?>
<section class="akd-anca-section" id="anca-how-it-works" aria-labelledby="anca-how-heading">
    <header class="akd-anca-section__header">
        <p class="akd-anca-section__eyebrow">How ANCA Works</p>
        <h2 class="akd-anca-section__title" id="anca-how-heading">Nominate, vote, and see who wins</h2>
        <p class="akd-anca-section__desc">Members put names forward, the community votes, and winners are announced for the current edition.</p>
    </header>

    <div class="akd-anca-steps">
        <?php foreach ($steps as $step): ?>
            <?php $status = akd_anca_stage_status($step['key'], $currentPhase); ?>
            <a href="<?= htmlspecialchars($step['link']) ?>" class="akd-anca-step">
                <span class="akd-anca-step__status akd-anca-step__status--<?= htmlspecialchars($status['state']) ?>">
                    <?= htmlspecialchars($status['label']) ?>
                </span>
                <span class="akd-anca-step__label"><?= htmlspecialchars($step['label']) ?></span>
                <span class="akd-anca-step__desc"><?= htmlspecialchars($step['description']) ?></span>
                <span class="akd-anca-step__link">
                    Learn more <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</section>