<?php
declare(strict_types=1);

/**
 * Simulated challenge data. No database, no API, this file is the
 * single source of truth for the Community Challenges page.
 *
 * 'phase' drives every part of the page (hero, timeline, rounds, and
 * which phase partial gets included). 'type' drives which media,
 * validation, icons and labels apply, via
 * akd_challenge_media_config() in challenges-support.php. Change
 * $activeType below to preview a different challenge type entirely.
 *
 * Supported phases, in order:
 *   coming_soon -> submissions -> review -> voting -> tie_break -> final -> winner -> completed
 *
 * @return array<string, mixed>
 */

$activeType  = 'art';    // change to 'voice' or 'art' to preview that challenge type
$activePhase = 'submissions'; // single source of truth for the mock phase, change this to 
// preview a different phase: coming_soon, submissions, review, voting, tie_break, final, 
// winner, completed

/*
 * Shared across every challenge type. The phase-transition copy and
 * the coming_soon lifecycle overview are intentionally generic
 * ("your entry", "voting", "results"), the type-specific identity
 * lives entirely in each challenge's own title/tagline/description
 * and in akd_challenge_media_config().
 */
$phasesTemplate = [
    'coming_soon' => [
        'badge'       => 'Coming Soon',
        'heading'     => 'A New Challenge Is Coming',
        'description' => 'A new community challenge is on the way. Here is how it will work, so you know exactly what to expect once it opens.',
        'ctaLabel'    => null,
        'ctaTarget'   => null,
        'note'        => 'Submissions open September 8, 2026.',
    ],
    'submissions' => [
        'badge'       => 'Submissions Open',
        'heading'     => 'Submit Your Entry',
        'description' => "Submissions close soon, so don't wait.",
        'ctaLabel'    => 'Submit Entry',
        'ctaTarget'   => '#submit-entry',
        'note'        => 'Submissions close September 14, 2026.',
    ],
    'review' => [
        'badge'       => 'Under Review',
        'heading'     => 'Under Review',
        'description' => 'Submissions are now closed. The submitted entries are being reviewed and prepared for the next stage. Voting will open once review is complete. There is nothing you need to do right now.',
        'ctaLabel'    => null,
        'ctaTarget'   => null,
        'note'        => 'Voting opens shortly.',
    ],
    'voting' => [
        'badge'       => 'Voting Open',
        'heading'     => 'Community Voting',
        'description' => 'Review the entries and vote for the ones you like most.',
        'ctaLabel'    => 'Vote Now',
        'ctaTarget'   => '#voting',
        'note'        => 'Voting closes September 21, 2026.',
    ],
    'tie_break' => [
        'badge'       => 'Tie-break',
        'heading'     => 'Tie-break',
        'description' => 'Vote for the entry you think should advance.',
        'ctaLabel'    => 'Cast Tie-break Vote',
        'ctaTarget'   => '#voting',
        'note'        => null,
    ],
    'final' => [
        'badge'       => 'Final Round',
        'heading'     => 'The Final',
        'description' => 'The last entries are competing for the win.',
        'ctaLabel'    => 'Vote Now',
        'ctaTarget'   => '#voting',
        'note'        => 'Final voting closes September 28, 2026.',
    ],
    'winner' => [
        'badge'       => 'Winner Announced',
        'heading'     => 'Winner',
        'description' => 'The community has spoken.',
        'ctaLabel'    => null,
        'ctaTarget'   => null,
        'note'        => null,
    ],
    'completed' => [
        'badge'       => 'Challenge Completed',
        'heading'     => 'Challenge Complete',
        'description' => 'Thanks to everyone who submitted and voted.',
        'ctaLabel'    => null,
        'ctaTarget'   => null,
        'note'        => null,
    ],
];

$lifecycleTemplate = [
    [
        'key'         => 'submit',
        'title'       => 'Submit your entry',
        'description' => 'Once submissions open, put your entry together and upload it directly from this page.',
    ],
    [
        'key'         => 'review',
        'title'       => 'Review',
        'description' => 'After submissions close, every entry is checked over and prepared for voting.',
    ],
    [
        'key'         => 'voting',
        'title'       => 'Community voting',
        'description' => 'Eligible members get a set number of votes to spend across their favourite entries, across as many rounds as the field needs.',
    ],
    [
        'key'         => 'tie_break',
        'title'       => 'Tie-break, if needed',
        'description' => 'If two or more entries tie for the last qualifying spot, a short tie-break vote decides who advances.',
    ],
    [
        'key'         => 'results',
        'title'       => 'Results',
        'description' => 'Once voting closes, the winner is revealed right here.',
    ],
];

$timelineStepsTemplate = [
    ['key' => 'submissions', 'label' => 'Submissions'],
    ['key' => 'voting',      'label' => 'Voting'],
    ['key' => 'final',       'label' => 'Final'],
    ['key' => 'winner',      'label' => 'Winner'],
];

$titleRulesTemplate = ['min' => 3, 'max' => 40];

// ---------------------------------------------------------------
// VOICE CHALLENGE
// ---------------------------------------------------------------
$voiceTotal = 64;

$voiceChallenge = [
    'id'   => 'anime-voice-challenge-2026',
    'type' => 'voice',

    'title'         => 'Anime Voice Challenge',
    'tagline'       => 'Give your favourite anime moment a voice.',
    'description'   => 'Record, submit and vote on the anime lines and moments that deserve to be heard. One challenge, one winner, chosen entirely by the community.',
    'communityLine' => 'Created by the Anime Nigeria community.',
    'accent'        => 'teal',
    'phase'         => $activePhase,

    'image'    => '/uploads/bleach-tybw-poster.webp',
    'imageAlt' => 'Bleach: Thousand-Year Blood War',

    'currentRoundId' => 'round_1',
    'timelineSteps'  => $timelineStepsTemplate,
    'phases'         => $phasesTemplate,
    'lifecycle'      => $lifecycleTemplate,

    'submission' => [
        'instructions' => [
            'Record a short audio clip (voice only) of your chosen anime line or moment.',
            'Give it a title that identifies the moment.',
            'Keep clips under 60 seconds.',
        ],
    ],

    'submissions' => [
        'total' => $voiceTotal,
        'max'   => 100,
    ],

    'rules' => [
        'title' => $titleRulesTemplate,
    ],

    'voting' => [
        'finalMaxSelections'    => 1,
        'tieBreakMaxSelections' => 1,
    ],

    'tieBreak' => [
        'roundId'            => 'round_1',
        'qualifyingPosition' => 32,
        'tiedEntryIds'       => ['submission_002', 'submission_005'],
        'deadline'           => '2026-09-17',
        'resolved'           => false,
        'resolvedEntryId'    => null,
    ],

    // Computed from $voiceTotal by the general elimination algorithm,
    // not hardcoded: 64 entries naturally produces Round 1, Round 2,
    // Round 3, Semifinals, Final.
    'rounds' => akd_challenge_generate_rounds($voiceTotal),

    // Rendered pool for the currently displayed round. Kept at a
    // manageable size for the mock UI rather than matching a round's
    // full headcount literally, same relationship the previous
    // mock data already had between 'entries' and 'rounds'.
    'entries' => akd_challenge_generate_entries('voice', 16),

    'finalists' => ['submission_001', 'submission_003'],

    'winner' => [
        'entryId' => 'submission_001',
        'votes'   => 1284,
    ],
];

// ---------------------------------------------------------------
// ART CHALLENGE
// ---------------------------------------------------------------
$artTotal = 6;

$artChallenge = [
    'id'   => 'anime-art-challenge-2026',
    'type' => 'art',

    'title'         => 'Anime Art Challenge',
    'tagline'       => "Bring your favourite anime universe to life on canvas.",
    'description'   => 'Submit original artwork inspired by anime, and let the community vote for the piece that captures it best. One challenge, one winning artwork, chosen entirely by the community.',
    'communityLine' => 'Hosted by the Anime Nigeria art circle.',
    'accent'        => 'violet',
    'phase'         => $activePhase,

    'image'    => '/uploads/frieren-poster.webp',
    'imageAlt' => "Frieren: Beyond Journey's End",

    'currentRoundId' => 'semifinals',
    'timelineSteps'  => $timelineStepsTemplate,
    'phases'         => $phasesTemplate,
    'lifecycle'      => $lifecycleTemplate,

    'submission' => [
        'instructions' => [
            'Create or draw an original piece inspired by any anime you love.',
            'Give it a title that captures what it depicts.',
            'Submit as PNG, JPG or WEBP.',
        ],
    ],

    'submissions' => [
        'total' => $artTotal,
        'max'   => 50,
    ],

    'rules' => [
        'title' => $titleRulesTemplate,
    ],

    'voting' => [
        'finalMaxSelections'    => 1,
        'tieBreakMaxSelections' => 1,
    ],

    'tieBreak' => [
        'roundId'            => 'semifinals',
        'qualifyingPosition' => 2,
        'tiedEntryIds'       => ['submission_002', 'submission_004'],
        'deadline'           => '2026-09-20',
        'resolved'           => false,
        'resolvedEntryId'    => null,
    ],

    // With only 6 eligible entries, the same algorithm naturally
    // skips numbered rounds entirely: Semifinals, then Final.
    'rounds' => akd_challenge_generate_rounds($artTotal),

    'entries' => akd_challenge_generate_entries('art', $artTotal),

    'finalists' => ['submission_001', 'submission_003'],

    'winner' => [
        'entryId' => 'submission_001',
        'votes'   => 486,
    ],
];

return $activeType === 'art' ? $artChallenge : $voiceChallenge;