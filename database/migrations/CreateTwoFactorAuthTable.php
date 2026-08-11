<?php
namespace App\Database\Migrations;
use PDO;

class CreateTwoFactorAuthTable {
    public function up(PDO $pdo): void {
        $pdo->exec("CREATE TABLE two_factor_auth (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                secret VARCHAR(255) NOT NULL,
                enabled_at TIMESTAMP NULL DEFAULT NULL,
                recovery_codes TEXT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,

                UNIQUE KEY uq_two_factor_user (user_id),

                CONSTRAINT fk_two_factor_user FOREIGN KEY (user_id) REFERENCES users(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS two_factor_auth");
    }
}