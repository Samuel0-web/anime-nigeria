<?php
namespace App\Database\Migrations;
use PDO;

class CreateBlogArticlesTable {
    public function up(PDO $pdo): void {
        $pdo->exec("CREATE TABLE blog_articles (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                public_id CHAR(8) NOT NULL,
                slug VARCHAR(160) NULL,
                title VARCHAR(160) NOT NULL DEFAULT '',
                excerpt VARCHAR(240) NOT NULL DEFAULT '',
                category_id BIGINT UNSIGNED NULL,
                cover_image VARCHAR(500) NULL,
                cover_image_alt VARCHAR(200) NULL,
                content_html MEDIUMTEXT NOT NULL,
                word_count INT UNSIGNED NOT NULL DEFAULT 0,
                reading_time_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 1,
                status ENUM('draft','scheduled','published','archived') NOT NULL DEFAULT 'draft',
                published_at DATETIME NULL,
                archived_at DATETIME NULL,
                featured_position TINYINT UNSIGNED NULL,
                author_id BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_blog_articles_public_id (public_id),
                UNIQUE KEY uq_blog_articles_slug (slug),
                UNIQUE KEY uq_blog_articles_featured (featured_position),
                KEY idx_blog_articles_public (status, published_at, id),
                KEY idx_blog_articles_category (category_id, status, published_at),
                KEY idx_blog_articles_author (author_id),
                KEY idx_blog_articles_updated (updated_at),
                CONSTRAINT fk_blog_articles_category FOREIGN KEY (category_id)
                    REFERENCES blog_categories(id) ON DELETE RESTRICT,
                CONSTRAINT fk_blog_articles_author FOREIGN KEY (author_id)
                    REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS blog_articles");
    }
}