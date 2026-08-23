<?php
$page_title       = "Anime Awards";
$page_description = "The 2026 ANAA. Chosen by the Anime Nigeria community.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Awards', 'url' => null],
    ['label' => 'Overview', 'url' => null],
];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/data/awards-support.php';

$awardsOverview = require __DIR__ . '/../includes/data/awards-data.php';
?>

<main class="akd-content">
    <div class="akd-anaa akd-award-container">
        <?php require __DIR__ . '/../includes/partials/awards/hero.php'; ?>
        <?php require __DIR__ . '/../includes/partials/awards/timeline.php'; ?>
        <?php require __DIR__ . '/../includes/partials/awards/categories.php'; ?>
        <?php require __DIR__ . '/../includes/partials/awards/nominees-rail.php'; ?>
        <?php require __DIR__ . '/../includes/partials/awards/voting-cta.php'; ?>
        <?php require __DIR__ . '/../includes/partials/awards/winners.php'; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>