<?php
$page_title       = "ANAA Voting";
$page_description = "Cast your vote for the 2026 ANAA.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Awards', 'url' => '/member/awards'],
    ['label' => 'Voting', 'url' => null],
];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/data/awards-support.php';

$awardsOverview = require __DIR__ . '/../includes/data/awards-data.php';
$votingPage     = akd_award_voting_page_config($awardsOverview['phases'], $awardsOverview['phase']);
?>

<main class="akd-content">
    <div class="akd-anaa akd-award-container<?= !empty($votingPage['formActive']) ? ' akd-award-container--voting' : '' ?>">
        <?php if (!empty($votingPage['formActive'])): ?>
            <?php require __DIR__ . '/../includes/partials/awards/voting/experience.php'; ?>
        <?php else: ?>
            <?php require __DIR__ . '/../includes/partials/awards/voting/closed.php'; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>