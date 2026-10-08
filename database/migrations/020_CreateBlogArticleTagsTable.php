<?php
namespace App\Database\Migrations;
use PDO;

class CreateBlogArticleTagsTable {
    public function up(PDO $pdo): void {
        $pdo->exec("CREATE TABLE blog_article_tags (
                article_id BIGINT UNSIGNED NOT NULL,
                tag_id BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (article_id, tag_id),
                KEY idx_blog_article_tags_tag (tag_id),
                CONSTRAINT fk_blog_article_tags_article FOREIGN KEY (article_id)
                    REFERENCES blog_articles(id) ON DELETE CASCADE,
                CONSTRAINT fk_blog_article_tags_tag FOREIGN KEY (tag_id)
                    REFERENCES blog_tags(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS blog_article_tags");
    }
}