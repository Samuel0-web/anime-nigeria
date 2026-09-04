<?php

/**
 * Expects the following to be defined by the parent view (challenges.php):
 *
 * @var array{
 *     phase: string,
 *     timelineSteps: list<array{key: string, label: string}>,
 * } $challenge
 */

$phaseKey = $challenge['phase'];
$steps    = $challenge['timelineSteps'];

$stateMeta = [
    'closed'    => ['label' => 'Closed',      'icon' => 'check'],
    'open'      => ['label' => 'Open',        'icon' => 'dot'],
    'available' => ['label' => 'Available',   'icon' => 'trophy'],
    'upcoming'  => ['label' => 'Coming Soon', 'icon' => 'ring'],
];
?>
<section class="akd-challenge-timeline" aria-label="Challenge timeline">
    <ol class="akd-challenge-timeline__list">
        <?php foreach ($steps as $step): ?>
            <?php
            $state = akd_challenge_timeline_state($step['key'], $phaseKey);
            $meta  = $stateMeta[$state];
            ?>
            <li class="akd-challenge-timeline__item akd-challenge-timeline__item--<?= htmlspecialchars($state) ?>">
                <span class="akd-challenge-timeline__marker" aria-hidden="true">
                    <?php if ($meta['icon'] === 'check'): ?>
                        <i class="fa-solid fa-check"></i>
                    <?php elseif ($meta['icon'] === 'trophy'): ?>
                        <i class="fa-solid fa-trophy"></i>
                    <?php elseif ($meta['icon'] === 'dot'): ?>
                        <span class="akd-challenge-timeline__pulse"></span>
                    <?php else: ?>
                        <span class="akd-challenge-timeline__ring"></span>
                    <?php endif; ?>
                </span>
                <span class="akd-challenge-timeline__label"><?= htmlspecialchars($step['label']) ?></span>
                <span class="akd-challenge-timeline__state"><?= htmlspecialchars($meta['label']) ?></span>
            </li>
        <?php endforeach; ?>
    </ol>
</section>