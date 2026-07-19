<?php

require_once __DIR__ . "/../bootstrap.php";

use App\Database\Database;

try {

    Database::connection();

    echo "✅ Database connected successfully.";

} catch (Throwable $e) {

    echo $e->getMessage();

}