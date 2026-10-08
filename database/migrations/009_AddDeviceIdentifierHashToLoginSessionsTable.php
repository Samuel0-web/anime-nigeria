<?php
namespace App\Database\Migrations;

use PDO;

class AddDeviceIdentifierHashToLoginSessionsTable {
    public function up(PDO $pdo): void {
        $pdo->exec("ALTER TABLE login_sessions
            ADD COLUMN device_identifier_hash VARCHAR(64) NULL
            AFTER session_id_hash
        ");

        $pdo->exec("CREATE INDEX idx_login_sessions_user_device_revoked
            ON login_sessions (user_id, device_identifier_hash, revoked_at)
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP INDEX idx_login_sessions_user_device_revoked
            ON login_sessions
        ");

        $pdo->exec("ALTER TABLE login_sessions DROP COLUMN device_identifier_hash");
    }
}