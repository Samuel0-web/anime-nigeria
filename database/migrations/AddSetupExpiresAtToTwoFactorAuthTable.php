<?php
namespace App\Database\Migrations;
use PDO;

class AddSetupExpiresAtToTwoFactorAuthTable {
    public function up(PDO $pdo): void {
        $pdo->exec("ALTER TABLE two_factor_auth
            ADD COLUMN setup_expires_at TIMESTAMP NULL DEFAULT NULL AFTER enabled_at
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("ALTER TABLE two_factor_auth DROP COLUMN setup_expires_at");
    }
}