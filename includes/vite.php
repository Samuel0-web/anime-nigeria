<?php
declare(strict_types=1);

// Vite can emit CSS for imported chunks (shared modules).
// Recursively collect all CSS files so shared styles (e.g. Font Awesome)
// are loaded in production builds.
function collectCss(array $manifest, string $key, array &$css = []): void {
    if (!isset($manifest[$key])) {
        return;
    }

    $asset = $manifest[$key];

    foreach ($asset['css'] ?? [] as $file) {
        $css[$file] = true; // Prevent duplicates
    }

    foreach ($asset['imports'] ?? [] as $import) {
        collectCss($manifest, $import, $css);
    }
}

function vite(string $bundle = 'public'): void {
    $entries = [
        'public' => 'resources/js/public.js',
        'member' => 'resources/js/member.js',
        'admin' => 'resources/js/admin.js',
        'react' => 'resources/js/react/main.jsx',
    ];

    $devServer = 'http://127.0.0.1:5173';
    $connection = @fsockopen('127.0.0.1', 5173, $errno, $errstr, 0.2);

    if ($connection) {
        fclose($connection);

        echo <<<HTML
        <script type="module" src="{$devServer}/@vite/client"></script>
        <script type="module" src="{$devServer}/{$entries[$bundle]}"></script>

        HTML;
        return;
    }

    $manifestPath = __DIR__ . '/../public/build/.vite/manifest.json';

    if (!file_exists($manifestPath)) {
        throw new RuntimeException('Vite manifest not found. Run "npm run build".');
    }

    $manifest = json_decode(file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);

    if (!isset($manifest[$entries[$bundle]])) {
        throw new RuntimeException("Entry '{$entries[$bundle]}' not found.");
    }

    $asset = $manifest[$entries[$bundle]];

    // NEW: Collect CSS from the entry AND all imported chunks
    $cssFiles = [];
    collectCss($manifest, $entries[$bundle], $cssFiles);

    foreach (array_keys($cssFiles) as $css) {
        echo '<link rel="stylesheet" href="/build/' . htmlspecialchars($css) . '">' . PHP_EOL;
    }

    echo '<script type="module" src="/build/' . htmlspecialchars($asset['file']) . '"></script>';
}