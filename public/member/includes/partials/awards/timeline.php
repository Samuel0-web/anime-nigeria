<?php
/**
 * Expects the following to be defined by the parent view (awards.php):
 *
 * @var array{
 *     phase: string,
 *     timelineSteps: list<array{key: string, label: string}>,
 * } $awardsOverview
 */

$phaseKey = $awardsOverview['phase'];
$steps    = $awardsOverview['timelineSteps'];

$stateMeta = [
    'closed'    => ['label' => 'Closed',      'icon' => 'check'],
    'open'      => ['label' => 'Open',        'icon' => 'dot'],
    'available' => ['label' => 'Available',   'icon' => 'trophy'],
    'upcoming'  => ['label' => 'Coming Soon', 'icon' => 'ring'],
];
?>
<section class="akd-award-timeline" aria-label="Award season timeline">
    <ol class="akd-award-timeline__list">
        <?php foreach ($steps as $step): ?>
            <?php
            $state = akd_award_timeline_state($step['key'], $phaseKey);
            $meta  = $stateMeta[$state];
            ?>
            <li class="akd-award-timeline__item akd-award-timeline__item--<?= htmlspecialchars($state) ?>">
                <span class="akd-award-timeline__marker" aria-hidden="true">
                    <?php if ($meta['icon'] === 'check'): ?>
                        <i class="fa-solid fa-check"></i>
                    <?php elseif ($meta['icon'] === 'trophy'): ?>
                        <i class="fa-solid fa-trophy"></i>
                    <?php elseif ($meta['icon'] === 'dot'): ?>
                        <span class="akd-award-timeline__pulse"></span>
                    <?php else: ?>
                        <span class="akd-award-timeline__ring"></span>
                    <?php endif; ?>
                </span>
                <span class="akd-award-timeline__label"><?= htmlspecialchars($step['label']) ?></span>
                <span class="akd-award-timeline__state"><?= htmlspecialchars($meta['label']) ?></span>
            </li>
        <?php endforeach; ?>
    </ol>
</section>