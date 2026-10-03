<?php
// public/admin/partials/header.php
require_once __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/../../../includes/vite.php';

$auth->requireAuth();

use App\Security\Csrf;
use App\Security\Nonce;
use App\Support\Avatar;

$user = $auth->user();

if ($user === null) {
    $auth->logout();
    header('Location: /login');
    exit;
}

if (!in_array($user['role'] ?? null, ['admin', 'moderator'], true)) {
    header('Location: /dashboard');
    exit;
}

$page_title = $page_title ?? 'Admin Dashboard';
$page_description = $page_description ?? 'Anime Nigeria Admin Dashboard';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$canonicalPath = rtrim($currentPath, '/') ?: '/';
$canonicalUrl = rtrim((string) \App\Core\Config::get('APP_URL'), '/') . $canonicalPath;

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

// Same fallback-colour source as the member side (App\Support\Avatar).
$avatarSeed = ($user['username'] ?? '') !== '' ? $user['username'] : ($user['fullname'] ?? 'User');
$avatarColor = Avatar::color((string) $avatarSeed);
$csrfToken = Csrf::token();

// Pages may define $adminNotifications before including this header:
// [['title' => '...', 'time' => '5m', 'url' => '/admin/...', 'unread' => true], ...]
$adminNotifications ??= [];
$adminNotifUnread = count(array_filter(
    $adminNotifications,
    static fn ($n) => !empty($n['unread'])
));

// Navigation structure. Same shape as the member side: 'label', 'icon',
// 'url' (null for pure parents), 'children' (any depth), optional 'badge'.
$navGroups = [
    'Overview' => [
        ['label' => 'Dashboard', 'icon' => 'fa-solid fa-gauge-high', 'url' => '/home'],
    ],
    'Management' => [
        [
            'label' => 'Users',
            'icon' => 'fa-solid fa-users',
            'url' => null,
            'children' => [
                ['label' => 'Members', 'url' => '/admin/users'],
                [
                    'label' => 'Roles',
                    'url' => null,
                    'children' => [
                        ['label' => 'Admin Roles', 'url' => '/admin/users/roles/admin'],
                        ['label' => 'Moderator Roles', 'url' => '/admin/users/roles/moderator'],
                        ['label' => 'Custom Roles', 'url' => '/admin/users/roles/custom'],
                    ],
                ],
                [
                    'label' => 'Permissions',
                    'url' => null,
                    'children' => [
                        ['label' => 'Global', 'url' => '/admin/users/permissions/global'],
                        [
                            'label' => 'Advanced',
                            'url' => null,
                            'children' => [
                                ['label' => 'Read', 'url' => '/admin/users/permissions/advanced/read'],
                                ['label' => 'Write', 'url' => '/admin/users/permissions/advanced/write'],
                                ['label' => 'Delete', 'url' => '/admin/users/permissions/advanced/delete'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        ['label' => 'Announcements', 'icon' => 'fa-solid fa-bullhorn', 'url' => '/admin/announcements'],
        [
            'label' => 'Awards',
            'icon' => 'fa-solid fa-trophy',
            'url' => null,
            'children' => [
                ['label' => 'Anime Awards (ANAA)', 'url' => '/admin/awards/anaa'],
                ['label' => 'Community Awards (ANCA)', 'url' => '/admin/awards/anca'],
            ],
        ],
    ],
    'System' => [
        ['label' => 'Settings', 'icon' => 'fa-solid fa-cog', 'url' => '/admin/settings'],
    ],
];

if (!function_exists('akd_admin_nav_is_active')) {
    function akd_admin_nav_is_active(?string $url, string $currentPath): bool {
        if (!$url) { return false; }
        return rtrim($currentPath, '/') === rtrim($url, '/');
    }
}

if (!function_exists('akd_admin_nav_branch_is_active')) {
    function akd_admin_nav_branch_is_active(array $item, string $currentPath): bool
    {
        if (akd_admin_nav_is_active($item['url'] ?? null, $currentPath)) {
            return true;
        }

        foreach ($item['children'] ?? [] as $child) {
            if (akd_admin_nav_branch_is_active($child, $currentPath)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('akd_admin_render_nav_badge')) {
    function akd_admin_render_nav_badge(array $item): void
    {
        if (empty($item['badge'])) {
            return;
        }
        ?>
        <span class="akd-admin-nav__badge" aria-hidden="true"><?= htmlspecialchars((string) $item['badge']) ?></span>
        <?php if (!empty($item['badge_label'])): ?>
            <span class="visually-hidden"><?= htmlspecialchars($item['badge_label']) ?></span>
        <?php endif; ?>
        <?php
    }
}

// Icon for top-level items, a small dot for nested items.
if (!function_exists('akd_admin_render_nav_lead')) {
    function akd_admin_render_nav_lead(array $item, int $depth): void
    {
        if (!empty($item['icon'])) {
            ?><i class="<?= htmlspecialchars($item['icon']) ?> akd-admin-nav__icon" aria-hidden="true"></i><?php
        } elseif ($depth > 0) {
            ?><span class="akd-admin-nav__dot" aria-hidden="true"></span><?php
        }
    }
}

/**
 * One recursive renderer for every depth. A parent owns an independent
 * expand/collapse state (aria-expanded + a collapse wrapper id). Every
 * ancestor of the active route renders already expanded.
 */
if (!function_exists('akd_admin_render_nav_items')) {
    function akd_admin_render_nav_items(array $items, string $currentPath, int $depth = 0): void
    {
        static $submenuCounter = 0;

        foreach ($items as $item) {
            $hasChildren = !empty($item['children']);
            $url = $item['url'] ?? null;
            $isActive = akd_admin_nav_is_active($url, $currentPath);
            $branchActive = $hasChildren && akd_admin_nav_branch_is_active($item, $currentPath);
            $isExpanded = $branchActive;
            $itemClass = 'akd-admin-nav__item';
            $itemClass .= $depth === 0 ? ' akd-admin-nav__item--root' : ' akd-admin-nav__item--child';

            if ($depth >= 2) {
                $itemClass .= ' akd-admin-nav__item--deep';
            }

            if ($isActive) {
                $itemClass .= ' akd-admin-nav__item--active';
            }

            if ($hasChildren && $branchActive && !$isActive) {
                $itemClass .= ' akd-admin-nav__item--branch';
            }

            if (!$hasChildren) {
                ?>
                <li class="<?= $itemClass ?>">
                    <a href="<?= htmlspecialchars($url ?? '#') ?>" class="akd-admin-nav__link"<?= $isActive ? ' aria-current="page"' : '' ?>>
                        <?php akd_admin_render_nav_lead($item, $depth); ?>
                        <span class="akd-admin-nav__label"><?= htmlspecialchars($item['label']) ?></span>
                        <?php akd_admin_render_nav_badge($item); ?>
                    </a>
                </li>
                <?php
                continue;
            }

            $submenuId = 'akd-admin-submenu-' . (++$submenuCounter);
            ?>
            <li class="<?= $itemClass ?> akd-admin-nav__item--parent">
                <div class="akd-admin-nav__row">
                    <?php if ($url): ?>
                        <a href="<?= htmlspecialchars($url) ?>" class="akd-admin-nav__link akd-admin-nav__link--parent"<?= $isActive ? ' aria-current="page"' : '' ?>>
                            <?php akd_admin_render_nav_lead($item, $depth); ?>
                            <span class="akd-admin-nav__label"><?= htmlspecialchars($item['label']) ?></span>
                            <?php akd_admin_render_nav_badge($item); ?>
                        </a>
                        <button type="button"
                            class="akd-admin-nav__chevron-btn akd-admin-nav__expand-btn"
                            aria-expanded="<?= $isExpanded ? 'true' : 'false' ?>"
                            aria-controls="<?= $submenuId ?>"
                            aria-label="Toggle <?= htmlspecialchars($item['label']) ?> submenu"
                        >
                            <i class="fas fa-chevron-right akd-admin-nav__chevron" aria-hidden="true"></i>
                        </button>
                    <?php else: ?>
                        <button type="button"
                            class="akd-admin-nav__link akd-admin-nav__link--parent akd-admin-nav__expand-btn"
                            aria-expanded="<?= $isExpanded ? 'true' : 'false' ?>"
                            aria-controls="<?= $submenuId ?>"
                        >
                            <?php akd_admin_render_nav_lead($item, $depth); ?>
                            <span class="akd-admin-nav__label"><?= htmlspecialchars($item['label']) ?></span>
                            <?php akd_admin_render_nav_badge($item); ?>
                            <i class="fas fa-chevron-right akd-admin-nav__chevron" aria-hidden="true"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="akd-admin-nav__collapse<?= $isExpanded ? ' is-open' : '' ?>" id="<?= $submenuId ?>">
                    <ul class="akd-admin-nav__sublist">
                        <?php akd_admin_render_nav_items($item['children'], $currentPath, $depth + 1); ?>
                    </ul>
                </div>
            </li>
            <?php
        }
    }
}

// Avatar markup shared by the topbar, the account menu and the mobile sidebar.
// Keeps the data-user-avatar* hooks the member profile code updates.
if (!function_exists('akd_admin_render_avatar')) {
    function akd_admin_render_avatar(array $user, string $initials, string $color, string $modifier = ''): void
    {
        $hasAvatar = !empty($user['avatar']);
        ?>
        <span class="akd-admin-avatar<?= $modifier !== '' ? ' ' . htmlspecialchars($modifier) : '' ?>" data-user-avatar-container>
            <img data-user-avatar class="akd-admin-avatar__img"
                src="<?= htmlspecialchars($user['avatar'] ?? '') ?>" alt=""
                <?= $hasAvatar ? '' : 'style="display:none"' ?>
            >
            <span data-user-avatar-initials class="akd-admin-avatar__initials"
                style="<?= $hasAvatar ? 'display:none;' : 'display:flex;' ?> background-color: <?= htmlspecialchars($color) ?>;"
            ><?= htmlspecialchars($initials) ?></span>
        </span>
        <?php
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | Anime Nigeria</title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="theme-color" content="#09090b">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
    <meta name="robots" content="noindex, nofollow">

    <link rel="icon" type="image/png" sizes="192x192" href="/uploads/logos/upscalemedia-transformed (1).png">
    <link rel="icon" type="image/png" sizes="32x32" href="/uploads/logos/upscalemedia-transformed (1).png">
    <link rel="apple-touch-icon" sizes="180x180" href="/uploads/logos/upscalemedia-transformed (1).png">

    <?php /* Runs before first paint so a hidden desktop sidebar never flashes open. */ ?>
    <script<?= Nonce::attr() ?>>
        (function () {
            try {
                var hidden = localStorage.getItem('akd-admin-sidebar-hidden:' + <?= (int) $user['id'] ?>) === '1';
                if (hidden && window.innerWidth >= 1024) {
                    document.documentElement.classList.add('akd-admin-sidebar-hidden');
                }
            } catch (e) {}
        })();
    </script>

    <?php vite('admin'); ?>

    <?php if (vite_is_dev()): ?>
        <script type="module"<?= Nonce::attr() ?>>
            import RefreshRuntime from 'http://127.0.0.1:5173/@react-refresh';
            RefreshRuntime.injectIntoGlobalHook(window);
            window.$RefreshReg$ = () => {};
            window.$RefreshSig$ = () => (type) => type;
            window.__vite_plugin_react_preamble_installed__ = true;
        </script>
    <?php endif; ?>
</head>
<body>
<div class="preloader" id="preloader">
    <svg class="preloader__wheel" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <defs>
            <linearGradient id="wheel-grad" x1="10%" y1="10%" x2="90%" y2="90%">
                <stop offset="0%" stop-color="#937C49" />
                <stop offset="50%" stop-color="#C5B173" />
                <stop offset="100%" stop-color="#A59152" />
            </linearGradient>
            <linearGradient id="rim-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#8A7B4B" />
                <stop offset="100%" stop-color="#5C502A" />
            </linearGradient>
        </defs>

        <g stroke="url(#rim-grad)" fill="url(#rim-grad)" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="50" cy="50" r="28" fill="none" stroke-width="6.5" />
            <path d="M50 12 L50 88 M12 50 L88 50 M23.13 23.13 L76.87 76.87 M23.13 76.87 L76.87 23.13" stroke-width="5.5" />
            <circle cx="50" cy="12" r="6" stroke="none" />
            <circle cx="50" cy="88" r="6" stroke="none" />
            <circle cx="12" cy="50" r="6" stroke="none" />
            <circle cx="88" cy="50" r="6" stroke="none" />
            <circle cx="23.13" cy="23.13" r="6" stroke="none" />
            <circle cx="76.87" cy="76.87" r="6" stroke="none" />
            <circle cx="23.13" cy="76.87" r="6" stroke="none" />
            <circle cx="76.87" cy="23.13" r="6" stroke="none" />
        </g>

        <g stroke="url(#wheel-grad)" fill="url(#wheel-grad)" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="50" cy="50" r="28" fill="none" stroke-width="4.5" />
            <path d="M50 12 L50 88 M12 50 L88 50 M23.13 23.13 L76.87 76.87 M23.13 76.87 L76.87 23.13" stroke-width="3.5" />
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

    <!-- Top bar: full width, global controls only -->
    <header class="akd-admin-topbar">
        <div class="akd-admin-topbar__start">
            <button type="button" class="akd-admin-icon-btn" id="sidebarToggle"
                aria-controls="akdSidebar" aria-expanded="true" aria-label="Hide sidebar"
            >
                <i class="fas fa-bars akd-admin-icon-btn__glyph akd-admin-icon-btn__glyph--mobile" aria-hidden="true"></i>
                <i class="bi bi-layout-sidebar-inset akd-admin-icon-btn__glyph akd-admin-icon-btn__glyph--desktop akd-admin-icon-btn__glyph--expanded" aria-hidden="true"></i>
                <i class="bi bi-layout-sidebar-inset-reverse akd-admin-icon-btn__glyph akd-admin-icon-btn__glyph--desktop akd-admin-icon-btn__glyph--collapsed" aria-hidden="true"></i>
            </button>

            <a href="/home" class="akd-admin-topbar__brand" aria-label="Anime Nigeria Admin">
                <img src="/uploads/logos/Landscape-Anime-Nigeria-Logo.png" alt="Anime Nigeria" class="akd-admin-topbar__logo">
            </a>
        </div>

        <div class="akd-admin-topbar__end">
            <div class="akd-admin-menu" data-admin-menu>
                <button type="button" class="akd-admin-icon-btn"
                    data-admin-menu-trigger aria-haspopup="true" aria-expanded="false"
                    aria-controls="adminNotifPanel"
                    aria-label="Notifications<?= $adminNotifUnread > 0 ? ', ' . $adminNotifUnread . ' unread' : '' ?>"
                >
                    <i class="fas fa-bell" aria-hidden="true"></i>
                    <?php if ($adminNotifUnread > 0): ?>
                        <span class="akd-admin-icon-btn__badge"><?= $adminNotifUnread > 9 ? '9+' : $adminNotifUnread ?></span>
                    <?php endif; ?>
                </button>

                <div class="akd-admin-menu__panel" id="adminNotifPanel" data-admin-menu-panel>
                    <div class="akd-admin-menu__head">Notifications</div>

                    <?php if (empty($adminNotifications)): ?>
                        <p class="akd-admin-menu__empty">No notifications yet</p>
                    <?php else: ?>
                        <ul class="akd-admin-menu__list">
                            <?php foreach ($adminNotifications as $notif): ?>
                                <?php $notifTag = !empty($notif['url']) ? 'a' : 'div'; ?>
                                <li>
                                    <<?= $notifTag ?>
                                        class="akd-admin-menu__notif<?= !empty($notif['unread']) ? ' akd-admin-menu__notif--unread' : '' ?>"
                                        <?= $notifTag === 'a' ? 'href="' . htmlspecialchars($notif['url']) . '"' : '' ?>
                                    >
                                        <span class="akd-admin-menu__notif-title"><?= htmlspecialchars($notif['title'] ?? '') ?></span>
                                        <span class="akd-admin-menu__notif-time"><?= htmlspecialchars($notif['time'] ?? '') ?></span>
                                    </<?= $notifTag ?>>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Account menu: desktop only. On mobile the account lives at the bottom of the sidebar. -->
            <div class="akd-admin-menu akd-admin-menu--account" data-admin-menu>
                <button type="button" class="akd-admin-topbar__avatar-btn"
                    data-admin-menu-trigger aria-haspopup="true" aria-expanded="false"
                    aria-controls="adminAccountPanel" aria-label="Account menu"
                >
                    <?php akd_admin_render_avatar($user, $userInitials, $avatarColor); ?>
                </button>

                <div class="akd-admin-menu__panel akd-admin-menu__panel--narrow" id="adminAccountPanel" data-admin-menu-panel>
                    <div class="akd-admin-menu__identity">
                        <span class="akd-admin-menu__identity-name" data-user-fullname><?= htmlspecialchars($user['fullname'] ?? 'User') ?></span>
                        <span class="akd-admin-menu__identity-handle" data-user-username>@<?= htmlspecialchars($user['username'] ?? 'admin') ?></span>
                    </div>

                    <div class="akd-admin-menu__divider"></div>

                    <a href="/admin/profile" class="akd-admin-menu__item">
                        <i class="fas fa-user" aria-hidden="true"></i> View Profile
                    </a>

                    <div class="akd-admin-menu__divider"></div>

                    <form action="/logout" method="POST" class="akd-admin-menu__logout-form" data-logout-form>
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="akd-admin-menu__item akd-admin-menu__item--danger">
                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i> Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile overlay (dims the page during a partial swipe) -->
    <div class="akd-admin-overlay" id="akdOverlay"></div>

    <!-- Sidebar: underneath the topbar on desktop, full-screen on mobile -->
    <nav class="akd-admin-sidebar" id="akdSidebar" data-user-id="<?= (int) $user['id'] ?>" aria-label="Admin navigation">
        <div class="akd-admin-sidebar__mobile-head">
            <a href="/home" class="akd-admin-sidebar__brand" aria-label="Anime Nigeria Admin">
                <img src="/uploads/logos/Landscape-Anime-Nigeria-Logo.png" alt="Anime Nigeria" class="akd-admin-topbar__logo">
            </a>

            <button type="button" class="akd-admin-icon-btn" id="sidebarClose" aria-label="Close menu">
                <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
        </div>

        <div class="akd-admin-sidebar__scroll">
            <div class="akd-admin-nav">
                <?php foreach ($navGroups as $groupName => $items): ?>
                    <div class="akd-admin-nav__group">
                        <div class="akd-admin-nav__group-label"><?= htmlspecialchars($groupName) ?></div>
                        <ul class="akd-admin-nav__list">
                            <?php akd_admin_render_nav_items($items, $currentPath); ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="akd-admin-sidebar__account">
            <a href="/admin/profile" class="akd-admin-sidebar__account-link">
                <?php akd_admin_render_avatar($user, $userInitials, $avatarColor, 'akd-admin-avatar--md'); ?>

                <div class="akd-admin-sidebar__account-info">
                    <span class="akd-admin-sidebar__account-name" data-user-fullname><?= htmlspecialchars($user['fullname'] ?? 'User') ?></span>
                    <span class="akd-admin-sidebar__account-handle" data-user-username>@<?= htmlspecialchars($user['username'] ?? 'admin') ?></span>
                </div>
            </a>

            <div class="akd-admin-sidebar__account-actions">
                <form action="/logout" method="POST" class="akd-admin-sidebar__account-form" data-logout-form>
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="akd-admin-icon-btn" aria-label="Sign out">
                        <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Pinned corner mask: cuts the large top-left radius out of .akd-content -->
    <div class="akd-admin-corner" aria-hidden="true"></div>
</div>