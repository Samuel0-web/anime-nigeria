<?php
$page_title       = "ANCA Overview";
$page_description = "The 2026 ANCA. For the Anime Nigeria community";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Community', 'url' => null],
    ['label' => 'Awards', 'url' => null],
    ['label' => 'Overview', 'url' => null],
];

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/data/community-awards-support.php';

$ancaOverview = require __DIR__ . '/../../includes/data/community-awards-data.php';
?>

<main class="akd-content">
    <div class="akd-anca akd-award-container">
        <?php require __DIR__ . '/../../includes/partials/community/awards/overview/hero.php'; ?>
        <?php require __DIR__ . '/../../includes/partials/community/awards/overview/philosophy.php'; ?>
        <?php require __DIR__ . '/../../includes/partials/community/awards/overview/categories.php'; ?>
        <?php require __DIR__ . '/../../includes/partials/community/awards/overview/how-it-works.php'; ?>
        <?php require __DIR__ . '/../../includes/partials/community/awards/overview/dates.php'; ?>
        <?php require __DIR__ . '/../../includes/partials/community/awards/overview/cta.php'; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>