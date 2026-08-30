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
*/
$pathSegments = array_values(array_filter( explode('/', trim($uri, '/')),
    static fn(string $segment): bool => $segment !== ''
));

$apiIndex = array_search('api', $pathSegments, true);

if ($apiIndex !== false) {
    $method = $_SERVER['REQUEST_METHOD'];
    $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE',];

    /*
    |--------------------------------------------------------------------------
    | Validate HTTP method
    |--------------------------------------------------------------------------
    */

    if (!in_array($method, $allowedMethods, true)) {
        http_response_code(405);
        header('Allow: GET, POST, PUT, PATCH, DELETE');
        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed.',
        ]);

        exit;
    }


    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        VerifyCsrf::handle();
    }

    /*
    |--------------------------------------------------------------------------
    | API path
    |--------------------------------------------------------------------------
    */

    $directory = array_slice($pathSegments, 0, $apiIndex);
    $endpoint  = array_slice($pathSegments, $apiIndex + 1);

    /*
    |--------------------------------------------------------------------------
    | API endpoint is required
    |--------------------------------------------------------------------------
    */

    if (empty($endpoint)) {
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
    | Validate URL segments
    |--------------------------------------------------------------------------
    */

    foreach (array_merge($directory, $endpoint) as $segment) {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $segment)) {
            http_response_code(400);
            header('Content-Type: application/json');

            echo json_encode([
                'success' => false,
                'message' => 'Invalid API route.',
            ]);

            exit;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve API resource
    |
    | The resource file handles its own sub-resource routing.
    |--------------------------------------------------------------------------
    */

    $resource = $endpoint[0];

    $apiFile = __DIR__ . '/' . implode('/', $directory) . '/api/' . $resource
        . '.php';

    if (is_file($apiFile)) {

        /*
        |--------------------------------------------------------------------------
        | Make API path available to the resource
        |--------------------------------------------------------------------------
        */

        $apiPath = array_slice($endpoint, 1);
        require $apiFile;
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | API endpoint not found
    |--------------------------------------------------------------------------
    */

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
    '/phpinfo'                                     => 'phpinfo.php',
    '/privacy'                                  => 'privacy-policy.php',
    '/terms'                                    => 'terms-of-use.php',

    '/login'                                    => 'auth/login.php',
    '/join'                                     => 'auth/register.php',
    '/join/google'                              => 'auth/google-auth/join.php',
    '/auth/google'                              => 'auth/google-auth/google.php',
    '/auth/google/callback'                     => 'auth/google-auth/callback.php',
    '/auth/username'                            => 'auth/username.php',
    '/auth/verify'                              => 'auth/verify.php',
    '/auth/2fa'                                 => 'auth/2fa.php',
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
    '/member/awards/overview'                   => 'member/awards/awards.php',
    '/member/awards/nominations'                => 'member/awards/nominations.php',
    '/member/awards/voting'                     => 'member/awards/voting.php',
    '/member/awards/winners'                    => 'member/awards/winners.php',
    '/member/announcements'                     => 'member/announcements.php',
    '/member/trivia'                            => 'member/trivia.php',
    '/member/leaderboard'                       => 'member/leaderboard.php',
    '/member/gallery'                           => 'member/gallery.php',
    '/member/blogs'                             => 'member/blogs.php',
    '/member/settings'                          => 'member/settings.php',
    '/member/help'                              => 'member/help-centre.php',

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

/*
 * Determine which section of the application the
 * requested URI belongs to.
 */
$firstSegment = $pathSegments[0] ?? '';

switch ($firstSegment) {
    case 'member':
        $errorPage = __DIR__ . '/member/404.php';
        break;

    case 'admin':
        $errorPage = __DIR__ . '/admin/404.php';
        break;

    default:
        $errorPage = __DIR__ . '/404.php';
        break;
}

if (is_file($errorPage)) {
    require $errorPage;
    exit;
}

/*
 * Absolute fallback in case a section-specific
 * 404 page is missing.
 */
require __DIR__ . '/404.php';
exit;