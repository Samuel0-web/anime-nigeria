<?php
$auth->requireAuth();

require_once __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/../../../includes/vite.php';

use App\Security\Csrf;
use App\Support\Avatar;
$user = $auth->user();

if ($user === null) {
    $auth->logout();
    header('Location: /login');
    exit;
}

$page_title = $page_title ?? 'Member Dashboard';
$page_description = $page_description ?? 'Anime Nigeria Member Dashboard';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$breadcrumbs ??= [
    [
        'label' => 'Dashboard',
        'url'   => null
    ]
];

$nameParts = explode(' ', $user['fullname'] ?? 'User');
$initial1 = substr($nameParts[0] ?? 'U', 0, 1);
$initial2 = isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : '';
$userInitials = strtoupper($initial1 . $initial2);
$firstName = $nameParts[0] ?? 'there';

// Navigation structure — shared source for the sidebar and the breadcrumb trail
$navGroups = [
    'Main' => [
        ['label' => 'Dashboard', 'icon' => 'fa-solid fa-house', 'url' => '/dashboard'],
        ['label' => 'Anime Awards', 'icon' => 'fa-solid fa-trophy', 'url' => '/member/awards'],
    ],
    'Community' => [
        ['label' => 'Voting', 'icon' => 'fa-solid fa-check-square', 'url' => '/member/voting'],
        ['label' => 'Trivia', 'icon' => 'fa-solid fa-brain', 'url' => '/member/trivia'],
        ['label' => 'Leaderboard', 'icon' => 'fa-solid fa-ranking-star', 'url' => '/member/leaderboard'],
        ['label' => 'Community', 'icon' => 'fa-solid fa-users', 'url' => '/member/community'],
        ['label' => 'Gallery', 'icon' => 'fa-solid fa-images', 'url' => '/member/gallery'],
        ['label' => 'Blogs', 'icon' => 'fa-solid fa-pen-fancy', 'url' => '/member/blogs'],
    ],
    'System' => [
        ['label' => 'Settings', 'icon' => 'fa-solid fa-cog', 'url' => '/member/settings'],
        ['label' => 'Help Center', 'icon' => 'fa-solid fa-question-circle', 'url' => '/member/help'],
    ],
];

$avatarColor = Avatar::color($user['username'] !== '' ? $user['username'] : $user['fullname']);
$hour = (int) date('G');

if ($hour < 12) {
    $greeting = 'Good Morning';
    $greetingIcon = 'fa-solid fa-cloud-sun';
    $greetingClass = 'akd-header__greeting-icon--morning';
} elseif ($hour < 19) {
    $greeting = 'Good Afternoon';
    $greetingIcon = 'fa-solid fa-sun';
    $greetingClass = 'akd-header__greeting-icon--afternoon';
} else {
    $greeting = 'Good Evening';
    $greetingIcon = 'fa-solid fa-cloud-moon';
    $greetingClass = 'akd-header__greeting-icon--evening';
}

$todayDate = date('l, F j');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | Anime Nigeria</title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <meta name="theme-color" content="#000000">
    <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token()) ?>">

    <link rel="icon" type="image/png" sizes="192x192" href="/uploads/upscalemedia-transformed (1).png">
    <link rel="icon" type="image/png" sizes="32x32" href="/uploads/upscalemedia-transformed (1).png">
    <link rel="apple-touch-icon" sizes="180x180" href="/uploads/upscalemedia-transformed (1).png">
    <link rel="preload" href="/uploads/upscalemedia-transformed (3).png" as="image" fetchpriority="high">

    <?php vite('member'); ?>

    <script type="module">
        import RefreshRuntime from 'http://127.0.0.1:5173/@react-refresh';

        RefreshRuntime.injectIntoGlobalHook(window);
        window.$RefreshReg$ = () => {};
        window.$RefreshSig$ = () => (type) => type;
        window.__vite_plugin_react_preamble_installed__ = true;
    </script>

    <?php vite('react'); ?>
</head>
<body>
<div class="preloader" id="preloader">
    <img src="/uploads/upscalemedia-transformed (3).png" alt="" class="preloader__wheel" draggable="false"
        fetchpriority="high" decoding="async"
    >
</div>

<div class="akd-layout" id="akdLayout">

    <!-- Header -->
    <header class="akd-header">
        <div class="akd-header__primary">
            <button class="akd-header__toggle" id="sidebarToggle" aria-label="Open menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>

            <div class="akd-header__context">
                <h1 class="akd-header__mobile-title">
                    <?= htmlspecialchars($page_title) ?>
                </h1>
                
                <nav class="akd-breadcrumbs" aria-label="Breadcrumb">
                    <?php foreach ($breadcrumbs as $i => $crumb): ?>
                        <?php if ($i > 0): ?>
                            <i class="fas fa-chevron-right akd-breadcrumbs__separator" aria-hidden="true"></i>
                        <?php endif; ?>

                        <?php
                        $url = $crumb['url'] ?? null;

                        if ($url && $i < count($breadcrumbs) - 1): ?>
                            <a href="<?= htmlspecialchars($url) ?>" class="akd-breadcrumbs__item">
                                <?= htmlspecialchars($crumb['label']) ?>
                            </a>
                        <?php else: ?>
                            <span class="akd-breadcrumbs__item akd-breadcrumbs__item--current">
                                <?= htmlspecialchars($crumb['label']) ?>
                            </span>
                        <?php endif; ?>

                    <?php endforeach; ?>
                </nav>

                <h1 class="akd-header__greeting">
                    <span class="akd-header__greeting-icon <?= htmlspecialchars($greetingClass) ?>">
                        <i class="<?= htmlspecialchars($greetingIcon) ?>"></i>
                    </span>
                    <?= htmlspecialchars($greeting) ?>, <?= htmlspecialchars($firstName) ?>
                </h1>

                <p class="akd-header__subtitle">
                    <span><?= htmlspecialchars($todayDate) ?></span>
                    <span class="akd-header__dot" aria-hidden="true">&bull;</span>
                    <span><?= htmlspecialchars($page_title) ?></span>
                </p>
            </div>
        </div>

        <div class="akd-header__actions">
            <button class="akd-header__notification" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <span class="akd-header__badge">3</span>
            </button>
        </div>
    </header>

    <!-- Mobile overlay -->
    <div class="akd-overlay" id="akdOverlay"></div>

    <!-- Sidebar -->
    <nav class="akd-sidebar" id="akdSidebar" aria-label="Main Navigation">
        <div class="akd-sidebar__header">
            <a href="/dashboard" class="akd-sidebar__brand">
                <img src="/uploads/Landscape-Anime-Nigeria-Logo.png" alt="Anime Nigeria" class="akd-sidebar__logo">
            </a>
            <button class="akd-sidebar__close" id="sidebarClose" aria-label="Close menu">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <div class="akd-sidebar__scroll">

            <!-- Profile Card with Dropdown -->
            <div class="akd-sidebar__profile-wrapper">
                <button class="akd-sidebar__profile" id="profileDropdownTrigger" aria-expanded="false" aria-haspopup="true">
                    <div class="akd-sidebar__avatar-wrap" data-user-avatar-container>
                        <img data-user-avatar src="<?= htmlspecialchars($user['avatar'] ?? '') ?>"
                            alt="<?= htmlspecialchars($user['fullname']) ?>" class="akd-sidebar__avatar"
                            <?= !empty($user['avatar']) ? '' : 'style="display:none"' ?>
                        >

                        <div data-user-avatar-initials class="akd-sidebar__avatar akd-sidebar__avatar--initials"
                            style="<?= !empty($user['avatar']) ? 'display:none;' : 'display:flex;' ?>; background-color: <?= htmlspecialchars($avatarColor) ?>;"
                        >
                            <?= htmlspecialchars($userInitials) ?>
                        </div>
                    </div>

                    <div class="akd-sidebar__profile-info">
                        <span data-user-fullname class="akd-sidebar__profile-name">
                            <?= htmlspecialchars($user['fullname'] ?? 'User') ?>
                        </span>

                        <span data-user-username class="akd-sidebar__profile-username">
                            @<?= htmlspecialchars($user['username'] ?? 'member') ?>
                        </span>
                    </div>

                    <i class="fas fa-chevron-down akd-sidebar__profile-chevron"></i>
                </button>

                <div class="akd-sidebar__profile-dropdown" id="profileDropdown" role="menu">
                    <a href="/member/profile" class="akd-sidebar__dropdown-item" role="menuitem">
                        View Profile
                    </a>
                    <div class="akd-sidebar__dropdown-divider"></div>
                    <form action="/logout" method="POST" class="akd-sidebar__dropdown-logout-form" data-logout-form>
                        <button type="submit" class="akd-sidebar__dropdown-item
                            akd-sidebar__dropdown-item--logout" role="menuitem"
                        >
                            <i class="fas fa-sign-out-alt akd-sidebar__icon"></i>
                            <span class="akd-sidebar__label">Sign Out</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Navigation -->
            <div class="akd-sidebar__nav">
                <?php foreach ($navGroups as $groupName => $items): ?>
                    <div class="akd-sidebar__group">
                        <div class="akd-sidebar__group-label"><?= htmlspecialchars($groupName) ?></div>
                        <ul class="akd-sidebar__list">
                            <?php foreach ($items as $item):
                                $isActive = str_starts_with($currentPath, $item['url']);
                                $class = 'akd-sidebar__item' . ($isActive ? ' akd-sidebar__item--active' : '');
                            ?>
                                <li class="<?= $class ?>">
                                    <a href="<?= htmlspecialchars($item['url']) ?>" class="akd-sidebar__link">
                                        <i class="<?= htmlspecialchars($item['icon']) ?> akd-sidebar__icon"></i>
                                        <span class="akd-sidebar__label"><?= htmlspecialchars($item['label']) ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>
</div>