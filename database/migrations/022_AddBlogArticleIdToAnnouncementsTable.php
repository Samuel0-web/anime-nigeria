<?php
namespace App\Database\Migrations;
use PDO;

class AddBlogArticleIdToAnnouncementsTable {
    public function up(PDO $pdo): void {
        // url stays NOT NULL: it is the snapshot used when the article is deleted or unpublished.
        $pdo->exec("ALTER TABLE announcements
            ADD COLUMN blog_article_id BIGINT UNSIGNED NULL AFTER url,
            ADD INDEX idx_announcements_blog_article (blog_article_id),
            ADD CONSTRAINT fk_announcements_blog_article FOREIGN KEY (blog_article_id)
                REFERENCES blog_articles(id) ON DELETE SET NULL
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("ALTER TABLE announcements
            DROP FOREIGN KEY fk_announcements_blog_article,
            DROP INDEX idx_announcements_blog_article,
            DROP COLUMN blog_article_id
        ");
    }
}