<?php
$page_title       = "ANAA Nominations";
$page_description = "Nominate your favourites for the 2026 ANAA.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Awards', 'url' => '/member/awards'],
    ['label' => 'Nominations', 'url' => null],
];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/data/awards-support.php';

$awardsOverview  = require __DIR__ . '/../includes/data/awards-data.php';
$nominationsPage = akd_award_nominations_page_config($awardsOverview['phases'], $awardsOverview['phase']);
?>

<main class="akd-content">
    <div class="akd-anaa akd-award-container">
        <?php require __DIR__ . '/../includes/partials/awards/nominations/status.php'; ?>
        <?php require __DIR__ . '/../includes/partials/awards/nominations/eligibility.php'; ?>
        <?php if (!empty($nominationsPage['formActive'])): ?>
            <?php require __DIR__ . '/../includes/partials/awards/nominations/workspace.php'; ?>
        <?php else: ?>
            <?php require __DIR__ . '/../includes/partials/awards/nominations/categories-preview.php'; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>