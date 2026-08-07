<?php
$page_title       = "Leaderboard";
$page_description = "Check your activities.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Leaderboard', 'url' => null],
];

require_once __DIR__ . '/includes/header.php';
?>

<div id="react-root"></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
