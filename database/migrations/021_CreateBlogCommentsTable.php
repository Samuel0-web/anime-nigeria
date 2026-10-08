<?php
namespace App\Database\Migrations;
use PDO;

class CreateBlogCommentsTable {
    public function up(PDO $pdo): void {
        // parent_id  = the top-level comment of the thread (NULL on a top-level comment).
        // reply_to_id = the specific reply being answered (NULL when replying to the top-level comment).
        // parent_id cascades one level only (a top-level delete removes its whole thread).
        // reply_to_id is SET NULL on purpose: InnoDB cascades stop at depth 15, so subtree
        // deletes are done explicitly by the service instead.
        // created_at is DATETIME in UTC, written with UTC_TIMESTAMP().
        $pdo->exec("CREATE TABLE blog_comments (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                article_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                parent_id BIGINT UNSIGNED NULL,
                reply_to_id BIGINT UNSIGNED NULL,
                content VARCHAR(2000) NOT NULL,
                created_at DATETIME NOT NULL,
                KEY idx_blog_comments_thread (article_id, parent_id, id),
                KEY idx_blog_comments_replies (parent_id, id),
                KEY idx_blog_comments_reply_to (reply_to_id),
                KEY idx_blog_comments_user (user_id, created_at),
                CONSTRAINT fk_blog_comments_article FOREIGN KEY (article_id)
                    REFERENCES blog_articles(id) ON DELETE CASCADE,
                CONSTRAINT fk_blog_comments_user FOREIGN KEY (user_id)
                    REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_blog_comments_parent FOREIGN KEY (parent_id)
                    REFERENCES blog_comments(id) ON DELETE CASCADE,
                CONSTRAINT fk_blog_comments_reply_to FOREIGN KEY (reply_to_id)
                    REFERENCES blog_comments(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS blog_comments");
    }
}