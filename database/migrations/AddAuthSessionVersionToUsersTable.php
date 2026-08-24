<?php
namespace App\Database\Migrations;
use PDO;

class AddAuthSessionVersionToUsersTable {
    public function up(PDO $pdo): void {
        $pdo->exec("ALTER TABLE users
            ADD COLUMN auth_session_version INT UNSIGNED NOT NULL DEFAULT 1
            AFTER last_login_at
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("ALTER TABLE users DROP COLUMN auth_session_version");
    }
}