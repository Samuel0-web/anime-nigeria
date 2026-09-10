<?php
/**
 * Non-open-state view for the Voting page: shown before voting opens
 * (coming_soon or nominations phase) or after it has ended (winners
 * phase, and any later phase, since winners stays available once
 * reached per akd_anca_stage_status()).
 *
 * Expects the following to be defined by the parent view (voting.php):
 *
 * @var array{phase: string} $ancaOverview
 * @var array{state: string, label: string} $votingStatus
 */

$currentPhase = $ancaOverview['phase'];

if ($votingStatus['state'] === 'not_open') {
    $nominationsStatus = akd_anca_stage_status('nominations', $currentPhase);
    $heading = 'Voting has not opened yet';

    if ($nominationsStatus['state'] === 'open') {
        $body = 'Voting opens once nominations close. Right now, nominations are open, so put someone forward first.';
        $cta  = ['label' => 'Nominate Someone', 'link' => '/member/community/awards/nominations'];
    } else {
        $body = 'ANCA voting is not open right now. Check back once nominations have closed.';
        $cta  = ['label' => 'ANCA Overview', 'link' => '/member/community/awards/overview'];
    }
} else {
    // 'closed'. Voting has already ended (winners phase or later).
    $winnersStatus = akd_anca_stage_status('winners', $currentPhase);
    $heading = 'Voting has closed';

    if ($winnersStatus['state'] === 'available') {
        $body = "Voting for ANCA has ended. This year's winners are ready to view.";
        $cta  = ['label' => 'View Winners', 'link' => '/member/community/awards/winners'];
    } else {
        $body = 'Voting for ANCA has ended.';
        $cta  = ['label' => 'ANCA Overview', 'link' => '/member/community/awards/overview'];
    }
}
?>
<section class="akd-anca-vote-status" aria-labelledby="anca-vote-status-heading">
    <span class="akd-anca-status akd-anca-status--<?= htmlspecialchars($currentPhase) ?>">
        <span class="akd-anca-status__dot"></span>
        <?= htmlspecialchars($votingStatus['label']) ?>
    </span>

    <h1 class="akd-anca-vote-status__heading" id="anca-vote-status-heading"><?= htmlspecialchars($heading) ?></h1>
    <p class="akd-anca-vote-status__desc"><?= htmlspecialchars($body) ?></p>

    <a href="<?= htmlspecialchars($cta['link']) ?>" class="akd-award-btn akd-award-btn--primary">
        <?= htmlspecialchars($cta['label']) ?>
    </a>
</section>