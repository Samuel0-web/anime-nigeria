<?php
/**
 * Non-open-state view for the Nominations page: shown when nominations
 * have not started yet, or have already closed. When nominations are
 * open, the controller renders intro.php and board.php instead.
 *
 * Expects the following to be defined by the parent view (nominations.php):
 *
 * @var array{
 *     phase: string,
 *     howItWorks: list<array{key: string, label: string, link: string, cta: string}>,
 * } $ancaOverview
 * @var array{state: string, label: string} $nominationStatus
 */

$currentPhase = $ancaOverview['phase'];
$isNotOpen    = $nominationStatus['state'] === 'not_open';

if ($isNotOpen) {
    $heading = 'Nominations have not opened yet';
    $body    = 'ANCA nominations are not open right now. Check back once they begin, or read how the award works while you wait.';
    $cta     = ['label' => 'How ANCA Works', 'link' => '/member/community/awards/overview#anca-how-it-works'];
} else {
    $redirect = akd_anca_closed_stage_redirect($ancaOverview['howItWorks'], $currentPhase);
    $heading  = 'Nominations are closed';
    $body     = 'The nomination period for ANCA has ended. ' . $redirect['label'] . ' is next.';
    $cta      = ['label' => $redirect['cta'], 'link' => $redirect['link']];
}
?>
<section class="akd-anca-nom-status" aria-labelledby="anca-nom-status-heading">
    <span class="akd-anca-status akd-anca-status--<?= htmlspecialchars($currentPhase) ?>">
        <span class="akd-anca-status__dot"></span>
        <?= htmlspecialchars($nominationStatus['label']) ?>
    </span>

    <h1 class="akd-anca-nom-status__heading" id="anca-nom-status-heading"><?= htmlspecialchars($heading) ?></h1>
    <p class="akd-anca-nom-status__desc"><?= htmlspecialchars($body) ?></p>

    <a href="<?= htmlspecialchars($cta['link']) ?>" class="akd-award-btn akd-award-btn--primary">
        <?= htmlspecialchars($cta['label']) ?>
    </a>
</section>