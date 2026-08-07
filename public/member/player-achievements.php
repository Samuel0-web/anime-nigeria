<?php
require_once __DIR__ . '/includes/data/players.php';

$viewedUsername = trim($username ?? '');
$player = $viewedUsername !== '' ? akdFindPlayerByUsername($viewedUsername) : null;

if (!$player) {
    http_response_code(404);
    $page_title       = "Player Not Found";
    $page_description = "This player couldn't be found.";
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => '/dashboard'],
        ['label' => 'Leaderboard', 'url' => '/member/leaderboard'],
        ['label' => 'Player', 'url' => null],
        ['label' => 'Player Not Found', 'url' => null],
    ];

    require_once __DIR__ . '/includes/header.php';
    ?>
    <main class="akd-content">
        <div class="akd-profile">
            <section class="akd-empty-state">
                <i class="fa-solid fa-user-slash akd-empty-state__icon"></i>
                <h2 class="akd-empty-state__title">Player not found</h2>
                <p class="akd-empty-state__desc">We couldn't find a member with that username.</p>
                <a href="/member/leaderboard" class="akd-btn akd-btn--primary">Back to Leaderboard</a>
            </section>
        </div>
    </main>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$profile = akdBuildPlayerProfile($player);

$page_title       = "{$profile['fullname']}'s Achievements";
$page_description = "Every achievement @{$profile['username']} has earned.";

$playerUrl = '/member/player/' . rawurlencode($profile['username']);

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Leaderboard', 'url' => '/member/leaderboard'],
    ['label' => 'Player', 'url' => null],
    ['label' => $profile['fullname'], 'url' => $playerUrl],
    ['label' => 'Achievements', 'url' => null],
];

require_once __DIR__ . '/includes/header.php';
?>

<main class="akd-content">
    <div class="akd-profile">
        <section class="akd-achievements">
            <div class="akd-section-heading">
                <div class="akd-section-heading__text">
                    <h2 class="akd-section-heading__title">Achievements</h2>
                    <p class="akd-section-heading__subtitle">Every badge <?= htmlspecialchars($profile['fullname']) ?> has earned on Anime Nigeria.</p>
                </div>
                <a href="<?= htmlspecialchars($playerUrl) ?>" class="akd-section-heading__action"><i class="fa-solid fa-arrow-left"></i> Back to profile</a>
            </div>

            <div class="akd-achievements-grid">
                <?php foreach ($profile['badges'] as $achievement): ?>
                    <?php require __DIR__ . '/includes/partials/achievement-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>