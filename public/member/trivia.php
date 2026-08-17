<?php
$page_title       = "Trivia";
$page_description = "Participate in trivia.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Trivia', 'url' => null],
];

require_once __DIR__ . '/includes/header.php';
?>

<div id="react-root" data-page="trivia"></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
