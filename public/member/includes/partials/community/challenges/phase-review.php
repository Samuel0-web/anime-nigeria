<?php

/**
 * Expects the following to be defined by the parent view (challenges.php):
 *
 * @var array{
 *     phases: array<string, array<string, mixed>>,
 *     phase: string,
 *     entries: list<array<string, mixed>>,
 * } $challenge
 */

$phase     = akd_challenge_phase_config($challenge['phases'], $challenge['phase']);
$entries   = akd_challenge_active_entries($challenge);
$mediaKind = akd_challenge_media_config($challenge['type'])['kind'];
?>
<section class="akd-challenge-panel akd-challenge-panel--review" aria-labelledby="challenge-panel-heading">
    <h2 class="akd-challenge-panel__heading" id="challenge-panel-heading"><?= htmlspecialchars($phase['heading']) ?></h2>
    <p class="akd-challenge-panel__desc"><?= htmlspecialchars($phase['description']) ?></p>

    <?php if (!empty($entries)): ?>
        <p class="akd-challenge-panel__sub-heading">Heading into voting</p>
        <div class="akd-challenge-grid akd-challenge-grid--preview">
            <?php foreach ($entries as $entry): ?>
                <?php $votingActive = false; ?>
                <?php require __DIR__ . '/submission-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>