<?php
$page_title       = 'Dashboard';
$page_description = 'Overview of the Anime Nigeria admin area.';

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => null],
];

$pageActions = [
    ['label' => 'Create User', 'url' => '/admin/users/create', 'primary' => true],
];

require_once __DIR__ . '/partials/header.php';
?>

<main class="akd-content">
    <?php require __DIR__ . '/partials/page-header.php'; ?>

    <div class="akd-admin-panel">
        <p>Page content goes here.</p>
    </div>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>