<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Database\Database;

// Only run from CLI
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$db = Database::connection();

$db->exec("CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$executed = $db->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

// ANSI helpers
$c = fn(string $code, string $s) => "\033[{$code}m{$s}\033[0m";
$green  = fn($s) => $c('32', $s);
$yellow = fn($s) => $c('33', $s);
$red    = fn($s) => $c('31', $s);
$cyan   = fn($s) => $c('36', $s);
$bold   = fn($s) => $c('1',  $s);
$gray   = fn($s) => $c('90', $s);

// Colored line:  "  → name"  (icon + name same color)
$line = fn(string $glyph, callable $color, string $name) =>
    "  " . $color($glyph . " " . $name);

// Write without a trailing newline (used for the in-flight line)
$write = fn(string $s) => fwrite(STDOUT, $s);

// Overwrite the current line and finish it with a newline
$replace = fn(string $s) => fwrite(STDOUT, "\r\033[K" . $s . PHP_EOL);

echo $bold("Running migrations...") . PHP_EOL;
echo str_repeat('─', 50) . PHP_EOL;

$ran = 0;
$skipped = 0;

foreach (glob(__DIR__ . '/migrations/*.php') as $file) {
    require_once $file;

    $name  = pathinfo($file, PATHINFO_FILENAME);
    $class = 'App\\Database\\Migrations\\' . $name;

    if (!class_exists($class)) {
        echo $line('!', $yellow, $name) . $gray(" (class not found)") . PHP_EOL;
        continue;
    }

    if (in_array($name, $executed, true)) {
        echo $line('·', $cyan, $name) . $gray(" (already run)") . PHP_EOL;
        $skipped++;
        continue;
    }

    // Show "→ name" in place, no newline
    $write($line('→', $cyan, $name));

    try {
        $migration = new $class();
        $migration->up($db);

        $stmt = $db->prepare("INSERT INTO migrations (migration) VALUES (?)");
        $stmt->execute([$name]);

        // Overwrite the running line with the result
        $replace($line('+', $green, $name) . $gray(" applied"));
        $ran++;
    } catch (\Throwable $e) {
        $replace($line('x', $red, $name) . $gray(" failed"));
        fwrite(STDERR, $red("    " . $e->getMessage()) . PHP_EOL);
        exit(1);
    }
}

echo str_repeat('─', 50) . PHP_EOL;
echo $green("Done.") . " Ran: {$ran}, Skipped: {$skipped}" . PHP_EOL;