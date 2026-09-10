<?php
$page_title       = "ANCA Winners";
$page_description = "Winners of the 2026 ANCA.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Community', 'url' => null],
    ['label' => 'Awards', 'url' => null],
    ['label' => 'Winners', 'url' => null],
];

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/data/community-awards-support.php';

$ancaOverview = require __DIR__ . '/../../includes/data/community-awards-data.php';

$winnersStatus = akd_anca_stage_status('winners', $ancaOverview['phase']);
?>

<main class="akd-content">
    <div class="akd-anca akd-award-container">
        <?php if ($winnersStatus['state'] === 'available'): ?>
            <?php require __DIR__ . '/../../includes/partials/community/awards/winners/hero.php'; ?>
            <?php require __DIR__ . '/../../includes/partials/community/awards/winners/featured.php'; ?>
            <?php require __DIR__ . '/../../includes/partials/community/awards/winners/wall.php'; ?>
            <?php require __DIR__ . '/../../includes/partials/community/awards/winners/closing.php'; ?>
        <?php else: ?>
            <?php require __DIR__ . '/../../includes/partials/community/awards/winners/status.php'; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>