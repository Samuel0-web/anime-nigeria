<?php

/**
 * Tie-break voting. Distinct from normal voting: exactly one vote,
 * and only the tied entries are shown. Reuses submission-card.php and
 * the same data-voting-root JS hook as phase-voting.php — no second
 * voting system.
 *
 * Expects the following to be defined by the parent view (challenges.php):
 *
 * @var array{
 *     phases: array<string, array<string, mixed>>,
 *     phase: string,
 *     voting: array{tieBreakMaxSelections: int},
 *     tieBreak: array{qualifyingPosition: int, deadline: ?string},
 * } $challenge
 */

$phase      = akd_challenge_phase_config($challenge['phases'], $challenge['phase']);
$entries    = akd_challenge_active_entries($challenge);
$mediaKind  = akd_challenge_media_config($challenge['type'])['kind'];
$voteBudget = (int) $challenge['voting']['tieBreakMaxSelections'];
$deadline   = $challenge['tieBreak']['deadline'] ?? null;
?>
<section class="akd-challenge-panel akd-challenge-panel--tie-break" id="voting" aria-labelledby="challenge-panel-heading"
    data-voting-root data-max-selections="<?= $voteBudget ?>"
>
    <p class="akd-challenge-panel__eyebrow">Tie-break</p>
    <h2 class="akd-challenge-panel__heading" id="challenge-panel-heading"><?= htmlspecialchars($phase['heading']) ?></h2>
    <p class="akd-challenge-panel__desc">
        These entries are tied for qualifying position <?= (int) $challenge['tieBreak']['qualifyingPosition'] ?>.
        <?= htmlspecialchars($phase['description']) ?>
    </p>

    <p class="akd-challenge-panel__vote-summary">
        You have <strong data-votes-remaining><?= $voteBudget ?></strong> vote.
        Choose one entry to advance.
    </p>

    <?php if ($deadline !== null): ?>
        <p class="akd-challenge-panel__note">
            <i class="fa-solid fa-clock" aria-hidden="true"></i>
            Tie-break voting closes <?= htmlspecialchars(akd_challenge_format_date($deadline)) ?>.
        </p>
    <?php endif; ?>

    <div class="akd-challenge-grid">
        <?php foreach ($entries as $entry): ?>
            <?php $votingActive = true; ?>
            <?php require __DIR__ . '/submission-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>