<?php
namespace App\Database\Migrations;
use PDO;

class CreateLoginSessionsTable {
    public function up(PDO $pdo): void {
        $pdo->exec("CREATE TABLE login_sessions 
            (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                session_id_hash CHAR(64) NOT NULL,
                auth_session_version INT UNSIGNED NOT NULL,
                device_type VARCHAR(50) NULL,
                brand VARCHAR(100) NULL,
                model VARCHAR(100) NULL,
                os VARCHAR(100) NULL,
                os_version VARCHAR(100) NULL,
                browser VARCHAR(100) NULL,
                browser_version VARCHAR(100) NULL,
                is_bot BOOLEAN NOT NULL DEFAULT FALSE,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                last_activity_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                revoked_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE KEY uq_login_sessions_session_id_hash (session_id_hash),
                INDEX idx_login_sessions_user_id (user_id),
                INDEX idx_login_sessions_user_active (user_id, revoked_at),
                INDEX idx_login_sessions_last_activity (last_activity_at),
                CONSTRAINT fk_login_sessions_user FOREIGN KEY (user_id)
                    REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS login_sessions");
    }
}