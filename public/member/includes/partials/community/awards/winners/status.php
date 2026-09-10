<?php
/**
 * Not-yet-available state for the Winners page: shown for the
 * coming_soon, nominations, and voting phases. Once winners become
 * available they stay available (see akd_anca_stage_status()), so
 * there is no separate branch needed for a later "completed" state.
 *
 * @var array{phase: string} $ancaOverview
 * @var array{state: string, label: string} $winnersStatus
 */

$currentPhase = $ancaOverview['phase'];

switch ($currentPhase) {
    case 'nominations':
        $heading = 'The winners will be revealed after voting';
        $body    = "Nominations are currently open. The community's winners will be revealed once voting has taken place.";
        $cta     = ['label' => 'Nominate Someone', 'link' => '/member/community/awards/nominations'];
        break;

    case 'voting':
        $heading = 'The community is still voting';
        $body    = 'Voting is underway. The winners will be revealed once voting closes.';
        $cta     = ['label' => 'Vote Now', 'link' => '/member/community/awards/voting'];
        break;

    default: // coming_soon
        $heading = 'The winners are still to come';
        $body    = 'The community awards are on their way. Winners will appear here once the voting period concludes.';
        $cta     = ['label' => 'How ANCA Works', 'link' => '/member/community/awards/overview#anca-how-it-works'];
        break;
}
?>
<section class="akd-anca-winners-status" aria-labelledby="anca-winners-status-heading">
    <span class="akd-anca-status akd-anca-status--<?= htmlspecialchars($currentPhase) ?>">
        <span class="akd-anca-status__dot"></span>
        <?= htmlspecialchars($winnersStatus['label']) ?>
    </span>

    <h1 class="akd-anca-winners-status__heading" id="anca-winners-status-heading"><?= htmlspecialchars($heading) ?></h1>
    <p class="akd-anca-winners-status__desc"><?= htmlspecialchars($body) ?></p>

    <a href="<?= htmlspecialchars($cta['link']) ?>" class="akd-award-btn akd-award-btn--primary">
        <?= htmlspecialchars($cta['label']) ?>
    </a>
</section>