<?php
namespace App\Database\Migrations;
use PDO;

class CreateUsersTable {
    public function up(PDO $pdo): void {
        $pdo->exec("CREATE TABLE users (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                fullname VARCHAR(100) NOT NULL,
                username VARCHAR(20) NULL UNIQUE,
                username_changed_at TIMESTAMP NULL DEFAULT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NULL,
                auth_provider ENUM('local', 'google') NOT NULL DEFAULT 'local',
                google_id VARCHAR(255) NULL UNIQUE,
                avatar VARCHAR(255) NULL,
                role ENUM('member', 'moderator', 'admin') NOT NULL DEFAULT 'member',
                email_verified_at TIMESTAMP NULL DEFAULT NULL,
                email_verification_token CHAR(64) NULL,
                email_verification_expires_at TIMESTAMP NULL DEFAULT NULL,
                email_verification_sent_at TIMESTAMP NULL,
                banned_at TIMESTAMP NULL DEFAULT NULL,
                ban_reason TEXT NULL,
                suspended_until TIMESTAMP NULL DEFAULT NULL,
                last_login_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                INDEX idx_username (username),
                INDEX idx_email (email),
                INDEX idx_role (role),
                INDEX idx_provider (auth_provider)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS users");
    }
}