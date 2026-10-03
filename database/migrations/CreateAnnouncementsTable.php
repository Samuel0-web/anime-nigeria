<?php
namespace App\Database\Migrations;
use PDO;

class CreateAnnouncementsTable {
    public function up(PDO $pdo): void {
        $pdo->exec("CREATE TABLE announcements (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                category_id BIGINT UNSIGNED NOT NULL,
                title VARCHAR(120) NOT NULL,
                excerpt VARCHAR(240) NOT NULL,
                cta VARCHAR(30) NOT NULL,
                url VARCHAR(500) NOT NULL,
                featured TINYINT(1) NOT NULL DEFAULT 0,
                `date` DATE NOT NULL,
                image VARCHAR(500) NULL,
                image_alt VARCHAR(200) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_announcements_date (`date`, id),
                INDEX idx_announcements_category (category_id),
                CONSTRAINT fk_announcements_category FOREIGN KEY (category_id)
                    REFERENCES announcement_categories(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS announcements");
    }
}