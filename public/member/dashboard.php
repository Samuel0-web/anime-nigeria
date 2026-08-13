<?php
$page_title       = "Dashboard";
$page_description = "Check your activities.";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/data/dashboard-data.php';
?>

<main class="akd-content">
    <div class="akd-dash">

        <?php require __DIR__ . '/includes/partials/dashboard/welcome.php'; ?>

        <?php require __DIR__ . '/includes/partials/dashboard/event-rail.php'; ?>

        <div class="akd-dash__pair-row">
            <?php require __DIR__ . '/includes/partials/dashboard/notifications-preview.php'; ?>
            <?php require __DIR__ . '/includes/partials/dashboard/progress.php'; ?>
        </div>

        <div class="akd-dash__pair-row">
            <?php require __DIR__ . '/includes/partials/dashboard/achievements-preview.php'; ?>
            <?php require __DIR__ . '/includes/partials/dashboard/announcements-preview.php'; ?>
        </div>

        <?php require __DIR__ . '/includes/partials/dashboard/major-event-banner.php'; ?>

        <?php require __DIR__ . '/includes/partials/dashboard/quick-actions.php'; ?>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>