<?php
$page_title       = "Help Centre";
$page_description = "Get help and support.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Help Centre', 'url' => null],
];

require_once __DIR__ . '/includes/header.php';
?>

<main class="akd-content">
    <div id="react-root"></div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
