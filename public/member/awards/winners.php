<?php
$page_title       = "ANAA Winners";
$page_description = "The winners of the Anime Nigeria Anime Awards.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Awards', 'url' => '/member/awards'],
    ['label' => 'Winners', 'url' => null],
];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/data/awards-support.php';

$awardsOverview = require __DIR__ . '/../includes/data/awards-data.php';
$winnersPage    = akd_award_winners_page_config($awardsOverview['phases'], $awardsOverview['phase']);
?>

<main class="akd-content">
    <div class="akd-anaa akd-award-container">
        <?php require __DIR__ . '/../includes/partials/awards/winners/hero.php'; ?>

        <?php if (!empty($winnersPage['revealWinners'])): ?>
            <?php require __DIR__ . '/../includes/partials/awards/winners/featured.php'; ?>
            <?php require __DIR__ . '/../includes/partials/awards/winners/grid.php'; ?>
        <?php else: ?>
            <?php require __DIR__ . '/../includes/partials/awards/winners/notice.php'; ?>
        <?php endif; ?>

        <?php require __DIR__ . '/../includes/partials/awards/winners/celebration.php'; ?>
        <?php require __DIR__ . '/../includes/partials/awards/winners/previous.php'; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>