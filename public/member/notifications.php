<?php
require_once __DIR__ . '/includes/data/notifications-data.php';
require_once __DIR__ . '/includes/data/notifications-support.php';

$page_title       = "Notifications";
$page_description = "Stay updated with the latest news and updates from the Anime Nigeria community.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Notifications', 'url' => null],
];

require_once __DIR__ . '/includes/header.php';
?>

<main class="akd-content">
    <div class="akd-notifications">
        <div class="akd-notify" data-notify-page>
            <div class="akd-notify__intro">
                <h2 class="akd-notify__heading">Notifications</h2>
                <p class="akd-notify__subheading">Stay updated with the latest news and updates from the Anime Nigeria community.</p>
            </div>

            <?php require __DIR__ . '/includes/partials/notifications/filter.php'; ?>

            <ul class="akd-notify-group" role="list" data-notify-group<?= empty($notifications) ? ' hidden' : '' ?>>
                <?php foreach ($notifications as $item): ?>
                    <?php $slug = akd_notify_slug($item['category']); ?>
                    <?php require __DIR__ . '/includes/partials/notifications/row.php'; ?>
                <?php endforeach; ?>
            </ul>

            <?php require __DIR__ . '/includes/partials/notifications/empty.php'; ?>
        </div>

        <div class="akd-notify-bulkbar" data-notify-bulkbar>
            <span class="akd-notify-bulkbar__count" data-notify-bulk-count>0 selected</span>
            <div class="akd-notify-bulkbar__actions">
                <button type="button" class="akd-notify-bulkbar__clear" data-notify-clear-selection>Clear</button>
                <button type="button" class="akd-notify-bulkbar__delete" data-notify-bulk-delete>Delete</button>
            </div>
        </div>
    </div>
</main>