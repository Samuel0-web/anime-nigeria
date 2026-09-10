<?php
/**
 * Compact, secondary date strip. Not a timeline: just labelled values,
 * matching how the rest of the Overview page treats season state as
 * supporting information rather than the main structure.
 *
 * Expects the following to be defined by the parent view (overview.php):
 *
 * @var array{
 *     dates: array{nominations_open: string, nominations_close: string, voting_open: string, voting_close: string, winners_announced: string},
 * } $ancaOverview
 */

$dates = $ancaOverview['dates'];

$items = [
    ['label' => 'Nominations open',  'value' => $dates['nominations_open']],
    ['label' => 'Nominations close', 'value' => $dates['nominations_close']],
    ['label' => 'Voting opens',      'value' => $dates['voting_open']],
    ['label' => 'Voting closes',     'value' => $dates['voting_close']],
    ['label' => 'Winners announced', 'value' => $dates['winners_announced']],
];
?>
<section class="akd-anca-dates" aria-label="Important ANCA 2026 dates">
    <?php foreach ($items as $item): ?>
        <div class="akd-anca-dates__item">
            <span class="akd-anca-dates__label"><?= htmlspecialchars($item['label']) ?></span>
            <span class="akd-anca-dates__value"><?= htmlspecialchars(akd_anca_format_date($item['value'])) ?></span>
        </div>
    <?php endforeach; ?>
</section>