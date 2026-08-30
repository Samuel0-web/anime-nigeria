<?php
/**
 * Simulated announcement data for the member Announcements page.
 * Structure mirrors what a service/repository would return later —
 * swap this array for real data without touching the page or partials.
 *
 * accent maps to a color token via .akd-announce-category--{accent}
 * in _announcements.scss (gold, teal, violet, ember, crimson).
 */

$announcements = [
    [
        'id'       => 'anaa-2026-voting',
        'category' => 'Anime Awards',
        'accent'   => 'gold',
        'date'     => '2026-08-26',
        'title'    => 'ANAA 2026 voting is now open',
        'excerpt'  => 'The nominees have been chosen by the Anime Nigeria community. Voting is now open.',
        'cta'      => 'Vote Now',
        'url'      => '/member/awards/voting',
        'featured' => true,
    ],
    [
        'id'       => 'anca-2026-nominations',
        'category' => 'Community Awards',
        'accent'   => 'gold',
        'date'     => '2026-08-24',
        'title'    => 'ANCA 2026 nominations are now open',
        'excerpt'  => 'Nominate the people, contributions and moments you think deserve recognition from the community.',
        'cta'      => 'Nominate Now',
        'url'      => '/member/community/awards/nominations',
        'featured' => false,
    ],
    [
        'id'       => 'community-challenge-week-35',
        'category' => 'Challenges',
        'accent'   => 'teal',
        'date'     => '2026-08-21',
        'title'    => 'A new community challenge has arrived',
        'excerpt'  => "Take part in this week's challenge and put your creativity to the test.",
        'cta'      => 'View Challenge',
        'url'      => '/member/community/challenges',
        'featured' => false,
    ],
    [
        'id'       => 'platform-refresh-2026',
        'category' => 'Platform',
        'accent'   => 'ember',
        'date'     => '2026-08-18',
        'title'    => 'Anime Nigeria has a new look',
        'excerpt'  => "We've made improvements across the member experience, including a refreshed dashboard and faster navigation.",
        'cta'      => 'Learn More',
        'url'      => '/member/blogs',
        'featured' => false,
    ],
    [
        'id'       => 'honoured-ones-august',
        'category' => 'Community',
        'accent'   => 'violet',
        'date'     => '2026-08-15',
        'title'    => 'New members have joined the Honoured Ones',
        'excerpt'  => 'A new group of standout community members has been recognized for their contributions this month.',
        'cta'      => 'View Honoured Ones',
        'url'      => '/member/community/honoured-ones',
        'featured' => false,
    ],
    [
        'id'       => 'scheduled-maintenance-aug',
        'category' => 'Important',
        'accent'   => 'crimson',
        'date'     => '2026-08-12',
        'title'    => 'Scheduled maintenance is now complete',
        'excerpt'  => 'Recent platform maintenance has finished. All member features are back to normal.',
        'cta'      => 'Visit Help Centre',
        'url'      => '/member/help',
        'featured' => false,
    ],
];