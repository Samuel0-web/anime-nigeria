<?php
/**
 * Hardcoded mock data for the member dashboard.
 * Structure mirrors what a service/repository would return later —
 * swap each array for real data without touching the partials.
 */

$dashboardData = [

    'welcome' => [
        'name'    => 'Emmanuel',
        'message' => "Here's what's happening around Anime Nigeria.",
    ],

    'events' => [
        [
            'id'          => 'trivia-night',
            'status'      => 'live',
            'statusLabel' => 'LIVE',
            'title'       => 'Anime Trivia Night',
            'meta'        => '12:43 remaining',
            'action'      => 'Join Trivia',
            'url'         => '/member/trivia',
            'image'       => '/uploads/frieren-poster.webp',
        ],
        [
            'id'          => 'voice-challenge',
            'status'      => 'starts-soon',
            'statusLabel' => 'STARTS SOON',
            'title'       => 'Anime Voice Challenge',
            'meta'        => 'Starts in 1h 24m',
            'action'      => 'View Challenge',
            'url'         => '/member/challenges',
            'image'       => '/uploads/logos/upscalemedia-transformed (1).png',
        ],
        [
            'id'          => 'community-awards',
            'status'      => 'upcoming',
            'statusLabel' => 'UPCOMING',
            'title'       => 'Community Awards 2026',
            'meta'        => 'Nominations open tomorrow',
            'action'      => 'View Awards',
            'url'         => '/member/awards',
            'image'       => '/uploads/upscalemedia-transformed (2).png',
        ],
        [
            'id'          => 'seasonal-trivia',
            'status'      => 'upcoming',
            'statusLabel' => 'UPCOMING',
            'title'       => 'Seasonal Trivia: Summer Arc',
            'meta'        => 'Opens this weekend',
            'action'      => 'View Trivia',
            'url'         => '/member/trivia',
            'image'       => '/uploads/upscalemedia-transformed (3).png',
        ],
    ],

    'notifications' => [
        [
            'icon'   => 'fa-trophy',
            'accent' => 'gold',
            'title'  => 'Achievement unlocked',
            'text'   => 'You earned "First Blood" for your first trivia win.',
            'time'   => '2h ago',
            'unread' => true,
        ],
        [
            'icon'   => 'fa-bell',
            'accent' => 'sakura',
            'title'  => 'Quiz reminder',
            'text'   => 'Anime Trivia Night starts in 15 minutes.',
            'time'   => '5h ago',
            'unread' => true,
        ],
        [
            'icon'   => 'fa-check-circle',
            'accent' => 'teal',
            'title'  => 'Voting is open',
            'text'   => 'Community Awards voting is now open.',
            'time'   => '1d ago',
            'unread' => false,
        ],
    ],

    'progress' => [
        ['label' => 'Challenges Joined', 'value' => 14, 'icon' => 'fa-flag-checkered'],
        ['label' => 'Trivia Played',     'value' => 27, 'icon' => 'fa-brain'],
        ['label' => 'Achievements',      'value' => 9,  'icon' => 'fa-medal'],
        ['label' => 'Awards Received',   'value' => 2,  'icon' => 'fa-trophy'],
    ],

    'achievements' => [
        [
            'icon' => 'fa-bolt',
            'name' => 'First Blood',
            'text' => 'Won your first trivia round.',
            'time' => '2h ago',
        ],
        [
            'icon' => 'fa-fire',
            'name' => 'On a Streak',
            'text' => 'Joined 5 challenges in a row.',
            'time' => '3d ago',
        ],
        [
            'icon' => 'fa-star',
            'name' => 'Fan Favourite',
            'text' => 'Received 100 community votes.',
            'time' => '1w ago',
        ],
    ],

    'announcements' => [
        [
            'title' => 'Community Awards nominations open soon',
            'text'  => 'Get ready to nominate your favourite creators and moments.',
            'date'  => 'Aug 14',
        ],
        [
            'title' => 'Trivia Night starts this Saturday',
            'text'  => 'A new season of weekly trivia kicks off this weekend.',
            'date'  => 'Aug 16',
        ],
        [
            'title' => 'New achievement badges added',
            'text'  => 'Five new badges just joined the achievements catalog.',
            'date'  => 'Aug 20',
        ],
    ],

    'majorEvent' => [
        'title'  => 'Community Awards 2026',
        'text'   => 'Nomination season is almost here.',
        'action' => 'View Awards',
        'url'    => '/member/awards',
        'image'  => '/uploads/upscalemedia-transformed (2).png',
    ],

    'quickActions' => [
        ['icon' => 'fa-user',            'label' => 'Profile',       'text' => 'View and edit your profile',        'url' => '/member/profile'],
        ['icon' => 'fa-medal',           'label' => 'Achievements',  'text' => "See everything you've unlocked",    'url' => '/member/achievements'],
        ['icon' => 'fa-circle-question', 'label' => 'Help Centre',   'text' => 'Guides, FAQs and support',          'url' => '/member/help-centre'],
    ],

];