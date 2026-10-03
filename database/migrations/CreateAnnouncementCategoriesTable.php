<?php
namespace App\Database\Migrations;
use PDO;

class CreateAnnouncementCategoriesTable {
    public function up(PDO $pdo): void {
        $pdo->exec("CREATE TABLE announcement_categories (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(30) NOT NULL,
                accent VARCHAR(32) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_announcement_categories_name (name),
                UNIQUE KEY uq_announcement_categories_accent (accent)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS announcement_categories");
    }
}