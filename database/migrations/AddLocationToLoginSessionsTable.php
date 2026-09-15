<?php
namespace App\Database\Migrations;

use PDO;

class AddLocationToLoginSessionsTable {
    public function up(PDO $pdo): void {
        $pdo->exec("ALTER TABLE login_sessions
            ADD COLUMN location_city VARCHAR(100) NULL AFTER ip_address,
            ADD COLUMN location_region VARCHAR(100) NULL AFTER location_city,
            ADD COLUMN location_country VARCHAR(100) NULL AFTER location_region,
            ADD COLUMN location_country_code CHAR(2) NULL AFTER location_country
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("ALTER TABLE login_sessions
            DROP COLUMN location_city,
            DROP COLUMN location_region,
            DROP COLUMN location_country,
            DROP COLUMN location_country_code
        ");
    }
}