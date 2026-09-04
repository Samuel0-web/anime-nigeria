<?php

/**
 * Handles both the 'voting' and 'final' phases — the interaction is
 * identical (listen/view, then vote); only the copy, entries and a
 * modifier class differ.
 *
 * Expects the following to be defined by the parent view (challenges.php):
 *
 * @var array{
 *     phases: array<string, array<string, mixed>>,
 *     phase: string,
 *     voting: array{maxSelections: int},
 * } $challenge
 */

$phase     = akd_challenge_phase_config($challenge['phases'], $challenge['phase']);
$entries   = akd_challenge_active_entries($challenge);
$mediaKind = akd_challenge_media_config($challenge['type'])['kind'];
$isFinal   = $challenge['phase'] === 'final';

if ($isFinal) {
    $voteBudget = (int) $challenge['voting']['finalMaxSelections'];
} else {
    $displayRound = akd_challenge_round_by_id($challenge['rounds'], akd_challenge_display_round_id($challenge));
    $roundSize    = $displayRound !== null ? (int) $displayRound['participantCount'] : count($entries);
    $voteBudget   = akd_challenge_vote_budget($roundSize);
}
?>
<section class="akd-challenge-panel akd-challenge-panel--voting<?= $isFinal ? ' akd-challenge-panel--final' : '' ?>"
    id="voting" aria-labelledby="challenge-panel-heading"
    data-voting-root data-max-selections="<?= $voteBudget ?>"
>
    <h2 class="akd-challenge-panel__heading" id="challenge-panel-heading"><?= htmlspecialchars($phase['heading']) ?></h2>
    <p class="akd-challenge-panel__desc"><?= htmlspecialchars($phase['description']) ?></p>

    <p class="akd-challenge-panel__vote-summary">
        You have <strong data-votes-remaining><?= $voteBudget ?></strong>
        <?= $voteBudget === 1 ? 'vote' : 'votes' ?>.
        <?= $voteBudget === 1 ? 'Pick the entry you like most.' : 'Vote once for each entry you like.' ?>
    </p>

    <div class="akd-challenge-grid<?= $isFinal ? ' akd-challenge-grid--final' : '' ?>">
        <?php foreach ($entries as $entry): ?>
            <?php $votingActive = true; ?>
            <?php require __DIR__ . '/submission-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>