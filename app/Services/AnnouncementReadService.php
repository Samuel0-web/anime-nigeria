<?php
namespace App\Services;

use App\Database\Database;
use App\Models\AnnouncementRead;

/**
 * Member-specific announcement read state. Deliberately uncached: the shared
 * announcement cache (AnnouncementService) holds content only.
 */
final class AnnouncementReadService {
    public function __construct(private AnnouncementRead $reads) {}

    public static function make(): self {
        return new self(new AnnouncementRead(Database::connection()));
    }

    public function unreadCount(int $userId): int {
        return $this->reads->unreadCount($userId);
    }

    public function markAllRead(int $userId): void {
        $this->reads->markAllRead($userId);
    }
}