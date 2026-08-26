<?php
namespace App\Database\Migrations;

use PDO;

class AddLoginSessionIdToRememberTokensTable {
    public function up(PDO $pdo): void {
        $pdo->exec("ALTER TABLE remember_tokens
            ADD COLUMN login_session_id BIGINT UNSIGNED NULL AFTER user_id,
            ADD INDEX idx_login_session_id (login_session_id),
            ADD CONSTRAINT fk_remember_login_session FOREIGN KEY (login_session_id)
            REFERENCES login_sessions(id) ON DELETE CASCADE
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("ALTER TABLE remember_tokens
            DROP FOREIGN KEY fk_remember_login_session,
            DROP INDEX idx_login_session_id,
            DROP COLUMN login_session_id
        ");
    }
}