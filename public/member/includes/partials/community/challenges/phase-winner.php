<?php

/**
 * Handles both the 'winner' and 'completed' phases. 'completed' adds
 * a finalist recap under the same winner reveal.
 *
 * Expects the following to be defined by the parent view (challenges.php):
 *
 * @var array{
 *     phases: array<string, array<string, mixed>>,
 *     phase: string,
 *     title: string,
 *     finalists: list<string>,
 *     entries: list<array<string, mixed>>,
 * } $challenge
 */

$phase       = akd_challenge_phase_config($challenge['phases'], $challenge['phase']);
$winner      = akd_challenge_winner($challenge);
$isCompleted = $challenge['phase'] === 'completed';
$mediaKind   = akd_challenge_media_config($challenge['type'])['kind'];
?>
<section class="akd-challenge-panel akd-challenge-panel--winner" aria-labelledby="challenge-panel-heading">
    <p class="akd-challenge-panel__eyebrow"><?= htmlspecialchars($challenge['title']) ?></p>
    <h2 class="akd-challenge-panel__heading akd-challenge-panel__heading--winner" id="challenge-panel-heading">
        <?= htmlspecialchars($phase['heading']) ?>
    </h2>

    <?php if ($winner !== null): ?>
        <?php $entry = $winner['entry']; ?>
        <div class="akd-challenge-winner-card">
            <?php if ($mediaKind === 'image'): ?>
                <img src="<?= htmlspecialchars($entry['file']) ?>" alt="<?= htmlspecialchars($entry['description']) ?>"
                    class="akd-challenge-winner-card__image"
                >
            <?php else: ?>
                <div class="akd-challenge-winner-card__media akd-challenge-winner-card__media--audio">
                    <audio controls preload="none">
                        <source src="<?= htmlspecialchars($entry['file']) ?>">
                        Your browser does not support the audio element.
                    </audio>
                </div>
            <?php endif; ?>

            <div class="akd-challenge-winner-card__body">
                <i class="fa-solid fa-trophy akd-challenge-winner-card__icon" aria-hidden="true"></i>
                <h3 class="akd-challenge-winner-card__title"><?= htmlspecialchars($entry['description']) ?></h3>
                <p class="akd-challenge-winner-card__by">by <?= htmlspecialchars(akd_challenge_submission_caption($entry)) ?></p>

                <?php if (!empty($winner['votes'])): ?>
                    <p class="akd-challenge-winner-card__votes"><?= number_format($winner['votes']) ?> votes</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($isCompleted && !empty($challenge['finalists'])): ?>
        <p class="akd-challenge-panel__sub-heading">Finalists</p>
        <div class="akd-challenge-grid akd-challenge-grid--preview">
            <?php foreach ($challenge['finalists'] as $finalistId): ?>
                <?php $entry = akd_challenge_entry_by_id($challenge['entries'], $finalistId); ?>
                <?php if ($entry === null): ?>
                    <?php continue; ?>
                <?php endif; ?>
                <?php $votingActive = false; ?>
                <?php require __DIR__ . '/submission-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>