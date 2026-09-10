<?php
$page_title       = "The Honoured Ones";
$page_description = "A record of the people recognised by the Anime Nigeria community.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Community', 'url' => null],
    ['label' => 'Honoured Ones', 'url' => null],
];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/data/honoured-ones-support.php';

$editions = akd_honoured_ones_editions();
?>

<main class="akd-content">
    <div class="akd-anca akd-award-container">
        <?php require __DIR__ . '/../includes/partials/community/honoured-ones/hero.php'; ?>
        <?php require __DIR__ . '/../includes/partials/community/honoured-ones/intro.php'; ?>

        <?php if (empty($editions)): ?>
            <?php require __DIR__ . '/../includes/partials/community/honoured-ones/empty.php'; ?>
        <?php else: ?>
            <?php require __DIR__ . '/../includes/partials/community/honoured-ones/archive.php'; ?>
        <?php endif; ?>

        <?php require __DIR__ . '/../includes/partials/community/honoured-ones/closing.php'; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>