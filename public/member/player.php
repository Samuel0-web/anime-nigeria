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

$page_title       = $profile['fullname'];
$page_description = "Viewing @{$profile['username']}'s public profile.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Leaderboard', 'url' => '/member/leaderboard'],
    ['label' => 'Player', 'url' => null],
    ['label' => $profile['fullname'], 'url' => null],
];

require_once __DIR__ . '/includes/header.php';

$nameParts      = explode(' ', $profile['fullname']);
$initial1       = substr($nameParts[0] ?? 'U', 0, 1);
$initial2       = isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : '';
$playerInitials = strtoupper($initial1 . $initial2);
$avatarColor    = akdPlayerAvatarColor($profile['username']);
$achievementPreview  = array_slice($profile['badges'], 0, 6);
$hasMoreAchievements = count($profile['badges']) > 6;
$playerUrl = '/member/player/' . rawurlencode($profile['username']);
?>

<main class="akd-content">
    <div class="akd-profile">
        <section class="akd-player-header">
            <div class="akd-avatar-ring">
                <?php if ($profile['avatar']): ?>
                    <img src="<?= htmlspecialchars($profile['avatar']) ?>" alt="<?= htmlspecialchars($profile['fullname']) ?>" class="akd-avatar-ring__image">
                <?php else: ?>
                    <div class="akd-avatar-ring__image akd-avatar-ring__image--initials" style="background-color: <?= htmlspecialchars($avatarColor) ?>"><?= htmlspecialchars($playerInitials) ?></div>
                <?php endif; ?>
            </div>

            <div class="akd-player-header__info">
                <div class="akd-player-header__name-row">
                    <h1 class="akd-player-header__name"><?= htmlspecialchars($profile['fullname']) ?></h1>
                    <span class="akd-role-badge akd-role-badge--member">#<?= (int) $profile['rank'] ?></span>
                </div>

                <span class="akd-player-header__username">@<?= htmlspecialchars($profile['username']) ?></span>

                <div class="akd-player-header__chips">
                    <div class="akd-chip">
                        <i class="fa-solid fa-calendar"></i>
                        <span>Joined <?= htmlspecialchars($profile['joinedDate']) ?></span>
                    </div>

                    <div class="akd-chip">
                        <i class="fa-solid fa-ranking-star"></i>
                        <span>Rank #<?= (int) $profile['rank'] ?></span>
                    </div>

                    <div class="akd-chip">
                        <i class="fa-solid fa-clock"></i>
                        <span>Active <?= htmlspecialchars($profile['lastActive']) ?></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="akd-player-identity">
            <div class="akd-player-identity__stat">
                <span class="akd-player-identity__label">Level</span>
                <span class="akd-player-identity__value"><?= (int) $profile['level'] ?></span>
            </div>
            <div class="akd-player-identity__stat">
                <span class="akd-player-identity__label">Brain Cells (BC)</span>
                <span class="akd-player-identity__value"><?= number_format($profile['xp']) ?></span>
            </div>
            <div class="akd-player-identity__stat">
                <span class="akd-player-identity__label">Current Rank</span>
                <span class="akd-player-identity__value">#<?= (int) $profile['rank'] ?></span>
            </div>
            <div class="akd-player-identity__stat">
                <span class="akd-player-identity__label">Peak Rank</span>
                <span class="akd-player-identity__value">#<?= (int) $profile['highestRank'] ?></span>
            </div>
        </section>

        <section class="akd-achievements">
            <div class="akd-section-heading">
                <div class="akd-section-heading__text">
                    <h2 class="akd-section-heading__title">Achievements</h2>
                    <p class="akd-section-heading__subtitle"><?= htmlspecialchars($profile['fullname']) ?>'s earned badges.</p>
                </div>
                <?php if ($hasMoreAchievements): ?>
                    <a href="<?= htmlspecialchars($playerUrl . '/achievements') ?>" class="akd-section-heading__action">See all <i class="fa-solid fa-arrow-right"></i></a>
                <?php endif; ?>
            </div>

            <div class="akd-achievements-grid">
                <?php foreach ($achievementPreview as $achievement): ?>
                    <?php require __DIR__ . '/includes/partials/achievement-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Awards Earned -->
        <section class="akd-player-awards">
            <div class="akd-section-heading">
                <div class="akd-section-heading__text">
                    <h2 class="akd-section-heading__title">Awards Earned</h2>
                    <p class="akd-section-heading__subtitle">Recognition from the Anime Nigeria Awards.</p>
                </div>
            </div>

            <?php if (empty($profile['awards'])): ?>
                <p class="akd-awards-empty">No awards earned yet.</p>
            <?php else: ?>
                <div class="akd-awards-list">
                    <?php foreach ($profile['awards'] as $award): ?>
                        <span class="akd-award-chip">
                            <i class="<?= htmlspecialchars($award['icon']) ?>"></i>
                            <?= htmlspecialchars($award['name']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="akd-profile-stats">
            <div class="akd-section-heading">
                <div class="akd-section-heading__text">
                    <h2 class="akd-section-heading__title">Community Statistics</h2>
                </div>
            </div>

            <div class="akd-stats-grid">
                <div class="akd-stat-card">
                    <i class="fa-solid fa-check-square akd-stat-card__icon" aria-hidden="true"></i>
                    <div class="akd-stat-card__body">
                        <span class="akd-stat-card__value"><?= (int) $profile['votesCast'] ?></span>
                        <span class="akd-stat-card__label">Votes Cast</span>
                    </div>
                </div>

                <div class="akd-stat-card">
                    <i class="fa-solid fa-comments akd-stat-card__icon" aria-hidden="true"></i>
                    <div class="akd-stat-card__body">
                        <span class="akd-stat-card__value"><?= (int) $profile['comments'] ?></span>
                        <span class="akd-stat-card__label">Comments</span>
                    </div>
                </div>

                <div class="akd-stat-card">
                    <i class="fa-solid fa-images akd-stat-card__icon" aria-hidden="true"></i>
                    <div class="akd-stat-card__body">
                        <span class="akd-stat-card__value"><?= (int) $profile['galleryPosts'] ?></span>
                        <span class="akd-stat-card__label">Gallery Posts</span>
                    </div>
                </div>

                <div class="akd-stat-card">
                    <i class="fa-solid fa-flag-checkered akd-stat-card__icon" aria-hidden="true"></i>
                    <div class="akd-stat-card__body">
                        <span class="akd-stat-card__value"><?= (int) $profile['challengesJoined'] ?></span>
                        <span class="akd-stat-card__label">Challenges Joined</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Trivia Statistics -->
        <section class="akd-profile-stats">
            <div class="akd-section-heading">
                <div class="akd-section-heading__text">
                    <h2 class="akd-section-heading__title">Trivia Statistics</h2>
                </div>
            </div>

            <div class="akd-stats-grid">
                <div class="akd-stat-card">
                    <i class="fa-solid fa-brain akd-stat-card__icon" aria-hidden="true"></i>
                    <div class="akd-stat-card__body">
                        <span class="akd-stat-card__value"><?= (int) $profile['triviaCompleted'] ?></span>
                        <span class="akd-stat-card__label">Trivia Completed</span>
                    </div>
                </div>

                <div class="akd-stat-card">
                    <i class="fa-solid fa-crown akd-stat-card__icon" aria-hidden="true"></i>
                    <div class="akd-stat-card__body">
                        <span class="akd-stat-card__value"><?= (int) $profile['triviaWins'] ?></span>
                        <span class="akd-stat-card__label">Trivia Wins</span>
                    </div>
                </div>

                <div class="akd-stat-card">
                    <i class="fa-solid fa-percent akd-stat-card__icon" aria-hidden="true"></i>
                    <div class="akd-stat-card__body">
                        <span class="akd-stat-card__value"><?= (int) $profile['winRatePercent'] ?>%</span>
                        <span class="akd-stat-card__label">Win Rate</span>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>