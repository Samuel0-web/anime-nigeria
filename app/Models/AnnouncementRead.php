<?php
namespace App\Models;
use PDO;

class AnnouncementRead {
    public function __construct(private PDO $db) {}

    public function unreadCount(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM announcements a
            WHERE a.id > COALESCE(
                (SELECT r.last_read_announcement_id FROM announcement_reads r
                    WHERE r.user_id = :read_user),
                (SELECT COALESCE(MAX(b.id), 0) FROM announcements b
                    WHERE b.created_at <= (SELECT u.created_at FROM users u
                        WHERE u.id = :joined_user)),
                0
            )
        ");
        $stmt->bindValue(':read_user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':joined_user', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Everything that exists right now becomes read. The watermark is taken in
     * SQL at the moment of the write, so an announcement published a moment
     * later keeps a higher id and stays unread. It never moves backwards.
     */
    public function markAllRead(int $userId): void {
        $stmt = $this->db->prepare("INSERT INTO announcement_reads
                (user_id, last_read_announcement_id)
            SELECT :user_id, COALESCE(MAX(id), 0) FROM announcements
            ON DUPLICATE KEY UPDATE last_read_announcement_id =
                GREATEST(last_read_announcement_id, VALUES(last_read_announcement_id))
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }
}