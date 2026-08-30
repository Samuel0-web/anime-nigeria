<?php
$page_title       = 'Announcements';
$page_description = "What's happening across Anime Nigeria.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Announcements', 'url' => null],
];

require_once __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/data/announcements-support.php';
require __DIR__ . '/includes/data/announcements-data.php';

/** @var array<int, array{id:string,category:string,accent:string,date:string,title:string,excerpt:string,cta:string,url:string,featured:bool}> $announcements */
?>

<main class="akd-content">
    <div class="akd-announce">
        <?php if (empty($announcements)): ?>
            <?php require __DIR__ . '/includes/partials/announcements/empty.php'; ?>
        <?php else: ?>
            <?php require __DIR__ . '/includes/partials/announcements/filter.php'; ?>

            <ul class="akd-announce-group" role="list" data-announce-group>
                <?php foreach ($announcements as $item): ?>
                    <?php $slug = akd_announce_slug($item['category']); ?>
                    <?php require __DIR__ . '/includes/partials/announcements/row.php'; ?>
                <?php endforeach; ?>
            </ul>

            <div class="akd-announce-empty" hidden data-announce-empty>
                <div class="akd-announce-empty__icon" aria-hidden="true">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
                <h2 class="akd-announce-empty__title">No announcements here</h2>
                <p class="akd-announce-empty__text">There are no announcements in this category yet.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>