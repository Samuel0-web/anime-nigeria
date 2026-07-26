<?php
namespace App\Models;
use PDO;

class RateLimit {
    public function __construct(private PDO $db) {}

    public function clear(string $ip, string $action): bool {
        return $this->deleteByIpAndAction($ip, $action);
    }

    public function find(string $ip, string $action): array|false {
        $stmt = $this->db->prepare("SELECT * FROM rate_limits WHERE ip_address = :ip
            AND action = :action LIMIT 1
        ");

        $stmt->execute([
            'ip' => $ip,
            'action' => $action,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(string $ip, string $action, int $attempts, string $windowStartedAt,
        ?string $blockedUntil
    ): bool {

        $stmt = $this->db->prepare("INSERT INTO rate_limits
            (ip_address, action, attempts, blocked_until, window_started_at) VALUES (:ip, :action,
                :attempts, :blocked_until, :window_started_at
            )
        ");

        return $stmt->execute([
            'ip' => $ip,
            'action' => $action,
            'attempts' => $attempts,
            'blocked_until' => $blockedUntil,
            'window_started_at' => $windowStartedAt,
        ]);
    }

    public function update(int $id, int $attempts, string $windowStartedAt,
        ?string $blockedUntil
    ): bool {

        $stmt = $this->db->prepare("UPDATE rate_limits SET attempts = :attempts,
            window_started_at = :window_started_at, blocked_until = :blocked_until WHERE id = :id
        ");

        return $stmt->execute([
            'attempts' => $attempts,
            'window_started_at' => $windowStartedAt,
            'blocked_until' => $blockedUntil,
            'id' => $id,
        ]);
    }

    public function increment(int $id): bool {
        $stmt = $this->db->prepare("UPDATE rate_limits SET attempts = attempts + 1 WHERE id = :id");

        return $stmt->execute([
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM rate_limits WHERE id = :id");

        return $stmt->execute([
            'id' => $id,
        ]);
    }

    public function deleteByIpAndAction(string $ip, string $action): bool {
        $stmt = $this->db->prepare("DELETE FROM rate_limits WHERE ip_address = :ip
            AND action = :action
        ");

        return $stmt->execute([
            'ip' => $ip,
            'action' => $action,
        ]);
    }

    public function cleanup(): bool {
        $stmt = $this->db->prepare("DELETE FROM rate_limits WHERE
            window_started_at < DATE_SUB(NOW(), INTERVAL 7 DAY) AND (blocked_until IS NULL
            OR blocked_until < NOW())
        ");

        return $stmt->execute();
    }
}