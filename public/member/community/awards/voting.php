<?php
$page_title       = "ANCA Voting";
$page_description = "Cast your vote for the 2026 ANCA.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Community', 'url' => null],
    ['label' => 'Awards', 'url' => null],
    ['label' => 'Voting', 'url' => null],
];

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/data/community-awards-support.php';

$ancaOverview = require __DIR__ . '/../../includes/data/community-awards-data.php';

$votingStatus = akd_anca_stage_status('voting', $ancaOverview['phase']);
?>

<main class="akd-content">
    <div class="akd-anca akd-award-container">
        <?php if ($votingStatus['state'] === 'open'): ?>
            <?php require __DIR__ . '/../../includes/partials/community/awards/voting/experience.php'; ?>
        <?php else: ?>
            <?php require __DIR__ . '/../../includes/partials/community/awards/voting/status.php'; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>