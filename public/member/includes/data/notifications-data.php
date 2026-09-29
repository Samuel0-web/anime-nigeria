<?php
/**
 * Simulated notification data for the member Notifications feature
 * (header dropdown preview + full Notifications page). Mirrors the
 * announcements-data.php convention: swap this array for real data
 * later without touching the header, page, or partials.
 *
 * Categories are derived from real notification sources in the app
 * (Achievements, Trivia, ANAA, ANCA, Challenges, Announcements) rather
 * than invented social-network categories.
 *
 * accent maps to a color token via .akd-notify-icon--{accent} /
 * .akd-notif-dropdown__icon--{accent} in _notifications.scss
 * (gold, teal, violet, ember, crimson).
 *
 * cta is optional — omit the key entirely (or leave it null) for a
 * purely informational notification.
 */

$notifications = [
    [
        'id'          => 'ach-shonen-marathon',
        'category'    => 'Achievements',
        'accent'      => 'ember',
        'icon'        => 'fa-medal',
        'title'       => 'Achievement unlocked',
        'description' => "You unlocked the Shonen Marathon badge.",
        'time'        => '12m ago',
        'unread'      => true,
    ],
    [
        'id'          => 'trivia-tonight',
        'category'    => 'Quizzes',
        'accent'      => 'teal',
        'icon'        => 'fa-brain',
        'title'       => 'Trivia starts soon',
        'description' => 'Anime Trivia Night begins in 30 minutes.',
        'time'        => '45m ago',
        'unread'      => true,
        'cta'         => ['label' => 'Join Trivia', 'url' => '/member/trivia'],
    ],
    [
        'id'          => 'anca-voting-open',
        'category'    => 'Community Awards',
        'accent'      => 'gold',
        'icon'        => 'fa-award',
        'title'       => 'ANCA voting is open',
        'description' => "Cast your vote in this year's Community Awards.",
        'time'        => '2h ago',
        'unread'      => true,
        'cta'         => ['label' => 'Vote Now', 'url' => '/member/community/awards/voting'],
    ],
    [
        'id'          => 'platform-refresh',
        'category'    => 'Announcements',
        'accent'      => 'crimson',
        'icon'        => 'fa-bullhorn',
        'title'       => 'New announcement posted',
        'description' => 'Anime Nigeria has a new look across the member area.',
        'time'        => '5h ago',
        'unread'      => false,
        'cta'         => ['label' => 'Read More', 'url' => '/member/announcements'],
    ],
    [
        'id'          => 'challenge-week-35',
        'category'    => 'Challenges',
        'accent'      => 'violet',
        'icon'        => 'fa-flag-checkered',
        'title'       => 'New community challenge',
        'description' => "This week's Anime Voice Challenge has begun.",
        'time'        => 'Yesterday',
        'unread'      => false,
        'cta'         => ['label' => 'View Challenge', 'url' => '/member/community/challenges'],
    ],
    [
        'id'          => 'anaa-nominations-closing',
        'category'    => 'Anime Awards',
        'accent'      => 'gold',
        'icon'        => 'fa-star',
        'title'       => 'ANAA nominations closing soon',
        'description' => 'Nominations for the 2026 Anime Awards close this weekend.',
        'time'        => 'Yesterday',
        'unread'      => false,
        'cta'         => ['label' => 'View Nominations', 'url' => '/member/awards/nominations'],
    ],
    [
        'id'          => 'ach-quiz-master',
        'category'    => 'Achievements',
        'accent'      => 'ember',
        'icon'        => 'fa-medal',
        'title'       => 'New badge available',
        'description' => 'Complete 5 quizzes this week to earn the Quiz Master badge.',
        'time'        => '2 days ago',
        'unread'      => false,
    ],
    [
        'id'          => 'honoured-ones-august',
        'category'    => 'Community Awards',
        'accent'      => 'gold',
        'icon'        => 'fa-award',
        'title'       => 'Honoured Ones updated',
        'description' => 'New members have joined the Honoured Ones this month.',
        'time'        => '3 days ago',
        'unread'      => false,
        'cta'         => ['label' => 'View Honoured Ones', 'url' => '/member/community/honoured-ones'],
    ],
    [
        'id'          => 'maintenance-complete',
        'category'    => 'Announcements',
        'accent'      => 'crimson',
        'icon'        => 'fa-bullhorn',
        'title'       => 'Scheduled maintenance complete',
        'description' => 'All member features are back to normal.',
        'time'        => '4 days ago',
        'unread'      => false,
    ],
];