<?php
require_once __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/../../../includes/vite.php';

$auth->requireAuth();

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
        [
            'label' => 'Anime Awards',
            'icon' => 'fa-solid fa-trophy',
            'url' => null,
            'children' => [
                ['label' => 'Overview', 'url' => '/member/awards'],
                ['label' => 'Nominations', 'url' => '/member/awards/nominations'],
                ['label' => 'Voting', 'url' => '/member/awards/voting'],
                ['label' => 'Winners', 'url' => '/member/awards/winners'],
            ],
        ],
        ['label' => 'Announcements', 'icon' => 'fa-solid fa-bullhorn', 'url' => '/member/announcements'],
    ],
    'Community' => [
        ['label' => 'Trivia', 'icon' => 'fa-solid fa-brain', 'url' => '/member/trivia'],
        ['label' => 'Leaderboard', 'icon' => 'fa-solid fa-ranking-star', 'url' => '/member/leaderboard'],
        [
            'label' => 'Community',
            'icon' => 'fa-solid fa-users',
            'url' => null,
            'children' => [
                ['label' => 'Challenges', 'url' => '/member/community/challenges'],
                [
                    'label' => 'Community Awards',
                    'url' => null,
                    'children' => [
                        ['label' => 'Overview', 'url' => '/member/community/awards'],
                        ['label' => 'Nominations', 'url' => '/member/community/awards/nominations'],
                        ['label' => 'Voting', 'url' => '/member/community/awards/voting'],
                        ['label' => 'Winners', 'url' => '/member/community/awards/winners'],
                    ],
                ],
                ['label' => 'Honoured Ones', 'url' => '/member/community/honoured-ones'],
            ],
        ],
        ['label' => 'Gallery', 'icon' => 'fa-solid fa-images', 'url' => '/member/gallery'],
        ['label' => 'Blogs', 'icon' => 'fa-solid fa-pen-fancy', 'url' => '/member/blogs'],
    ],
    'System' => [
        ['label' => 'Settings', 'icon' => 'fa-solid fa-cog', 'url' => '/member/settings'],
        ['label' => 'Help Center', 'icon' => 'fa-solid fa-question-circle', 'url' => '/member/help'],
    ],
];

if (!function_exists('akd_nav_is_route_active')) {
    function akd_nav_is_route_active(?string $url, string $currentPath): bool
    {
        if (!$url) {
            return false;
        }

        return rtrim($currentPath, '/') === rtrim($url, '/');
    }
}

if (!function_exists('akd_nav_branch_is_active')) {
    function akd_nav_branch_is_active(array $item, string $currentPath): bool
    {
        // Exact match for this item's own URL.
        if (akd_nav_is_route_active($item['url'] ?? null, $currentPath)) {
            return true;
        }

        // Otherwise only consider descendants.
        foreach ($item['children'] ?? [] as $child) {
            if (akd_nav_branch_is_active($child, $currentPath)) {
                return true;
            }
        }

        return false;
    }
}

/**
 * Recursively renders <li> nav items at any depth. A parent (has 'children')
 * gets its own independent expand/collapse state, driven by aria-expanded +
 * an id on its <ul>. Only the branch containing the active route auto-opens.
 */
if (!function_exists('akd_render_nav_items')) {
    function akd_render_nav_items(array $items, string $currentPath, int $depth = 0): void
    {
        static $submenuCounter = 0;

        foreach ($items as $item) {
            $hasChildren = !empty($item['children']);
            $url = $item['url'] ?? null;
            $isActive = akd_nav_is_route_active($url, $currentPath);
            $branchActive = $hasChildren && akd_nav_branch_is_active($item, $currentPath);
            $isExpanded = $branchActive;

            $itemClass = 'akd-sidebar__item';
            if ($depth > 0) {
                $itemClass .= ' akd-sidebar__item--child';
            }
            if ($isActive) {
                $itemClass .= ' akd-sidebar__item--active';
            }

            if (!$hasChildren) {
                ?>
                <li class="<?= $itemClass ?>">
                    <a href="<?= htmlspecialchars($url ?? '#') ?>" class="akd-sidebar__link">
                        <?php if (!empty($item['icon'])): ?>
                            <i class="<?= htmlspecialchars($item['icon']) ?> akd-sidebar__icon"></i>
                        <?php endif; ?>
                        <span class="akd-sidebar__label"><?= htmlspecialchars($item['label']) ?></span>
                    </a>
                </li>
                <?php
                continue;
            }

            $submenuId = 'akd-submenu-' . (++$submenuCounter);
            ?>
            <li class="<?= $itemClass ?> akd-sidebar__item--parent">
                <div class="akd-sidebar__row">
                    <?php if ($url): ?>
                        <a href="<?= htmlspecialchars($url) ?>" class="akd-sidebar__link akd-sidebar__link--parent">
                            <?php if (!empty($item['icon'])): ?>
                                <i class="<?= htmlspecialchars($item['icon']) ?> akd-sidebar__icon"></i>
                            <?php endif; ?>
                            <span class="akd-sidebar__label"><?= htmlspecialchars($item['label']) ?></span>
                        </a>
                        <button
                            type="button"
                            class="akd-sidebar__expand-btn"
                            aria-expanded="<?= $isExpanded ? 'true' : 'false' ?>"
                            aria-controls="<?= $submenuId ?>"
                            aria-label="Toggle <?= htmlspecialchars($item['label']) ?> submenu"
                        >
                            <i class="fas fa-chevron-right akd-sidebar__chevron" aria-hidden="true"></i>
                        </button>
                    <?php else: ?>
                        <button
                            type="button"
                            class="akd-sidebar__link akd-sidebar__link--parent akd-sidebar__expand-btn"
                            aria-expanded="<?= $isExpanded ? 'true' : 'false' ?>"
                            aria-controls="<?= $submenuId ?>"
                        >
                            <?php if (!empty($item['icon'])): ?>
                                <i class="<?= htmlspecialchars($item['icon']) ?> akd-sidebar__icon"></i>
                            <?php endif; ?>
                            <span class="akd-sidebar__label"><?= htmlspecialchars($item['label']) ?></span>
                            <i class="fas fa-chevron-right akd-sidebar__chevron" aria-hidden="true"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <ul class="akd-sidebar__sublist<?= $isExpanded ? ' is-open' : '' ?>" id="<?= $submenuId ?>">
                    <?php akd_render_nav_items($item['children'], $currentPath, $depth + 1); ?>
                </ul>
            </li>
            <?php
        }
    }
}

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
    <svg class="preloader__wheel" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <defs>
            <!-- Gradient for the main golden body to simulate lighting -->
            <linearGradient id="wheel-grad" x1="10%" y1="10%" x2="90%" y2="90%">
                <stop offset="0%" stop-color="#937C49" />
                <stop offset="50%" stop-color="#C5B173" />
                <stop offset="100%" stop-color="#A59152" />
            </linearGradient>
            <!-- Gradient for the subtle dark rim/edge to simulate the 3D bevel -->
            <linearGradient id="rim-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#8A7B4B" />
                <stop offset="100%" stop-color="#5C502A" />
            </linearGradient>
        </defs>

        <!-- Base layer (Darker edge acting as a stroke/bevel) -->
        <g stroke="url(#rim-grad)" fill="url(#rim-grad)" stroke-linecap="round" stroke-linejoin="round">
            <!-- Outer Ring -->
            <circle cx="50" cy="50" r="28" fill="none" stroke-width="6.5" />
            <!-- Spokes -->
            <path d="M50 12 L50 88 M12 50 L88 50 M23.13 23.13 L76.87 76.87 M23.13 76.87 L76.87 23.13" stroke-width="5.5" />
            <!-- Outer Knobs -->
            <circle cx="50" cy="12" r="6" stroke="none" />
            <circle cx="50" cy="88" r="6" stroke="none" />
            <circle cx="12" cy="50" r="6" stroke="none" />
            <circle cx="88" cy="50" r="6" stroke="none" />
            <circle cx="23.13" cy="23.13" r="6" stroke="none" />
            <circle cx="76.87" cy="76.87" r="6" stroke="none" />
            <circle cx="23.13" cy="76.87" r="6" stroke="none" />
            <circle cx="76.87" cy="23.13" r="6" stroke="none" />
        </g>

        <!-- Top layer (Golden highlight) -->
        <g stroke="url(#wheel-grad)" fill="url(#wheel-grad)" stroke-linecap="round" stroke-linejoin="round">
            <!-- Outer Ring -->
            <circle cx="50" cy="50" r="28" fill="none" stroke-width="4.5" />
            <!-- Spokes -->
            <path d="M50 12 L50 88 M12 50 L88 50 M23.13 23.13 L76.87 76.87 M23.13 76.87 L76.87 23.13" stroke-width="3.5" />
            <!-- Outer Knobs -->
            <circle cx="50" cy="12" r="5" stroke="none" />
            <circle cx="50" cy="88" r="5" stroke="none" />
            <circle cx="12" cy="50" r="5" stroke="none" />
            <circle cx="88" cy="50" r="5" stroke="none" />
            <circle cx="23.13" cy="23.13" r="5" stroke="none" />
            <circle cx="76.87" cy="76.87" r="5" stroke="none" />
            <circle cx="23.13" cy="76.87" r="5" stroke="none" />
            <circle cx="76.87" cy="23.13" r="5" stroke="none" />
        </g>
    </svg>
</div>

<div class="akd-layout" id="akdLayout">
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
    <nav class="akd-sidebar" id="akdSidebar" data-user-id="<?= (int) $user['id'] ?>" aria-label="Main Navigation">
        <div class="akd-sidebar__header">
            <a href="/dashboard" class="akd-sidebar__brand">
                <img src="/uploads/Landscape-Anime-Nigeria-Logo.png" alt="Anime Nigeria" class="akd-sidebar__logo">
            </a>
            <button class="akd-sidebar__close" id="sidebarClose" aria-label="Close menu">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <div class="akd-sidebar__scroll">
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
                            <?php akd_render_nav_items($items, $currentPath); ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>
</div>