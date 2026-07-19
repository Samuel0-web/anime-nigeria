<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Current URI
|--------------------------------------------------------------------------
*/

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/*
|--------------------------------------------------------------------------
| Remove trailing slash
|--------------------------------------------------------------------------
*/

$uri = rtrim($uri, '/');

if ($uri === '') {
    $uri = '/';
}

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
*/

$routes = [
    '/'                         => 'index.php',
    '/test'                         => 'test.php',
    '/privacy'                  => 'privacy-policy.php',
    '/terms'                    => 'terms-of-use.php',

    '/login'                    => 'auth/login.php',
    '/join'                     => 'auth/register.php',
    '/username'                 => 'auth/username.php',
    '/forgot-password'          => 'auth/forgot-password.php',
    '/reset-password'           => 'auth/reset-password.php',

    '/our-community'            => 'community/community.php',
    '/community/gallery'        => 'community/gallery.php',
    '/community/challenges'     => 'community/challenges.php',
    '/community/awards'         => 'community/awards.php',
    '/community/honoured-ones'  => 'community/honoured-ones.php',
    '/community/whatsapp'       => 'community/whatsapp.php',

    '/overview'                 => 'trivia/trivia.php',
    '/trivia/leaderboard'       => 'trivia/leaderboard.php',
    '/trivia/winners'           => 'trivia/winners.php',

    '/blog'                     => 'blog.php',

    '/awards-overview'          => 'awards/awards.php',
    '/awards/categories'        => 'awards/categories.php',
    '/awards/nominations'       => 'awards/nominations.php',
    '/awards/voting'            => 'awards/voting.php',
    '/awards/winners'           => 'awards/winners.php',

    '/about'                    => 'about.php',
    '/contact'                  => 'contact.php',

    '/dashboard'                => 'member/dashboard.php',
];

/*
|--------------------------------------------------------------------------
| Route Matching
|--------------------------------------------------------------------------
*/

if (array_key_exists($uri, $routes)) {
    $page = __DIR__ . '/' . $routes[$uri];

    if (is_file($page)) {
        require $page;
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| 404
|--------------------------------------------------------------------------
*/

http_response_code(404);
require __DIR__ . '/404.php';
exit;