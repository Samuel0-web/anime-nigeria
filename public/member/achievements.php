<?php
$page_title       = "Achievements";
$page_description = "Every achievement you've earned on Anime Nigeria.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Profile', 'url' => '/member/profile'],
    ['label' => 'Achievements', 'url' => null],
];

require_once __DIR__ . '/includes/header.php';

$achievements = require __DIR__ . '/includes/data/achievements.php';
?>

<main class="akd-content">
    <div class="akd-profile">
        <section class="akd-achievements">
            <div class="akd-section-heading">
                <div class="akd-section-heading__text">
                    <h2 class="akd-section-heading__title">Achievements</h2>
                    <p class="akd-section-heading__subtitle">Every badge you've earned on Anime Nigeria, in one place.</p>
                </div>
            </div>

            <div class="akd-achievements-grid">
                <?php foreach ($achievements as $achievement): ?>
                    <?php require __DIR__ . '/includes/partials/achievement-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>