<?php
namespace App\Database\Migrations;
use PDO;

class CreateRateLimitsTable {
    public function up(PDO $pdo): void {
        $pdo->exec("CREATE TABLE rate_limits 
            (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL,
                action VARCHAR(100) NOT NULL,
                attempts INT UNSIGNED NOT NULL DEFAULT 1,
                blocked_until TIMESTAMP NULL DEFAULT NULL,
                window_started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_ip_action (ip_address, action),
                INDEX idx_blocked (blocked_until)
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS rate_limits");
    }
}