<?php
declare(strict_types=1);

/**
 * Pure presentation helpers for the Honoured Ones archive. Reuses
 * akd_anca_winner_for_category() and akd_anca_stage_status() from
 * community-awards-support.php rather than duplicating winner
 * resolution logic, since the current edition's winners come from the
 * same underlying category/winnerId data the Winners page already
 * reads.
 */

if (!function_exists('akd_honoured_ones_editions')) {
    /**
     * Builds the full archive: past editions from honoured-ones-data.php,
     * plus the current ANCA edition when its winners have actually been
     * decided, sorted newest first. The current edition is never stored
     * here as static data, it is always read live so this archive can
     * never drift out of sync with the actual current-edition winners.
     *
     * @return list<array{year: int, edition: string, winners: list<array{category: string, fullname: string, username: string, accent: string, reason?: string|null}>}>
     */
    function akd_honoured_ones_editions(): array
    {
        require_once __DIR__ . '/community-awards-support.php';

        $anca     = require __DIR__ . '/community-awards-data.php';
        $editions = require __DIR__ . '/honoured-ones-data.php';

        $winnersStatus = akd_anca_stage_status('winners', $anca['phase']);

        if ($winnersStatus['state'] === 'available') {
            $currentWinners = [];

            foreach ($anca['categories'] as $category) {
                $winner = akd_anca_winner_for_category($category);

                if ($winner !== null) {
                    $currentWinners[] = [
                        'category' => $category['name'],
                        'fullname' => $winner['fullname'],
                        'username' => $winner['username'],
                        'accent'   => $winner['accent'],
                        'reason'   => $winner['reason'] ?? null,
                    ];
                }
            }

            if ($currentWinners !== []) {
                $editions[] = [
                    'year'    => $anca['edition'],
                    'edition' => 'ANCA ' . $anca['edition'],
                    'winners' => $currentWinners,
                ];
            }
        }

        usort($editions, static fn(array $a, array $b): int => $b['year'] <=> $a['year']);

        return $editions;
    }
}