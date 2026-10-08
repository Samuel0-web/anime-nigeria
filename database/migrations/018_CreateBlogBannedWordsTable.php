<?php
namespace App\Database\Migrations;
use PDO;

class CreateBlogBannedWordsTable {
    public function up(PDO $pdo): void {
        // utf8mb4_unicode_ci makes the unique key case-insensitive ("Heck" = "heck").
        $pdo->exec("CREATE TABLE blog_banned_words (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                word VARCHAR(60) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_blog_banned_words_word (word)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS blog_banned_words");
    }
}