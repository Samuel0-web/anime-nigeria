<?php
/**
 * Expects the following to be defined by the parent view (nominations.php):
 *
 * @var array{
 *     eligibility: array{
 *         start: string,
 *         end: string,
 *         rules: array<string, array{label: string, items: list<string>}>,
 *     },
 * } $awardsOverview
 */

$eligibility = $awardsOverview['eligibility'];
$periodStart = akd_award_format_date($eligibility['start']);
$periodEnd   = akd_award_format_date($eligibility['end']);
?>
<section class="akd-award-section akd-award-eligibility" aria-labelledby="anaa-eligibility-heading">
    <header class="akd-award-section__header">
        <div class="akd-award-section__heading-group">
            <p class="akd-award-section__eyebrow">Before You Nominate</p>
            <h2 class="akd-award-section__title" id="anaa-eligibility-heading">Eligibility</h2>
            <p class="akd-award-section__desc">
                ANAA 2026 covers anime, characters and moments from <?= htmlspecialchars($periodStart) ?> to <?= htmlspecialchars($periodEnd) ?>.
            </p>
        </div>
    </header>

    <div class="akd-award-eligibility__grid">
        <?php foreach ($eligibility['rules'] as $group): ?>
            <div class="akd-award-eligibility__card">
                <h3 class="akd-award-eligibility__label"><?= htmlspecialchars($group['label']) ?></h3>
                <ul class="akd-award-eligibility__list">
                    <?php foreach ($group['items'] as $item): ?>
                        <li><?= htmlspecialchars($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
</section>