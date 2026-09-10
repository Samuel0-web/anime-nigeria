<?php
declare(strict_types=1);

/**
 * Pure presentation helpers for ANCA (Anime Nigeria Community Awards).
 * No I/O beyond requiring players.php for the nomination member pool.
 * Kept separate from awards-support.php (ANAA) since the two award
 * systems are independent and shouldn't share a source of truth.
 */

if (!function_exists('akd_anca_phase_config')) {
    /**
     * Fetch the current phase's hero copy, falling back to
     * 'coming_soon' if 'phase' is ever set to something unrecognised.
     *
     * @param array<string, array<string, mixed>> $phases
     * @return array<string, mixed>
     */
    function akd_anca_phase_config(array $phases, string $currentPhase): array
    {
        return $phases[$currentPhase] ?? $phases['coming_soon'];
    }
}

if (!function_exists('akd_anca_stage_order')) {
    /**
     * @return list<string>
     */
    function akd_anca_stage_order(): array
    {
        return ['nominations', 'voting', 'winners'];
    }
}

if (!function_exists('akd_anca_stage_status')) {
    /**
     * Resolve a single stage's chronological status against the current
     * phase. This is the one place stage state is decided, so
     * how-it-works.php, cta.php, and the Nominations controller never
     * disagree about whether nominations are open, closed, or not open
     * yet.
     *
     * Nominations and voting move through: not_open, open, closed.
     * Winners moves through: not_available, available (and stays
     * available once reached, covering an eventual "completed" state
     * without needing a separate phase for it).
     *
     * @return array{state: string, label: string}
     */
    function akd_anca_stage_status(string $stage, string $currentPhase): array
    {
        $order        = akd_anca_stage_order();
        $stageIndex   = array_search($stage, $order, true);
        $currentIndex = $currentPhase === 'coming_soon' ? -1 : array_search($currentPhase, $order, true);

        if ($stageIndex === false || $currentIndex === false) {
            return ['state' => 'not_open', 'label' => 'Not open yet'];
        }

        if ($stage === 'winners') {
            return $currentIndex >= $stageIndex
                ? ['state' => 'available', 'label' => 'Available']
                : ['state' => 'not_available', 'label' => 'Not available yet'];
        }

        if ($stageIndex < $currentIndex) {
            return ['state' => 'closed', 'label' => 'Closed'];
        }

        if ($stageIndex === $currentIndex) {
            return ['state' => 'open', 'label' => 'Open'];
        }

        return ['state' => 'not_open', 'label' => 'Not open yet'];
    }
}

if (!function_exists('akd_anca_closed_stage_redirect')) {
    /**
     * When nominations have closed, resolve which stage the member
     * should be pointed toward next, voting if it is currently open,
     * otherwise winners. Reuses 'howItWorks' rather than a second list
     * of routes, so there is one place those links live.
     *
     * @param list<array{key: string, label: string, link: string, cta: string}> $howItWorks
     * @return array{label: string, link: string, cta: string}
     */
    function akd_anca_closed_stage_redirect(array $howItWorks, string $currentPhase): array
    {
        foreach (['voting', 'winners'] as $key) {
            $status = akd_anca_stage_status($key, $currentPhase);

            if (in_array($status['state'], ['open', 'available'], true)) {
                foreach ($howItWorks as $stage) {
                    if ($stage['key'] === $key) {
                        return $stage;
                    }
                }
            }
        }

        // Fallback: point at winners even if not available yet, since
        // nominations having closed means that's the only place left to go.
        foreach ($howItWorks as $stage) {
            if ($stage['key'] === 'winners') {
                return $stage;
            }
        }

        return ['label' => 'ANCA Overview', 'link' => '/member/community/awards/overview', 'cta' => 'Back to Overview'];
    }
}

if (!function_exists('akd_anca_format_date')) {
    /**
     * Format a Y-m-d date string from community-awards-data.php for
     * display. Falls back to the raw string if it can't be parsed.
     */
    function akd_anca_format_date(string $date): string
    {
        $timestamp = strtotime($date);

        return $timestamp ? date('M j', $timestamp) : $date;
    }
}

if (!function_exists('akd_anca_nomination_members')) {
    /**
     * Community members available to nominate, sourced from the same
     * roster the leaderboard already uses (see players.php), so ANCA
     * never maintains a second, parallel member list. Only the fields
     * the nomination search needs are exposed, no XP, rank, or other
     * leaderboard-specific data. 'accent' cycles through the same six
     * ANCA accent colours used everywhere else on the award (see
     * $anca-accents in _overview.scss) for the initials monogram shown
     * next to each name, kept in sync manually since PHP has no access
     * to the SCSS map.
     *
     * @return list<array{id: string, fullname: string, username: string, accent: string}>
     */
    function akd_anca_nomination_members(): array
    {
        require_once __DIR__ . '/players.php';

        $accents = ['gold', 'teal', 'sakura', 'violet', 'ember', 'crimson'];
        $roster  = akdLeaderboardRoster();
        $members = [];

        foreach ($roster as $i => $player) {
            $members[] = [
                'id'       => (string) $player['id'],
                'fullname' => $player['fullname'],
                'username' => $player['username'],
                'accent'   => $accents[$i % count($accents)],
            ];
        }

        return $members;
    }
}


if (!function_exists('akd_anca_winner_for_category')) {
    /**
     * Resolve a category's current winner by looking up its 'winnerId'
     * inside its own 'nominees' list, mirroring
     * akd_award_winner_for_category() in awards-support.php (ANAA), so
     * the winner's name, username, and recognition text never need to
     * be duplicated as separate data. Returns null if the category has
     * no winnerId, or defensively if the id doesn't match anything in
     * 'nominees'.
     *
     * @param array{winnerId?: string|null, nominees: list<array{id: string, fullname: string, username: string, accent: string, reason?: string}>} $category
     * @return array{id: string, fullname: string, username: string, accent: string, reason?: string}|null
     */
    function akd_anca_winner_for_category(array $category): ?array {
        $winnerId = $category['winnerId'] ?? null;

        if ($winnerId === null) {
            return null;
        }

        foreach ($category['nominees'] as $nominee) {
            if ($nominee['id'] === $winnerId) {
                return $nominee;
            }
        }

        return null;
    }
}

if (!function_exists('akd_anca_initials')) {
    /**
     * Server-side equivalent of the initials() helper in
     * community-awards-voting.js, needed here since the Winners page
     * renders entirely on the server with no client-side script.
     */
    function akd_anca_initials(string $fullname): string {
        $parts = array_slice(array_filter(explode(' ', trim($fullname))), 0, 2);

        return implode('', array_map(
            static fn(string $part): string => mb_strtoupper(mb_substr($part, 0, 1)),
            $parts
        ));
    }
}