<?php
/**
 * Expects the following to be defined by the parent view (overview.php):
 *
 * @var array{
 *     phase: string,
 *     howItWorks: list<array{key: string, label: string, link: string, cta: string}>,
 *     honouredOnesLink: string,
 * } $ancaOverview
 *
 * Reuses 'howItWorks' rather than defining a second, parallel list of
 * stages and links, so there is one place the nominations/voting/
 * winners routes and state logic live, not two.
 */

$currentPhase = $ancaOverview['phase'];
$stages       = $ancaOverview['howItWorks'];
?>
<section class="akd-anca-section akd-anca-entry" aria-labelledby="anca-entry-heading">
    <header class="akd-anca-section__header">
        <p class="akd-anca-section__eyebrow">Get Involved</p>
        <h2 class="akd-anca-section__title" id="anca-entry-heading">Where to go next</h2>
    </header>

    <div class="akd-anca-entry__grid">
        <?php foreach ($stages as $stage): ?>
            <?php $status = akd_anca_stage_status($stage['key'], $currentPhase); ?>
            <?php if (in_array($status['state'], ['open', 'available'], true)): ?>
                <a href="<?= htmlspecialchars($stage['link']) ?>" class="akd-anca-entry__card">
                    <span class="akd-anca-entry__status akd-anca-entry__status--active"><?= htmlspecialchars($status['label']) ?></span>
                    <span class="akd-anca-entry__label"><?= htmlspecialchars($stage['label']) ?></span>
                    <span class="akd-anca-entry__link"><?= htmlspecialchars($stage['cta']) ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                </a>
            <?php else: ?>
                <div class="akd-anca-entry__card akd-anca-entry__card--disabled" aria-disabled="true">
                    <span class="akd-anca-entry__status"><?= htmlspecialchars($status['label']) ?></span>
                    <span class="akd-anca-entry__label"><?= htmlspecialchars($stage['label']) ?></span>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <a href="<?= htmlspecialchars($ancaOverview['honouredOnesLink']) ?>" class="akd-anca-honoured-link">
        Looking for past winners? Visit Honoured Ones <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
    </a>
</section>