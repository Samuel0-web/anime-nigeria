<?php
namespace App\Services;
use App\Models\RateLimit;
use PDO;

class RateLimiter {
    private RateLimit $rateLimits;

    private const LIMITS = [
        'login' => [
            'max' => 5,
            'window' => 900,      // 15 minutes
            'block' => 900,      // 15 minutes
        ],

        'register' => [
            'max' => 5,
            'window' => 3600,     // 1 hour
            'block' => 700,      // 1 hour
        ],

        'google' => [
            'max' => 10,
            'window' => 3600,     // 1 hour
            'block' => 1800,      // 30 minutes
        ],

        'forgot_password' => [
            'max' => 3,
            'window' => 3600,     // 1 hour
            'block' => 3600,      // 1 hour
        ],

        'username' => [
            'max' => 30,
            'window' => 60,       // 1 minute
            'block' => 300,       // 5 minutes
        ],
    ];

    private string $ip;

    public function __construct(PDO $db) {
        $this->rateLimits = new RateLimit($db);
        $this->ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Check if the current IP is blocked for an action.
     */
    public function tooManyAttempts(string $action): bool {
        $record = $this->rateLimits->find($this->ip, $action);

        if ($record === false) {
            return false;
        }

        if ($record['blocked_until'] !== null && strtotime($record['blocked_until']) <= time()) {
            $this->rateLimits->clear($this->ip, $action);
            return false;
        }

        if ($record['blocked_until'] === null) {
            return false;
        }

        return strtotime($record['blocked_until']) > time();
    }

    /**
     * Record a failed attempt.
     */
    public function hit(string $action): void {
        $config = $this->config($action);
        $ip = $this->ip;
        $now = time();
        $record = $this->rateLimits->find($ip, $action);

        // No existing record → create one
        if ($record === false) {
            $this->rateLimits->create($ip, $action, 1, date('Y-m-d H:i:s', $now), null);
            return;
        }

        if ($record['blocked_until'] !== null && strtotime($record['blocked_until']) > $now) {
            return;
        }

        $windowStarted = strtotime($record['window_started_at']);

        // Window expired → reset attempts
        if (($now - $windowStarted) >= $config['window']) {
            $this->rateLimits->update((int) $record['id'], 1, date('Y-m-d H:i:s', $now), null);
            return;
        }

        // Increment within current window
        if ((int) $record['attempts'] + 1 >= $config['max']) {
            $this->rateLimits->update((int) $record['id'], (int) $record['attempts'] + 1,
                $record['window_started_at'], date('Y-m-d H:i:s', $now + $config['block'])
            );

            return;
        }

        $this->rateLimits->increment((int) $record['id']);
    }

    /**
     * Clear rate-limit record after success.
     */
    public function clear(string $action): void {
        $this->rateLimits->clear($this->ip, $action);
    }

    /**
     * Seconds remaining until unblock.
     */
    public function remainingSeconds(string $action): int {
        $record = $this->rateLimits->find($this->ip, $action);

        if ($record === false || $record['blocked_until'] === null) {
            return 0;
        }

        $remaining = strtotime($record['blocked_until']) - time();
        return max(0, $remaining);
    }

    public function remainingTime(string $action): string {
        $seconds = $this->remainingSeconds($action);

        if ($seconds <= 0) {
            return '0 seconds';
        }

        if ($seconds < 60) {
            return $seconds . ' second' . ($seconds === 1 ? '' : 's');
        }

        if ($seconds < 3600) {
            $minutes = intdiv($seconds, 60);
            $remainingSeconds = $seconds % 60;

            return $remainingSeconds === 0 ? "{$minutes} minute" . ($minutes === 1 ? '' : 's')
                : "{$minutes} minute" . ($minutes === 1 ? '' : 's') .
                " {$remainingSeconds} second" . ($remainingSeconds === 1 ? '' : 's')
            ;
        }

        $hours = intdiv($seconds, 3600);
        $remainingMinutes = intdiv($seconds % 3600, 60);

        return $remainingMinutes === 0 ? "{$hours} hour" . ($hours === 1 ? '' : 's')
            : "{$hours} hour" . ($hours === 1 ? '' : 's') .
            " {$remainingMinutes} minute" . ($remainingMinutes === 1 ? '' : 's')
        ;
    }

    /**
     * Get current attempt count.
     */
    public function attempts(string $action): int {
        $record = $this->rateLimits->find($this->ip, $action);

        if ($record === false) {
            return 0;
        }

        if (time() - strtotime($record['window_started_at']) >= $this->config($action)['window']) {
            return 0;
        }

        return (int) $record['attempts'];
    }

    /**
     * Cleanup stale records.
     */
    public function cleanup(): void {
        $this->rateLimits->cleanup();
    }

    /**
     * Get configuration for an action.
     */
    private function config(string $action): array {
        return self::LIMITS[$action] ?? throw new \InvalidArgumentException(
            "Unknown rate-limit action: {$action}"
        );
    }
}