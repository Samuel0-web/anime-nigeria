<?php
$page_title       = "ANCA Nominations";
$page_description = "Nominate your favourites for the 2026 ANCA.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Community', 'url' => null],
    ['label' => 'Awards', 'url' => null],
    ['label' => 'Nominations', 'url' => null],
];

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/data/community-awards-support.php';

$ancaOverview = require __DIR__ . '/../../includes/data/community-awards-data.php';

$nominationStatus  = akd_anca_stage_status('nominations', $ancaOverview['phase']);
$nominationMembers = $nominationStatus['state'] === 'open' ? akd_anca_nomination_members() : [];
?>

<main class="akd-content">
    <div class="akd-anca akd-award-container">
        <?php if ($nominationStatus['state'] === 'open'): ?>
            <?php require __DIR__ . '/../../includes/partials/community/awards/nominations/intro.php'; ?>
            <?php require __DIR__ . '/../../includes/partials/community/awards/nominations/board.php'; ?>
        <?php else: ?>
            <?php require __DIR__ . '/../../includes/partials/community/awards/nominations/status.php'; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>