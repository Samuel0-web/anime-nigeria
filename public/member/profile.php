<?php
$page_title       = "Profile";
$page_description = "Your personal space on Anime Nigeria.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Profile', 'url' => null],
];

require_once __DIR__ . '/includes/header.php';

// ---- Real data, from $user (already loaded in header.php) ----
$fullname    = $user['fullname'] ?? 'Member';
$username    = $user['username'] ?? 'member';
$email       = $user['email'] ?? '';
$role        = $user['role'] ?? 'member';
$isVerified  = !empty($user['email_verified_at']);
$isGoogle    = ($user['auth_provider'] ?? 'local') === 'google';
$memberSince = !empty($user['created_at']) ? date('M Y', strtotime($user['created_at'])) : '—';
$avatarUrl   = $user['avatar'] ?? null;

$nameParts    = explode(' ', $fullname);
$initial1     = substr($nameParts[0] ?? 'U', 0, 1);
$initial2     = isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : '';
$userInitials = strtoupper($initial1 . $initial2);

/**
 * Deterministic solid background color for initials avatars.
 * Same seed always resolves to the same color. Solid fills only —
 * no gradients, no near-black/near-white — so white text always reads clearly.
 */
function akdAvatarColor(string $seed): string
{
    static $palette = [
        '#3457D5', '#0F9B8E', '#1E8E5A', '#C23B5D',
        '#5B4B8A', '#8B5FBF', '#D97B29', '#C2447A',
    ];

    $hash = crc32($seed);
    return $palette[$hash % count($palette)];
}

$avatarColor = akdAvatarColor($username !== '' ? $username : $fullname);

// ---- Hardcoded — no backing columns yet, kept as named vars for easy backend swap ----
$level       = 12;
$levelTitle  = 'Rising Member';
$xpCurrent   = 3450;
$xpToNext    = 5000;
$xpRemaining = $xpToNext - $xpCurrent;
$xpProgress  = (int) round(($xpCurrent / $xpToNext) * 100);

$rankLabel   = 'Top 5%';
$rankCaption = 'Higher than 95% of members';

$stats = [
    ['label' => 'Votes Cast',       'value' => 128, 'icon' => 'fa-solid fa-check-square', 'trend' => '+12 this month'],
    ['label' => 'Trivia Completed', 'value' => 34,  'icon' => 'fa-solid fa-brain',        'trend' => null],
    ['label' => 'Trivia Wins',      'value' => 21,  'icon' => 'fa-solid fa-crown',        'trend' => null],
    ['label' => 'Badges Earned',    'value' => 9,   'icon' => 'fa-solid fa-medal',        'trend' => null],
];

// ---- SECTION 3 — Badges (hardcoded placeholder data) ----
// ---- SECTION 3 — Achievements (shared with /member/achievements.php) ----
$achievements = require __DIR__ . '/includes/data/achievements.php';
$achievementsPreview = array_slice($achievements, -8); // 8 most recent — fixed count on every screen size

// ---- SECTION 4 — Activity Timeline (hardcoded, except the join date which is real) ----
$activityGroups = [
    'Today' => [
        ['icon' => 'fa-solid fa-crown',         'title' => 'Won Weekly Trivia',            'desc' => 'Placed first in this week\'s trivia challenge.', 'time' => '2 hours ago'],
        ['icon' => 'fa-solid fa-brain',         'title' => 'Completed Naruto Trivia',      'desc' => 'Finished the Naruto-themed trivia set.',          'time' => '5 hours ago'],
        ['icon' => 'fa-solid fa-check-square',  'title' => 'Voted for Best Anime Villain', 'desc' => 'Cast a vote in the Anime Awards.',                'time' => '8 hours ago'],
    ],
    'Yesterday' => [
        ['icon' => 'fa-solid fa-check-square',  'title' => 'Voted for Best Anime Opening', 'desc' => 'Cast a vote in the Anime Awards.',                'time' => 'Yesterday'],
        ['icon' => 'fa-solid fa-medal',         'title' => 'Earned First Badge',           'desc' => 'Unlocked the Founding Member badge.',             'time' => 'Yesterday'],
    ],
    'This Week' => [
        ['icon' => 'fa-solid fa-bolt',          'title' => 'Reached Level 12',             'desc' => 'Leveled up after a strong week of activity.',     'time' => '3 days ago'],
        ['icon' => 'fa-solid fa-check-square',  'title' => 'Finished 25 Votes',            'desc' => 'Cast your 25th vote across all categories.',      'time' => '5 days ago'],
        ['icon' => 'fa-solid fa-comments',      'title' => 'Commented on Community Post',  'desc' => 'Joined a discussion in the community feed.',      'time' => '6 days ago'],
    ],
    'Earlier' => [
        ['icon' => 'fa-solid fa-cake-candles',  'title' => 'Earned Anniversary Badge',     'desc' => 'Celebrated one year with Anime Nigeria.',         'time' => 'Jan 2026'],
        ['icon' => 'fa-solid fa-door-open',     'title' => 'Joined Anime Nigeria',         'desc' => 'Became a member of the community.',               'time' => $memberSince],
    ],
];
?>

<main class="akd-content">
    <div class="akd-profile">

        <!-- SECTION 1 — Profile Overview -->
        <section class="akd-profile-hero">

            <div class="akd-profile-hero__identity">
                <div class="akd-avatar-ring">
                    <?php if ($avatarUrl): ?>
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= htmlspecialchars($fullname) ?>" class="akd-avatar-ring__image">
                    <?php else: ?>
                        <div class="akd-avatar-ring__image akd-avatar-ring__image--initials" style="background-color: <?= htmlspecialchars($avatarColor) ?>"><?= htmlspecialchars($userInitials) ?></div>
                    <?php endif; ?>
                </div>

                <div class="akd-profile-hero__info">
                    <div class="akd-profile-hero__name-row">
                        <h1 class="akd-profile-hero__name"><?= htmlspecialchars($fullname) ?></h1>
                        <span class="akd-role-badge akd-role-badge--<?= htmlspecialchars($role) ?>"><?= htmlspecialchars($role) ?></span>
                    </div>

                    <span class="akd-profile-hero__username">@<?= htmlspecialchars($username) ?></span>

                    <div class="akd-profile-hero__email-row">
                        <i class="fa-solid fa-envelope"></i>
                        <span><?= htmlspecialchars($email) ?></span>
                        <?php if ($isVerified): ?>
                            <i class="fa-solid fa-circle-check akd-verified-icon" aria-label="Verified" title="Verified"></i>
                        <?php endif; ?>
                    </div>

                    <div class="akd-chip-row">
                        <div class="akd-chip">
                            <i class="fa-solid fa-calendar"></i>
                            <span>Member since <?= htmlspecialchars($memberSince) ?></span>
                        </div>
                        <div class="akd-chip">
                            <?php if ($isGoogle): ?>
                                <svg class="akd-google-icon" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z"/>
                                    <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z"/>
                                    <path fill="#FBBC05" d="M3.964 10.706A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.706V4.962H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.038l3.007-2.332z"/>
                                    <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.962L3.964 7.294C4.672 5.167 6.656 3.58 9 3.58z"/>
                                </svg>
                            <?php else: ?>
                                <i class="fa-solid fa-envelope"></i>
                            <?php endif; ?>
                            <span><?= $isGoogle ? 'Google Account' : 'Email Account' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="akd-identity-card">
                <div class="akd-identity-card__level">
                    <span class="akd-identity-card__eyebrow">Level</span>
                    <span class="akd-identity-card__level-value"><?= $level ?></span>
                    <span class="akd-identity-card__rank-title"><?= htmlspecialchars($levelTitle) ?></span>
                </div>

                <div class="akd-identity-card__xp">
                    <div class="akd-xp-bar">
                        <div class="akd-xp-bar__fill" style="width: <?= $xpProgress ?>%"></div>
                    </div>
                    <div class="akd-identity-card__xp-meta">
                        <span><?= $xpProgress ?>%</span>
                        <span><?= number_format($xpRemaining) ?> XP to next level</span>
                    </div>
                </div>

                <div class="akd-identity-card__actions">
                    <button type="button" class="akd-btn akd-btn--primary" data-modal-open>Edit Profile</button>
                </div>
            </div>

        </section>

        <!-- SECTION 2 — Statistics -->
        <section class="akd-profile-stats">
            <div class="akd-stat-featured">
                <i class="fa-solid fa-ranking-star akd-stat-featured__icon"></i>
                <div class="akd-stat-featured__body">
                    <span class="akd-stat-featured__label">Current Rank</span>
                    <span class="akd-stat-featured__value"><?= htmlspecialchars($rankLabel) ?></span>
                    <span class="akd-stat-featured__trend"><i class="fa-solid fa-arrow-trend-up"></i> <?= htmlspecialchars($rankCaption) ?></span>
                </div>
            </div>

            <div class="akd-stats-grid">
                <?php foreach ($stats as $stat): ?>
                    <div class="akd-stat-card">
                        <i class="<?= htmlspecialchars($stat['icon']) ?> akd-stat-card__icon" aria-hidden="true"></i>
                        <div class="akd-stat-card__body">
                            <span class="akd-stat-card__value"><?= htmlspecialchars((string) $stat['value']) ?></span>
                            <span class="akd-stat-card__label"><?= htmlspecialchars($stat['label']) ?></span>
                            <?php if ($stat['trend']): ?>
                                <span class="akd-stat-card__trend"><?= htmlspecialchars($stat['trend']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </section>

        <!-- SECTION 3 — Achievements (preview: 8 most recent, full list on /member/achievements) -->
        <section class="akd-achievements">
            <div class="akd-section-heading">
                <div class="akd-section-heading__text">
                    <h2 class="akd-section-heading__title">Achievements</h2>
                    <p class="akd-section-heading__subtitle">A trophy cabinet of everything you've earned so far.</p>
                </div>
                <a href="/member/achievements" class="akd-section-heading__action">View All <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="akd-achievements-grid">
                <?php foreach ($achievementsPreview as $achievement): ?>
                    <?php require __DIR__ . '/includes/partials/achievement-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- SECTION 4 — Activity Timeline -->
        <section class="akd-timeline">
            <div class="akd-section-heading">
                <div class="akd-section-heading__text">
                    <h2 class="akd-section-heading__title">Activity Timeline</h2>
                    <p class="akd-section-heading__subtitle">Your journey with Anime Nigeria so far.</p>
                </div>
            </div>

            <div class="akd-timeline__track">
                <?php foreach ($activityGroups as $groupLabel => $items): ?>
                    <div class="akd-timeline__group">
                        <h3 class="akd-timeline__group-label"><?= htmlspecialchars($groupLabel) ?></h3>
                        <ul class="akd-timeline__list">
                            <?php foreach ($items as $item): ?>
                                <li class="akd-timeline__item">
                                    <span class="akd-timeline__node"><i class="<?= htmlspecialchars($item['icon']) ?>"></i></span>
                                    <div class="akd-timeline__card">
                                        <span class="akd-timeline__title"><?= htmlspecialchars($item['title']) ?></span>
                                        <span class="akd-timeline__desc"><?= htmlspecialchars($item['desc']) ?></span>
                                        <span class="akd-timeline__time"><?= htmlspecialchars($item['time']) ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </div>

    <!-- Edit Profile Modal -->
    <div class="akd-modal-overlay" id="editProfileOverlay">
        <div class="akd-modal" role="dialog" aria-modal="true" aria-labelledby="editProfileTitle" id="editProfileModal">
            <div class="akd-modal__header">
                <h2 class="akd-modal__title" id="editProfileTitle">Edit Profile</h2>
                <button type="button" class="akd-modal__close" data-modal-close aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form novalidate data-is-google="<?= $isGoogle ? '1' : '0' ?>">
                <div class="akd-modal__body">

                    <div class="akd-modal__banner" data-modal-banner role="status" aria-live="polite"></div>

                    <div class="akd-modal__avatar-field">
                        <div class="akd-modal__avatar-wrap" data-user-initials="<?= htmlspecialchars($userInitials) ?>" data-avatar-color="<?= htmlspecialchars($avatarColor) ?>">
                            <button type="button" class="akd-modal__avatar-preview" data-avatar-preview aria-label="View profile picture">
                                <?php if ($avatarUrl): ?>
                                    <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="" class="akd-modal__avatar-img" data-avatar-img>
                                <?php else: ?>
                                    <div class="akd-modal__avatar-img akd-modal__avatar-img--initials" data-avatar-img style="background-color: <?= htmlspecialchars($avatarColor) ?>"><?= htmlspecialchars($userInitials) ?></div>
                                <?php endif; ?>
                            </button>
                            <label class="akd-modal__avatar-upload" for="avatarUploadInput" aria-label="Upload new photo">
                                <i class="fa-solid fa-camera"></i>
                            </label>
                        </div>

                        <input type="file" id="avatarUploadInput" accept="image/png,image/jpeg" class="akd-modal__avatar-input" data-avatar-input>
                        <span class="akd-modal__avatar-hint" data-avatar-hint>PNG or JPG, up to 2MB</span>
                        <span class="akd-modal__avatar-error" data-avatar-error></span>

                        <button type="button" class="akd-modal__avatar-remove" data-avatar-remove<?= $avatarUrl ? '' : ' hidden' ?>>
                            <i class="fa-solid fa-trash-can"></i> Remove
                        </button>
                    </div>

                    <div class="akd-field" data-field="fullname">
                        <label class="akd-field__label" for="editFullname">Full Name</label>
                        <div class="akd-field__control-wrap">
                            <input type="text" id="editFullname" class="akd-field__input" value="<?= htmlspecialchars($fullname) ?>" maxlength="100" autocomplete="off">
                        </div>
                        <span class="akd-field__error-msg" data-field-error></span>
                    </div>

                    <div class="akd-field" data-field="username">
                        <label class="akd-field__label" for="editUsername">Username</label>
                        <div class="akd-field__control-wrap">
                            <span class="akd-field__affix">@</span>
                            <input type="text" id="editUsername" class="akd-field__input akd-field__input--with-affix" value="<?= htmlspecialchars($username) ?>" maxlength="20" autocomplete="off">
                        </div>
                        <span class="akd-field__error-msg" data-field-error></span>
                    </div>

                    <?php if (!$isGoogle): ?>
                        <div class="akd-modal__divider"></div>
                        <div class="akd-password-section">
                            <div class="akd-field" data-field="currentPassword">
                                <label class="akd-field__label" for="editCurrentPassword">Current Password</label>
                                <div class="akd-field__control-wrap">
                                    <input type="password" id="editCurrentPassword" class="akd-field__input akd-field__input--with-toggle" autocomplete="current-password">
                                    <button type="button" class="akd-field__toggle" data-password-toggle aria-label="Show password">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <span class="akd-field__hint">Needed to confirm it's you before changing your password.</span>
                                <span class="akd-field__error-msg" data-field-error></span>
                            </div>

                            <div class="akd-field" data-field="newPassword">
                                <label class="akd-field__label" for="editNewPassword">New Password</label>
                                <div class="akd-field__control-wrap">
                                    <input type="password" id="editNewPassword" class="akd-field__input akd-field__input--with-toggle" autocomplete="new-password">
                                    <button type="button" class="akd-field__toggle" data-password-toggle aria-label="Show password">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <ul class="akd-password-rules" data-password-rules>
                                    <li data-rule="length"><i class="fa-solid fa-circle"></i> At least 8 characters</li>
                                    <li data-rule="uppercase"><i class="fa-solid fa-circle"></i> One uppercase letter</li>
                                    <li data-rule="number"><i class="fa-solid fa-circle"></i> One number</li>
                                    <li data-rule="symbol"><i class="fa-solid fa-circle"></i> One symbol (! @ # $ % &amp; * ? ,)</li>
                                </ul>
                                <span class="akd-field__error-msg" data-field-error></span>
                            </div>

                            <div class="akd-field" data-field="confirmPassword">
                                <label class="akd-field__label" for="editConfirmPassword">Confirm New Password</label>
                                <div class="akd-field__control-wrap">
                                    <input type="password" id="editConfirmPassword" class="akd-field__input akd-field__input--with-toggle" autocomplete="new-password">
                                    <button type="button" class="akd-field__toggle" data-password-toggle aria-label="Show password">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <span class="akd-field__error-msg" data-field-error></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="akd-modal__divider"></div>
                        <div class="akd-info-note">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>This account is managed through Google.</span>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="akd-modal__footer">
                    <button type="button" class="akd-btn akd-btn--secondary" data-modal-cancel>Cancel</button>
                    <button type="submit" class="akd-btn akd-btn--primary" data-modal-save disabled>Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirm Dialog (reused for delete-avatar + unsaved changes) -->
    <div class="akd-confirm-overlay" id="akdConfirmOverlay">
        <div class="akd-confirm" role="alertdialog" aria-modal="true" aria-labelledby="akdConfirmTitle" aria-describedby="akdConfirmMessage" id="akdConfirmDialog">
            <h3 class="akd-confirm__title" id="akdConfirmTitle"></h3>
            <p class="akd-confirm__message" id="akdConfirmMessage"></p>
            <div class="akd-confirm__actions">
                <button type="button" class="akd-btn akd-btn--secondary" data-confirm-cancel></button>
                <button type="button" class="akd-btn akd-btn--danger" data-confirm-accept></button>
            </div>
        </div>
    </div>

    <!-- Avatar Lightbox -->
    <div class="an-lightbox" id="akdAvatarLightbox">
        <button type="button" class="an-lightbox__close" data-lightbox-close aria-label="Close">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <img src="" alt="Profile picture" class="an-lightbox__image" data-lightbox-image>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>