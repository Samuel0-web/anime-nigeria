<?php

declare(strict_types=1);

/**
 * Pure presentation helpers for the Challenges module. No I/O, no
 * state, safe to require_once from any Challenges partial. These
 * exist so every phase, round, media, and accent decision is derived
 * from challenges-data.php instead of branching independently in
 * each view.
 */

if (!function_exists('akd_challenge_phase_order')) {
    /**
     * @return list<string>
     */
    function akd_challenge_phase_order(): array {
        return [
            'coming_soon', 'submissions', 'review', 'voting', 'tie_break', 'final', 'winner', 'completed'
        ];
    }
}

if (!function_exists('akd_challenge_phase_rank')) {
    function akd_challenge_phase_rank(string $phase): int {
        $rank = array_search($phase, akd_challenge_phase_order(), true);
        return $rank === false ? 0 : $rank;
    }
}

if (!function_exists('akd_challenge_effective_timeline_phase')) {
    /**
     * tie_break has no timeline step of its own, it's a sub-state of
     * voting for timeline purposes, so the "Voting" step should still
     * read as open while a tie-break is in progress.
     */
    function akd_challenge_effective_timeline_phase(string $phase): string {
        return $phase === 'tie_break' ? 'voting' : $phase;
    }
}

if (!function_exists('akd_challenge_phase_config')) {
    /**
     * Fetch the current phase's copy/CTA config, falling back to
     * 'coming_soon' if 'phase' is ever set to something unrecognised.
     *
     * @param array<string, array<string, mixed>> $phases
     * @return array<string, mixed>
     */
    function akd_challenge_phase_config(array $phases, string $currentPhase): array {
        return $phases[$currentPhase] ?? $phases['coming_soon'];
    }
}

if (!function_exists('akd_challenge_timeline_state')) {
    /**
     * Resolve a timeline step's display state relative to the current
     * phase.
     *
     * @return string One of: closed, open, available, upcoming.
     */
    function akd_challenge_timeline_state(string $stepKey, string $currentPhase): string {
        if ($currentPhase === 'coming_soon') {
            return 'upcoming';
        }

        $effectivePhase = akd_challenge_effective_timeline_phase($currentPhase);
        $currentRank    = akd_challenge_phase_rank($effectivePhase);

        $stepRanks = [
            'submissions' => akd_challenge_phase_rank('submissions'),
            'voting'      => akd_challenge_phase_rank('voting'),
            'final'       => akd_challenge_phase_rank('final'),
            'winner'      => akd_challenge_phase_rank('winner'),
        ];

        $stepRank = $stepRanks[$stepKey] ?? null;

        if ($stepRank === null) {
            return 'upcoming';
        }

        if ($stepKey === 'winner') {
            return $currentRank >= $stepRank ? 'available' : 'upcoming';
        }

        if ($currentRank === $stepRank) {
            return 'open';
        }

        return $currentRank > $stepRank ? 'closed' : 'upcoming';
    }
}

if (!function_exists('akd_challenge_entry_by_id')) {
    /**
     * @param list<array<string, mixed>> $entries
     * @return array<string, mixed>|null
     */
    function akd_challenge_entry_by_id(array $entries, string $id): ?array {
        foreach ($entries as $entry) {
            if ($entry['id'] === $id) {
                return $entry;
            }
        }

        return null;
    }
}

if (!function_exists('akd_challenge_display_round_id')) {
    /**
     * The round shown on the page. The Final phase always shows the
     * 'final' round regardless of 'currentRoundId', so flipping
     * 'phase' to 'final' can never leave the rounds panel out of sync.
     *
     * @param array<string, mixed> $challenge
     */
    function akd_challenge_display_round_id(array $challenge): string {
        if ($challenge['phase'] === 'final') {
            return 'final';
        }

        return $challenge['currentRoundId'];
    }
}

if (!function_exists('akd_challenge_round_by_id')) {
    /**
     * @param list<array<string, mixed>> $rounds
     * @return array<string, mixed>|null
     */
    function akd_challenge_round_by_id(array $rounds, string $id): ?array {
        foreach ($rounds as $round) {
            if ($round['id'] === $id) {
                return $round;
            }
        }

        return null;
    }
}

if (!function_exists('akd_challenge_round_status')) {
    /**
     * Resolve a round's complete/current/upcoming state relative to
     * the displayed round, purely by position in the 'rounds' list,
     * this is never stored on the round itself.
     *
     * @param array<string, mixed>       $round
     * @param list<array<string, mixed>> $rounds
     */
    function akd_challenge_round_status(array $round, string $displayRoundId, array $rounds): string
    {
        $ids          = array_column($rounds, 'id');
        $roundIndex   = array_search($round['id'], $ids, true);
        $currentIndex = array_search($displayRoundId, $ids, true);

        if ($roundIndex === false || $currentIndex === false) {
            return 'upcoming';
        }

        if ($roundIndex < $currentIndex) {
            return 'complete';
        }

        return $roundIndex === $currentIndex ? 'current' : 'upcoming';
    }
}

if (!function_exists('akd_challenge_generate_rounds')) {
    /**
     * Progressive elimination structure, computed from the number of
     * eligible entries rather than hardcoded per challenge. Larger
     * pools get more numbered rounds; smaller pools skip straight to
     * fewer stages. The algorithm has exactly two constants and no
     * special case for any particular total:
     *
     * - While more than $eliminationThreshold entries remain, halve
     *   the field (rounded up) each round.
     * - Once at or under that threshold, a single Semifinals round
     *   (only added when more than $semifinalTarget entries remain)
     *   narrows the field to exactly $semifinalTarget entries.
     * - The Final always seats exactly $semifinalTarget entries and
     *   produces 1 winner, matching the existing 'final' phase, which
     *   always displays exactly the 'finalists' pair.
     *
     * Examples: 64 entries produces Round 1, Round 2, Round 3,
     * Semifinals, Final. 6 entries produces just Semifinals, Final.
     * 2 entries produces just Final, no elimination at all.
     *
     * @return list<array{id: string, name: string, participantCount: int, advancingCount: int}>
     */
    function akd_challenge_generate_rounds(int $totalEntries): array {
        $eliminationThreshold = 8;
        $semifinalTarget      = 2;

        $rounds      = [];
        $remaining   = max($totalEntries, $semifinalTarget);
        $roundNumber = 1;

        while ($remaining > $eliminationThreshold) {
            $advancing = (int) ceil($remaining / 2);

            $rounds[] = [
                'id'               => 'round_' . $roundNumber,
                'name'             => 'Round ' . $roundNumber,
                'participantCount' => $remaining,
                'advancingCount'   => $advancing,
            ];

            $remaining = $advancing;
            $roundNumber++;
        }

        if ($remaining > $semifinalTarget) {
            $rounds[] = [
                'id'               => 'semifinals',
                'name'             => 'Semifinals',
                'participantCount' => $remaining,
                'advancingCount'   => $semifinalTarget,
            ];
        }

        $rounds[] = [
            'id'               => 'final',
            'name'             => 'Final',
            'participantCount' => $semifinalTarget,
            'advancingCount'   => 1,
        ];

        return $rounds;
    }
}

if (!function_exists('akd_challenge_active_entries')) {
    /**
     * The entries to render for the current phase: only the tied
     * entries during tie_break, the full pool for
     * submissions/review/voting, the finalist subset for final, and
     * none otherwise.
     *
     * @param array<string, mixed> $challenge
     * @return list<array<string, mixed>>
     */
    function akd_challenge_active_entries(array $challenge): array {
        $phase = $challenge['phase'];

        if ($phase === 'tie_break') {
            $tied = [];

            foreach ($challenge['tieBreak']['tiedEntryIds'] as $id) {
                $entry = akd_challenge_entry_by_id($challenge['entries'], $id);

                if ($entry !== null) {
                    $tied[] = $entry;
                }
            }

            return $tied;
        }

        if ($phase === 'final') {
            $finalists = [];

            foreach ($challenge['finalists'] as $id) {
                $entry = akd_challenge_entry_by_id($challenge['entries'], $id);

                if ($entry !== null) {
                    $finalists[] = $entry;
                }
            }

            return $finalists;
        }

        if (in_array($phase, ['submissions', 'review', 'voting'], true)) {
            return $challenge['entries'];
        }

        return [];
    }
}

if (!function_exists('akd_challenge_vote_budget')) {
    /**
     * Normal-round vote budget: 10 percent of the entries in that
     * round, rounded to the nearest whole vote, never below 1. Never
     * used for Final (single pick) or tie-break (always 1), those
     * read their fixed values straight from 'voting' in
     * challenges-data.php.
     */
    function akd_challenge_vote_budget(int $entryCount): int {
        return max(1, (int) round($entryCount * 0.10));
    }
}

if (!function_exists('akd_challenge_submission_capacity')) {
    /**
     * Derives remaining slots, full state, and progress text from
     * 'submissions.total' and 'submissions.max' so the view never
     * computes these itself.
     *
     * @param array<string, mixed> $challenge
     * @return array{total: int, max: int, remaining: int, isFull: bool}
     */
    function akd_challenge_submission_capacity(array $challenge): array {
        $total = (int) $challenge['submissions']['total'];
        $max   = (int) $challenge['submissions']['max'];

        return [
            'total'     => $total,
            'max'       => $max,
            'remaining' => max(0, $max - $total),
            'isFull'    => $total >= $max,
        ];
    }
}

if (!function_exists('akd_challenge_media_config')) {
    /**
     * The single place every audio/image decision lives. Templates
     * and JS never branch on challenge type directly, they read the
     * kind, accepted formats, size limit, icons and copy from here.
     * Adding a third challenge type means adding one entry to this
     * array, nothing else in the flow needs to change.
     *
     * @return array{kind: string, accept: list<string>, maxSizeMb: int, acceptLabel: string, fieldLabel: string, dropzoneLabel: string, uploadIcon: string, fileIcon: string, previewable: bool}
     */
    function akd_challenge_media_config(string $type): array {
        $configs = [
            'voice' => [
                'kind'          => 'audio',
                'accept'        => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4', 'audio/x-m4a'],
                'maxSizeMb'     => 10,
                'acceptLabel'   => 'MP3, WAV, OGG or M4A',
                'fieldLabel'    => 'Audio file',
                'dropzoneLabel' => 'Choose audio file',
                'uploadIcon'    => 'fa-cloud-arrow-up',
                'fileIcon'      => 'fa-file-audio',
                'previewable'   => false,
            ],
            'art' => [
                'kind'          => 'image',
                'accept'        => ['image/png', 'image/jpeg', 'image/webp'],
                'maxSizeMb'     => 8,
                'acceptLabel'   => 'PNG, JPG or WEBP',
                'fieldLabel'    => 'Artwork image',
                'dropzoneLabel' => 'Choose image file',
                'uploadIcon'    => 'fa-image',
                'fileIcon'      => 'fa-file-image',
                'previewable'   => true,
            ],
        ];

        return $configs[$type] ?? $configs['voice'];
    }
}

if (!function_exists('akd_challenge_generate_entries')) {
    /**
     * Builds a pool of mock entries from name and title fragment
     * lists instead of hand-writing dozens of literal array entries.
     * Deterministic (no randomness), so the same type and count
     * always render the same pool. Uses real, meaningful titles per
     * type rather than generic "Entry #N" labels.
     *
     * @return list<array{id: string, user: array{id: int, username: string}, description: string, file: string}>
     */
    function akd_challenge_generate_entries(string $type, int $count): array {
        $media = akd_challenge_media_config($type);

        $usernames = [
            'kaidoshirogane', 'sennin.reads', 'ayaka.draws', 'nightcrawlerng',
            'obinna.va', 'liarslane', 'chidinma.arts', 'tobiloba_va',
            'yusuf.ink', 'amaka.sketches', 'segunspeaks', 'ndidi.paints',
            'kelechi.va', 'zainab.draws', 'emeka_voices', 'fatima.arts',
        ];

        $voiceTitles = [
            'Domain Expansion: Infinite Void', 'Throughout Heaven and Earth...',
            'A Promise Under the Cherry Blossoms', 'I Am Going to Surpass You',
            'The Will of Fire Never Dies', 'Plus Ultra', 'Bankai: Final Getsuga',
            'Nothing Personal, Kid', 'I Reject My Humanity', 'This Is My Nen',
            'We Are Not Things', 'A Hero Always Arrives Late',
            'Believe It', 'I Am the Bone of My Sword',
            'The Weak Have No Choice', 'Idea of Evil',
        ];

        $artTitles = [
            'My Favourite Anime Moment', 'Shibuya at Midnight',
            'The Bluff That Changed Everything', 'A Quiet Morning in the Hidden Village',
            'Under the Sakura Tree', 'The Last Stand', 'Colours of Autumn',
            'Rain Over the Rooftops', 'Reflections in the Water',
            'The Guardian\'s Watch', 'Lanterns of the Festival',
            'Dawn Over the Battlefield', 'A Letter Never Sent',
            'The Long Way Home', 'Silence Before the Storm',
            'Echoes of a Forgotten Song',
        ];

        $artFiles = [
            '/uploads/bleach-tybw-poster.webp', '/uploads/black-torch-poster.webp',
            '/uploads/clevatess-poster.webp', '/uploads/frieren-poster.webp',
            '/uploads/kill-blue-poster.webp', '/uploads/liar-game-poster.webp',
            '/uploads/tensura-poster.webp',
        ];

        $titles  = $media['kind'] === 'audio' ? $voiceTitles : $artTitles;
        $entries = [];

        for ($i = 0; $i < $count; $i++) {
            $username = $usernames[$i % count($usernames)];
            $title    = $titles[$i % count($titles)];
            $number   = str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);

            $entries[] = [
                'id'          => 'submission_' . $number,
                'user'        => ['id' => $i + 1, 'username' => $username],
                'description' => $title,
                'file'        => $media['kind'] === 'audio'
                    ? '/uploads/challenges/audio/submission-' . $number . '.mp3'
                    : $artFiles[$i % count($artFiles)],
            ];
        }

        return $entries;
    }
}

if (!function_exists('akd_challenge_accent_hex')) {
    /**
     * Maps a challenge's 'accent' name to the matching hex value from
     * _tokens.scss. Kept in sync by hand since PHP cannot read SCSS
     * variables, update both places together if a token's hex value
     * changes. No new colours are introduced here, only existing
     * tokens already used elsewhere in the member dashboard.
     */
    function akd_challenge_accent_hex(string $accent): string {
        $accents = [
            'teal'    => '#6bbfba',
            'violet'  => '#a88cd8',
            'ember'   => '#e0935f',
            'crimson' => '#d73b5d',
            'sakura'  => '#e8a3b3',
            'gold'    => '#DF9A1B',
        ];

        return $accents[$accent] ?? $accents['gold'];
    }
}

if (!function_exists('akd_challenge_accent_rgba')) {
    /**
     * The accent colour at a given alpha, for badge/border/background
     * washes, mirroring how $gold-dim relates to $gold in
     * _tokens.scss.
     */
    function akd_challenge_accent_rgba(string $accent, float $alpha): string {
        $hex = ltrim(akd_challenge_accent_hex($accent), '#');
        $r   = hexdec(substr($hex, 0, 2));
        $g   = hexdec(substr($hex, 2, 2));
        $b   = hexdec(substr($hex, 4, 2));

        return "rgba($r, $g, $b, $alpha)";
    }
}

if (!function_exists('akd_challenge_winner')) {
    /**
     * Resolve the winning entry plus its vote count, or null if the
     * challenge hasn't reached a winner yet.
     *
     * @param array<string, mixed> $challenge
     * @return array{entry: array<string, mixed>, votes: int}|null
     */
    function akd_challenge_winner(array $challenge): ?array {
        if (!in_array($challenge['phase'], ['winner', 'completed'], true)) {
            return null;
        }

        $entry = akd_challenge_entry_by_id($challenge['entries'], $challenge['winner']['entryId']);

        if ($entry === null) {
            return null;
        }

        return ['entry' => $entry, 'votes' => (int) $challenge['winner']['votes']];
    }
}

if (!function_exists('akd_challenge_submission_caption')) {
    /**
     * The "@username" credit line shown under every submission's title.
     *
     * @param array<string, mixed> $entry
     */
    function akd_challenge_submission_caption(array $entry): string {
        return '@' . $entry['user']['username'];
    }
}

if (!function_exists('akd_challenge_format_date')) {
    function akd_challenge_format_date(string $date): string {
        $timestamp = strtotime($date);

        return $timestamp ? date('F j, Y', $timestamp) : $date;
    }
}