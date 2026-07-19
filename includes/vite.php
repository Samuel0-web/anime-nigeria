<?php
declare(strict_types=1);

function vite(string $entry = 'resources/js/app.js'): void {
    $devServer = 'http://127.0.0.1:5173';

    // Check if Vite dev server is running
    $connection = @fsockopen('127.0.0.1', 5173, $errno, $errstr, 0.2);

    if ($connection) {
        fclose($connection);

        echo <<<HTML
        <script type="module" src="{$devServer}/@vite/client"></script>
        <script type="module" src="{$devServer}/{$entry}"></script>

        HTML;
        return;
    }

    $manifestPath = __DIR__ . '/../public/build/.vite/manifest.json';

    if (!file_exists($manifestPath)) {
        throw new RuntimeException(
            'Vite manifest not found. Run "npm run build".'
        );
    }

    $manifest = json_decode(
        file_get_contents($manifestPath),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    if (!isset($manifest[$entry])) {
        throw new RuntimeException("Entry '{$entry}' not found.");
    }

    $asset = $manifest[$entry];

    if (!empty($asset['css'])) {
        foreach ($asset['css'] as $css) {
            echo '<link rel="stylesheet" href="/build/' . htmlspecialchars($css) . '">' . PHP_EOL;
        }
    }

    echo '<script type="module" src="/build/' . htmlspecialchars($asset['file']) .
        '"></script>' . PHP_EOL;
}