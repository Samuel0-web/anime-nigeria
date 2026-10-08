<?php
namespace App\Database\Migrations;
use PDO;

class CreateBlogCategoriesTable {
    public function up(PDO $pdo): void {
        $pdo->exec("CREATE TABLE blog_categories (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                slug VARCHAR(60) NOT NULL,
                label VARCHAR(40) NOT NULL,
                description VARCHAR(240) NOT NULL DEFAULT '',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_blog_categories_slug (slug),
                UNIQUE KEY uq_blog_categories_label (label)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS blog_categories");
    }
}