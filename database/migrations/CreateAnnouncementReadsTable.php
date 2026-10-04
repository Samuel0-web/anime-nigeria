<?php
namespace App\Database\Migrations;
use PDO;

class CreateAnnouncementReadsTable {
    public function up(PDO $pdo): void {
        $pdo->exec("CREATE TABLE announcement_reads (
                user_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
                last_read_announcement_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_announcement_reads_user FOREIGN KEY (user_id)
                    REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS announcement_reads");
    }
}