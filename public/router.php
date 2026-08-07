<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';
use App\Http\Middleware\VerifyCsrf;
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
| API Routing
|--------------------------------------------------------------------------
|
*/
if (str_contains($uri, '/api/')) {
    $method = $_SERVER['REQUEST_METHOD'];

    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        VerifyCsrf::handle();
    }

    // Prevent directory traversal
    if (preg_match('#\.\.#', $uri)) {
        http_response_code(400);
        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'message' => 'Invalid API route.',
        ]);

        exit;
    }

    $apiFile = __DIR__ . $uri . '.php';

    if (is_file($apiFile)) {
        require $apiFile;
        exit;
    }

    http_response_code(404);
    header('Content-Type: application/json');

    echo json_encode([
        'success' => false,
        'message' => 'API endpoint not found.',
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
*/

$routes = [
    '/'                                         => 'index.php',
    '/test'                                     => 'test.php',
    '/privacy'                                  => 'privacy-policy.php',
    '/terms'                                    => 'terms-of-use.php',

    '/login'                                    => 'auth/login.php',
    '/join'                                     => 'auth/register.php',
    '/join/google'                              => 'auth/google-auth/join.php',
    '/auth/google'                              => 'auth/google-auth/google.php',
    '/auth/google/callback'                     => 'auth/google-auth/callback.php',
    '/auth/username'                            => 'auth/username.php',
    '/auth/verify'                              => 'auth/verify.php',
    '/auth/forgot-password'                     => 'auth/forgot-password.php',
    '/auth/reset-password'                      => 'auth/reset-password.php',
    '/logout'                                   => 'auth/logout.php',

    '/our-community'                            => 'community/community.php',
    '/community/gallery'                        => 'community/gallery.php',
    '/community/challenges'                     => 'community/challenges.php',
    '/community/awards'                         => 'community/awards.php',
    '/community/honoured-ones'                  => 'community/honoured-ones.php',
    '/community/whatsapp'                       => 'community/whatsapp.php',

    '/overview'                                 => 'trivia/trivia.php',
    '/trivia/leaderboard'                       => 'trivia/leaderboard.php',
    '/trivia/winners'                           => 'trivia/winners.php',

    '/blog'                                     => 'blogs/index.php',

    '/awards-overview'                          => 'awards/awards.php',
    '/awards/categories'                        => 'awards/categories.php',
    '/awards/nominations'                       => 'awards/nominations.php',
    '/awards/voting'                            => 'awards/voting.php',
    '/awards/winners'                           => 'awards/winners.php',

    '/about'                                    => 'about.php',
    '/contact'                                  => 'contact.php',

    '/dashboard'                                => 'member/dashboard.php',
    '/member/profile'                           => 'member/profile.php',
    '/member/player/{username}'                 => 'member/player.php',
    '/member/player/{username}/achievements'    => 'member/player-achievements.php',
    '/member/achievements'                      => 'member/achievements.php',
    '/member/awards'                            => 'member/awards.php',
    '/member/voting'                            => 'member/voting.php',
    '/member/trivia'                            => 'member/trivia.php',
    '/member/leaderboard'                       => 'member/leaderboard.php',
    '/member/gallery'                           => 'member/gallery.php',
    '/member/blogs'                             => 'member/blogs.php',
    '/member/settings'                          => 'member/settings.php',

    '/home'                                     => 'admin/index.php',
];

/*
|--------------------------------------------------------------------------
| Route Matching
|--------------------------------------------------------------------------
*/

foreach ($routes as $route => $file) {
    $parameterNames = [];

    // Find route parameters such as {username} or {slug}
    $pattern = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
        function ($match) use (&$parameterNames) {
            $parameterNames[] = $match[1];
            return '([^/]+)';
        }, $route
    );

    // Escape the route for regex, except our dynamic parameters
    $pattern = preg_quote($pattern, '#');
    $pattern = str_replace('\(\[\^/\]\+\)', '([^/]+)', $pattern);
    $pattern = '#^' . $pattern . '$#';

    if (preg_match($pattern, $uri, $matches)) {
        array_shift($matches);
        $page = __DIR__ . '/' . $file;

        if (!is_file($page)) {
            continue;
        }

        // Make route parameters available to the page
        foreach ($parameterNames as $index => $name) {
            ${$name} = urldecode($matches[$index]);
        }

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