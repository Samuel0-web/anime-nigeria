<?php
namespace App\Database\Migrations;
use PDO;

class CreateBlogTagsTable {
    public function up(PDO $pdo): void {
        // utf8mb4_unicode_ci makes the unique key case-insensitive ("Awards" = "awards").
        $pdo->exec("CREATE TABLE blog_tags (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(30) NOT NULL,
                UNIQUE KEY uq_blog_tags_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS blog_tags");
    }
}