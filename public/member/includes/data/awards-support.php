<?php
declare(strict_types=1);

/**
 * Pure presentation helpers for the Awards module. No I/O, no state.
 * Safe to require_once from any Awards partial. These exist so the
 * timeline, CTAs, category states, and the Nominations workspace are all
 * derived from awards-data.php's 'phase' value instead of being set
 * independently in each view.
 */

if (!function_exists('akd_award_phase_order')) {
    /**
     * @return list<string>
     */
    function akd_award_phase_order(): array
    {
        return ['nominations', 'voting', 'winners'];
    }
}

if (!function_exists('akd_award_timeline_state')) {
    /**
     * Resolve a timeline step's display state relative to the current phase.
     *
     * @param string $stepKey      One of: nominations, voting, winners.
     * @param string $currentPhase One of: coming_soon, nominations, voting, winners.
     * @return string One of: closed, open, available, upcoming.
     */
    function akd_award_timeline_state(string $stepKey, string $currentPhase): string
    {
        if ($currentPhase === 'coming_soon') {
            return 'upcoming';
        }

        $order        = akd_award_phase_order();
        $stepIndex    = array_search($stepKey, $order, true);
        $currentIndex = array_search($currentPhase, $order, true);

        if ($stepIndex === false || $currentIndex === false) {
            return 'upcoming';
        }

        if ($stepIndex < $currentIndex) {
            return 'closed';
        }

        if ($stepIndex > $currentIndex) {
            return 'upcoming';
        }

        // Same index: this is the active step. Winners reads as
        // "available" rather than "open" since nothing is in progress.
        return $stepKey === 'winners' ? 'available' : 'open';
    }
}

if (!function_exists('akd_award_phase_config')) {
    /**
     * Fetch the current phase's copy and CTA config, falling back to
     * 'coming_soon' if 'phase' is ever set to something unrecognised.
     *
     * @param array<string, array<string, mixed>> $phases
     * @param string $currentPhase
     * @return array<string, mixed>
     */
    function akd_award_phase_config(array $phases, string $currentPhase): array
    {
        return $phases[$currentPhase] ?? $phases['coming_soon'];
    }
}

if (!function_exists('akd_award_nominations_page_config')) {
    /**
     * Fetch the Nominations-page-specific slice of the current phase's config.
     *
     * @param array<string, array<string, mixed>> $phases
     * @param string $currentPhase
     * @return array{statusLabel: string, heading: string, description: string, formActive: bool, ctaLabel: string|null, ctaLink: string|null}
     */
    function akd_award_nominations_page_config(array $phases, string $currentPhase): array
    {
        $phase = akd_award_phase_config($phases, $currentPhase);

        return $phase['nominationsPage'] ?? [
            'statusLabel' => 'Unavailable',
            'heading'     => 'Nominations are currently unavailable.',
            'description' => '',
            'formActive'  => false,
            'ctaLabel'    => 'ANAA Overview',
            'ctaLink'     => '/member/awards',
        ];
    }
}

if (!function_exists('akd_award_entries_for_category')) {
    /**
     * Resolve which entries from the shared eligibleEntries pool belong
     * to a given category, applying its optional 'filter'.
     *
     * @param array{entryType: string, filter?: array<string, mixed>|null} $category
     * @param array<string, list<array<string, mixed>>> $eligibleEntries
     * @return list<array<string, mixed>>
     */
    function akd_award_entries_for_category(array $category, array $eligibleEntries): array
    {
        $pool   = $eligibleEntries[$category['entryType']] ?? [];
        $filter = $category['filter'] ?? null;

        if (!$filter) {
            return $pool;
        }

        return array_values(array_filter($pool, static function (array $entry) use ($filter): bool {
            foreach ($filter as $key => $value) {
                if (($entry[$key] ?? null) !== $value) {
                    return false;
                }
            }

            return true;
        }));
    }
}

if (!function_exists('akd_award_entry_subtitle')) {
    /**
     * Build the secondary line shown under an entry's title. Differs by
     * entryType since anime, characters, openings/endings, and fights
     * each carry different supporting fields.
     *
     * @param array<string, mixed> $entry
     */
    function akd_award_entry_subtitle(string $entryType, array $entry): string
    {
        switch ($entryType) {
            case 'anime':
                $parts = array_filter([$entry['studio'] ?? null, $entry['format'] ?? null]);
                return implode(' • ', $parts);

            case 'character':
                return (string) ($entry['animeTitle'] ?? '');

            case 'openingEnding':
                $parts = array_filter([$entry['animeTitle'] ?? null, $entry['artist'] ?? null]);
                return implode(' • ', $parts);

            case 'fight':
                return (string) ($entry['animeTitle'] ?? '');

            default:
                return '';
        }
    }
}

if (!function_exists('akd_award_format_date')) {
    /**
     * Format a Y-m-d date string from awards-data.php for display.
     * Falls back to the raw string if it can't be parsed.
     */
    function akd_award_format_date(string $date): string
    {
        $timestamp = strtotime($date);

        return $timestamp ? date('F j, Y', $timestamp) : $date;
    }
}

if (!function_exists('akd_award_voting_page_config')) {
    /**
     * Fetch the Voting-page-specific slice of the current phase's config.
     *
     * @param array<string, array<string, mixed>> $phases
     * @param string $currentPhase
     * @return array{statusLabel: string, heading: string, description: string, formActive: bool, ctaLabel: string|null, ctaLink: string|null}
     */
    function akd_award_voting_page_config(array $phases, string $currentPhase): array
    {
        $phase = akd_award_phase_config($phases, $currentPhase);

        return $phase['votingPage'] ?? [
            'statusLabel' => 'Unavailable',
            'heading'     => 'Voting is currently unavailable.',
            'description' => '',
            'formActive'  => false,
            'ctaLabel'    => 'ANAA Overview',
            'ctaLink'     => '/member/awards',
        ];
    }
}