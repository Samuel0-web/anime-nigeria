<?php
/**
 * Compact page intro for the open-nominations state: kicker, heading,
 * live status pill, and the rules strip. Not a full photographic hero,
 * the Overview page already owns that visual moment. This page leads
 * with participation instead.
 *
 * Expects the following to be defined by the parent view (nominations.php):
 *
 * @var array{phase: string, phases: array<string, array<string, mixed>>} $ancaOverview
 * @var array{state: string, label: string} $nominationStatus
 */

$currentPhase = $ancaOverview['phase'];
$phaseNote    = akd_anca_phase_config($ancaOverview['phases'], $currentPhase)['statusNote'] ?? '';
?>
<header class="akd-anca-nom-intro">
    <p class="akd-anca-hero__kicker">Anime Nigeria Community Awards</p>
    <h1 class="akd-anca-nom-intro__heading">Nominations</h1>

    <div class="akd-anca-hero__meta">
        <span class="akd-anca-status akd-anca-status--<?= htmlspecialchars($currentPhase) ?>">
            <span class="akd-anca-status__dot"></span>
            <?= htmlspecialchars($nominationStatus['label']) ?>
        </span>
        <?php if ($phaseNote): ?>
            <span class="akd-anca-hero__note"><?= htmlspecialchars($phaseNote) ?></span>
        <?php endif; ?>
    </div>

    <?php require __DIR__ . '/rules.php'; ?>
</header>