<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Database\Database;
$db = Database::connection();

$db->exec("CREATE TABLE IF NOT EXISTS migrations (id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

$executed = $db->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
echo "<h2>Running migrations...</h2>";

foreach (glob(__DIR__ . '/migrations/*.php') as $file) {
    require_once $file;
    $class = 'App\\Database\\Migrations\\' . pathinfo($file, PATHINFO_FILENAME);
    $name = pathinfo($file, PATHINFO_FILENAME);

    if (!class_exists($class)) {
        continue;
    }

    if (in_array($name, $executed, true)) {
        echo "Skipping {$name}<br>";
        continue;
    }

    echo "Running {$name}<br>";
    $migration = new $class();
    $migration->up($db);
    $stmt = $db->prepare("INSERT INTO migrations (migration) VALUES (?)");
    $stmt->execute([$name]);
    echo "<hr>";
}

echo "<p>Done.</p>";