<?php
$pageTitle       = $pageTitle       ?? "Anime Nigeria — Nigeria's Home for Otakus";
$pageDescription = $pageDescription ?? "Anime Nigeria is Nigeria's anime and Japanese culture community — discussions, challenges, trivia, awards, blogs, and events for otaku across the country.";

$navGroups = [
    [
        'label' => 'Home',
        'href'  => '/',
    ],
    [
        'label' => 'Community',
        'items' => [
            ['label' => 'Our Community',      'href' => '/our-community'],
            ['label' => 'Gallery',            'href' => '/community/gallery'],
            ['label' => 'Anime Challenges',   'href' => '/community/challenges'],
            ['label' => 'Community Awards',   'href' => '/community/awards'],
            ['label' => 'The Honoured Ones',  'href' => '/community/honoured-ones'],
        ],
    ],
    [
        'label' => 'Trivia',
        'items' => [
            ['label' => 'Anime Trivia',       'href' => '/overview'],
            ['label' => 'Trivia Leaderboard', 'href' => '/trivia/leaderboard'],
            ['label' => 'Trivia Winners',     'href' => '/trivia/winners'],
        ],
    ],
    [
        'label' => 'Blog',
        'href'  => '/blog',
    ],
    [
        'label' => 'Anime Awards',
        'badge' => 'Coming Soon',
        'items' => [
            ['label' => 'Overview',     'href' => '/awards-overview'],
            ['label' => 'Categories',   'href' => '/awards/categories'],
            ['label' => 'Nominations',  'href' => '/awards/nominations'],
            ['label' => 'Voting',       'href' => '/awards/voting'],
            ['label' => 'Winners',      'href' => '/awards/winners'],
        ],
    ],
    [
        'label' => 'About',
        'items' => [
            ['label' => 'About Anime Nigeria', 'href' => '/about'],
            ['label' => 'Contact',             'href' => '/contact'],
        ],
    ],
];

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
require __DIR__ . '/vite.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
  <meta name="theme-color" content="#000000">

  <link rel="icon" type="image/png" sizes="192x192" href="/uploads/upscalemedia-transformed (1).png">
  <link rel="icon" type="image/png" sizes="32x32" href="/uploads/upscalemedia-transformed (1).png">
  <link rel="apple-touch-icon" sizes="180x180" href="/uploads/upscalemedia-transformed (1).png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

  <?php vite(); ?>
</head>
<body>

<a class="an-skip-link" href="#main-content">Skip to content</a>

<header class="an-header" id="site-header">
  <div class="an-header__inner">

    <a href="/" class="an-header__logo" aria-label="Anime Nigeria — Home">
      <img src="/uploads/Landscape-Anime-Nigeria-Logo.png" alt="Anime Nigeria — Anime &amp; Japanese Culture" class="an-header__logo-img">
    </a>

    <nav class="an-nav" aria-label="Primary">
      <ul class="an-nav__list">
        <?php foreach ($navGroups as $group): ?>
          <?php if (empty($group['items'])): ?>
            <li>
              <a href="<?= htmlspecialchars($group['href']) ?>" class="an-nav__link<?= $currentPath === $group['href'] ? ' is-active' : '' ?>">
                <?= htmlspecialchars($group['label']) ?>
              </a>
            </li>
          <?php else: ?>
            <li class="an-nav__item--dropdown">
              <button type="button" class="an-nav__link an-nav__trigger<?= in_array($currentPath, array_column($group['items'], 'href'), true) ? ' is-active' : '' ?>" aria-haspopup="true"
                aria-expanded="false"
              >
                <?= htmlspecialchars($group['label']) ?>
                <?php if (!empty($group['badge'])): ?>
                  <span class="an-badge"><?= htmlspecialchars($group['badge']) ?></span>
                <?php endif; ?>
                <svg class="an-nav__caret" viewBox="0 0 10 6" fill="none" aria-hidden="true">
                  <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
              <ul class="an-dropdown">
                <?php foreach ($group['items'] as $item): ?>
                  <li>
                    <a href="<?= htmlspecialchars($item['href']) ?>" class="an-dropdown__link<?= $currentPath === $item['href'] ? ' is-active' : '' ?>">
                      <?= htmlspecialchars($item['label']) ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="an-header__actions">
      <a href="/login" class="an-btn an-btn--ghost">Log In</a>
      <a href="/join" class="an-btn an-btn--primary">Join Community</a>
      <button type="button" class="an-menu-toggle" id="nav-toggle" aria-expanded="false"
        aria-controls="an-mobile-nav" aria-label="Toggle menu"
      >
        <span></span><span></span><span></span>
      </button>
    </div>

  </div>

  <nav class="an-mobile-nav" id="mobile-nav" aria-label="Mobile">
    <ul class="an-mobile-nav__list">
      <?php foreach ($navGroups as $i => $group): ?>
        <?php if (empty($group['items'])): ?>
          <li>
            <a href="<?= htmlspecialchars($group['href']) ?>" class="an-mobile-nav__link<?= $currentPath === $group['href'] ? ' is-active' : '' ?>">
              <?= htmlspecialchars($group['label']) ?>
            </a>
          </li>
        <?php else: ?>
          <li class="an-mobile-nav__group">
            <button type="button" class="an-mobile-nav__link an-mobile-nav__trigger<?= in_array($currentPath, array_column($group['items'], 'href'), true) ? ' is-active' : '' ?>" aria-expanded="false"
              aria-controls="mobile-group-<?= $i ?>"
            >
              <span>
                <?= htmlspecialchars($group['label']) ?>
                <?php if (!empty($group['badge'])): ?>
                  <span class="an-badge"><?= htmlspecialchars($group['badge']) ?></span>
                <?php endif; ?>
              </span>
              <svg class="an-nav__caret" viewBox="0 0 10 6" fill="none" aria-hidden="true">
                <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
            <ul class="an-mobile-nav__submenu" id="mobile-group-<?= $i ?>">
              <?php foreach ($group['items'] as $item): ?>
                <li>
                  <a href="<?= htmlspecialchars($item['href']) ?>" class="an-mobile-nav__sublink<?= $currentPath === $item['href'] ? ' is-active' : '' ?>">
                    <?= htmlspecialchars($item['label']) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
      <li class="an-mobile-nav__cta-row">
        <a href="/login" class="an-btn an-btn--ghost">Log In</a>
        <a href="/join" class="an-btn an-btn--primary">Join Community</a>
      </li>
    </ul>
  </nav>
</header>