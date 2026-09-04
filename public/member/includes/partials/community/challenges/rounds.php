<?php

/**
 * Expects the following to be defined by the parent view (challenges.php):
 *
 * @var array{
 *     rounds: list<array{id: string, name: string, participantCount: int, advancingCount: int}>,
 * } $challenge
 * @var string $displayRoundId
 */
?>
<section class="akd-challenge-rounds" aria-label="Round progression">
    <ol class="akd-challenge-rounds__list">
        <?php foreach ($challenge['rounds'] as $round): ?>
            <?php $status = akd_challenge_round_status($round, $displayRoundId, $challenge['rounds']); ?>
            <li class="akd-challenge-rounds__item akd-challenge-rounds__item--<?= htmlspecialchars($status) ?>">
                <span class="akd-challenge-rounds__name"><?= htmlspecialchars($round['name']) ?></span>
                <span class="akd-challenge-rounds__count">
                    <?= htmlspecialchars((string) $round['participantCount']) ?> submissions
                    <?php if ($status !== 'upcoming'): ?>
                        &bull; <?= htmlspecialchars((string) $round['advancingCount']) ?> advance
                    <?php endif; ?>
                </span>
            </li>
        <?php endforeach; ?>
    </ol>
</section>