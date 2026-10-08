<?php
namespace App\Database\Migrations;
use PDO;

class CreatePasswordResetTokensTable {
    public function up(PDO $pdo): void {
        $pdo->exec("CREATE TABLE password_reset_tokens (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                token_hash CHAR(64) NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                used_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_password_reset_user FOREIGN KEY (user_id) REFERENCES users(id)
                    ON DELETE CASCADE,

                INDEX idx_user (user_id),
                INDEX idx_token (token_hash),
                INDEX idx_expires (expires_at)
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS password_reset_tokens");
    }
}