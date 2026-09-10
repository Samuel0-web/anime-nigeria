<?php
declare(strict_types=1);

/**
 * Simulated ANCA 2026 data. No database, no API: mock content for the
 * member Overview UI (and, eventually, Nominations, Voting and Winners).
 *
 * ANCA is a separate award system from ANAA (see awards-data.php). This
 * file mirrors the general shape of awards-data.php, a single 'phase'
 * driving everything else, so the two systems share a lifecycle without
 * sharing any content.
 *
 * Each category's current winner is resolved through 'winnerId', which
 * points at one of that category's own 'nominees' entries, the same
 * pattern awards-data.php (ANAA) already uses. This means the winner's
 * name, username, and recognition text never need to be duplicated in
 * a second place. Historical recognition belongs on the separate
 * Honoured Ones page, not here.
 *
 * To simulate a different point in the season, change 'phase' below.
 * Nothing else needs to change.
 *
 * @return array<string, mixed>
 */
return [
    'id'        => 'anca-2026',
    'name'      => 'Anime Nigeria Community Awards',
    'shortName' => 'ANCA',
    'edition'   => 2026,
    'phase'     => 'winners', // coming_soon | nominations | voting | winners

    'description' => 'ANCA is where the Anime Nigeria community recognises its own: the admins, the members, and the personalities who make the community what it is.',

    'hero' => [
        'kicker'  => 'Anime Nigeria Community Awards',
        'tagline' => 'Celebrating the people, personalities, and everyday contributions that make our community what it is.',
        // Placeholder asset. None of the current uploads are confirmed
        // community photography, this is the closest candidate (not a
        // logo, poster, or texture). Swap it for a real group or event
        // photo whenever one is available, nothing else needs to change.
        'image'    => '/uploads/1783455914832.png',
        'imageAlt' => 'Members of the Anime Nigeria community',
    ],

    /*
     * Per-phase copy for the hero only. Per-stage status (Open, Closed,
     * Not open yet, Available, Not available yet) is derived instead of
     * stored, see akd_anca_stage_status() in community-awards-support.php.
     */
    'phases' => [
        'coming_soon' => [
            'statusLabel'  => 'Opening Soon',
            'statusNote'   => 'ANCA 2026 has not started yet. Nominations open soon.',
            'primaryLabel' => 'How ANCA Works',
            'primaryLink'  => '#anca-how-it-works',
        ],
        'nominations' => [
            'statusLabel'  => 'Nominations Open',
            'statusNote'   => 'The community is putting names forward right now.',
            'primaryLabel' => 'Nominate Someone',
            'primaryLink'  => '/member/community/awards/nominations',
        ],
        'voting' => [
            'statusLabel'  => 'Voting Open',
            'statusNote'   => "Nominations are closed. Time to vote for this year's winners.",
            'primaryLabel' => 'Vote Now',
            'primaryLink'  => '/member/community/awards/voting',
        ],
        'winners' => [
            'statusLabel'  => 'Winners Announced',
            'statusNote'   => "Nominations and voting have closed. Here's who the community chose.",
            'primaryLabel' => 'View Winners',
            'primaryLink'  => '/member/community/awards/winners',
        ],
    ],

    'dates' => [
        'nominations_open'  => '2026-11-01',
        'nominations_close' => '2026-11-21',
        'voting_open'       => '2026-11-24',
        'voting_close'      => '2026-12-05',
        'winners_announced' => '2026-12-12',
    ],

        /*
     * Nomination submission rules, used only by the Nominations page.
     * Mirrors the shape of ANAA's 'nomination' block in awards-data.php.
     */
    'nomination' => [
        'limitPerCategory' => 1,
        'reasonMaxLength'  => 160,
    ],

        /*
     * Voting rules, used only by the Voting page. 'allowChangeBeforeClose'
     * mirrors ANAA's own voting page, which keeps a member's picks
     * editable until they close, so the confirmation dialog never claims
     * a vote is final when nothing in the app actually enforces that.
     */
    'voting' => [
        'allowChangeBeforeClose' => true,
    ],

    'philosophy' => [
        'eyebrow' => 'What ANCA Celebrates',
        'heading' => 'The people who make this community what it is',
        'body'    => 'ANCA celebrates the members who show up, who keep conversations lively, who help others out, who organise activities, and who bring their own personality to the group. It exists to recognise contribution in every form, from the quiet and consistent to the loud and unmissable.',
        'traits'  => [
            ['label' => 'Participation', 'note' => 'Showing up, consistently.'],
            ['label' => 'Personality',   'note' => 'Being genuinely memorable.'],
            ['label' => 'Helpfulness',   'note' => 'Answering the question nobody else did.'],
            ['label' => 'Humour',        'note' => 'Making ordinary conversations funnier.'],
            ['label' => 'Creativity',    'note' => 'Bringing something nobody asked for, but everyone loved.'],
            ['label' => 'Consistency',   'note' => 'Being there, week after week.'],
        ],
    ],

    /*
     * Exactly seven categories are previewed on the Overview page (see
     * categories.php, which slices the first seven). The list can grow
     * beyond seven later for the full Nominations/Voting experience
     * without changing the Overview preview logic.
     */
        // No 'featured' flag: which category reads as visually larger on
    // Overview is decided structurally (first and last rendered, see
    // categories.php and _overview.scss), not by data. 'prompt' is the
    // short contextual question shown on Nominations and reused as the
    // Voting page's category question. 'nominees' is this category's
    // current shortlist for Voting, the same role ANAA's own categories
    // already play in awards-data.php. Every nominee here is drawn from
    // the existing member roster in players.php, no invented people.
        'categories' => [
        [
            'slug'   => 'community-mvp',
            'name'   => 'Community MVP',
            'blurb'  => 'The name that comes up first when the community talks about who holds things together.',
            'prompt' => 'Who holds this community together?',
            'accent' => 'gold',
            'winnerId' => '3',
            'nominees' => [
                ['id' => '1',  'fullname' => 'Naruto Uzumaki', 'username' => 'naruto',  'accent' => 'gold',   'reason' => 'Somehow ends up at the centre of every community moment.'],
                ['id' => '3',  'fullname' => 'Gojo Satoru',    'username' => 'gojo',    'accent' => 'teal',   'reason' => 'The name people bring up first when asked who holds things together.'],
                ['id' => '24', 'fullname' => 'Frieren',        'username' => 'frieren', 'accent' => 'sakura', 'reason' => 'Quietly present for years, never asking for credit.'],
            ],
        ],
        [
            'slug'   => 'most-active-member',
            'name'   => 'Most Active Member',
            'blurb'  => 'Always in the chat, always part of the conversation.',
            'prompt' => 'Who is always around, no matter what?',
            'accent' => 'teal',
            'winnerId' => '6',
            'nominees' => [
                ['id' => '2',  'fullname' => 'Roronoa Zoro',    'username' => 'zoro',   'accent' => 'violet',  'reason' => 'Online at any hour, day or night.'],
                ['id' => '6',  'fullname' => 'Ichigo Kurosaki', 'username' => 'ichigo', 'accent' => 'ember',   'reason' => 'Never misses a conversation.'],
                ['id' => '12', 'fullname' => 'Killua Zoldyck',  'username' => 'killua', 'accent' => 'crimson', 'reason' => 'Always the first to react.'],
            ],
        ],
        [
            'slug'   => 'most-active-admin',
            'name'   => 'Most Active Admin',
            'blurb'  => 'Keeps the community running, day in and day out.',
            'prompt' => 'Who keeps things running behind the scenes?',
            'accent' => 'violet',
            'winnerId' => '7',
            'nominees' => [
                ['id' => '7',  'fullname' => 'Levi Ackerman',   'username' => 'levi',    'accent' => 'gold',   'reason' => 'Keeps the chats in order without anyone noticing the effort.'],
                ['id' => '8',  'fullname' => 'Mikasa Ackerman', 'username' => 'mikasa',  'accent' => 'teal',   'reason' => 'Handles the behind the scenes work every week.'],
                ['id' => '18', 'fullname' => 'Kakashi Hatake',  'username' => 'kakashi', 'accent' => 'sakura', 'reason' => 'The admin people actually message when something goes wrong.'],
            ],
        ],
        [
            'slug'   => 'funniest-member',
            'name'   => 'Funniest Member',
            'blurb'  => 'Turns ordinary conversations into something everyone screenshots.',
            'prompt' => 'Who always has everyone laughing?',
            'accent' => 'sakura',
            'winnerId' => '21',
            'nominees' => [
                ['id' => '20', 'fullname' => 'Natsu Dragneel', 'username' => 'natsu', 'accent' => 'violet',  'reason' => 'Turns a slow afternoon into a running joke.'],
                ['id' => '21', 'fullname' => 'Asta',           'username' => 'asta',  'accent' => 'ember',   'reason' => 'Loud, chaotic, and somehow always on time with a joke.'],
                ['id' => '14', 'fullname' => 'Yuji Itadori',   'username' => 'yuji',  'accent' => 'crimson', 'reason' => 'Can make anyone laugh mid argument.'],
            ],
        ],
        [
            'slug'   => 'most-helpful-member',
            'name'   => 'Most Helpful Member',
            'blurb'  => 'The first to answer, explain, or point someone in the right direction.',
            'prompt' => 'Who is always ready to lend a hand?',
            'accent' => 'ember',
            'winnerId' => '9',
            'nominees' => [
                ['id' => '9',  'fullname' => 'Tanjiro Kamado',   'username' => 'tanjiro',       'accent' => 'gold',   'reason' => 'Answers questions before anyone else even sees them.'],
                ['id' => '15', 'fullname' => 'Megumi Fushiguro', 'username' => 'megumi',        'accent' => 'teal',   'reason' => 'Quietly helps without making a big deal of it.'],
                ['id' => '19', 'fullname' => 'Emmanuel Samuel',  'username' => 'zoro_itachi43', 'accent' => 'sakura', 'reason' => 'Always has an answer ready for new members.'],
            ],
        ],
        [
            'slug'   => 'most-engaging-member',
            'name'   => 'Most Engaging Member',
            'blurb'  => 'Starts the conversations everyone ends up joining.',
            'prompt' => 'Who gets every conversation going?',
            'accent' => 'crimson',
            'winnerId' => '23',
            'nominees' => [
                ['id' => '23', 'fullname' => 'Sung Jinwoo', 'username' => 'jinwoo', 'accent' => 'violet',  'reason' => 'Starts threads that half the community ends up joining.'],
                ['id' => '11', 'fullname' => 'Eren Yeager', 'username' => 'eren',   'accent' => 'ember',   'reason' => 'Strong opinions that always get a conversation going.'],
                ['id' => '22', 'fullname' => 'Yuno',        'username' => 'yuno',   'accent' => 'crimson', 'reason' => "Pulls people into conversations they didn't expect to join."],
            ],
        ],
        [
            'slug'   => 'most-supportive-member',
            'name'   => 'Most Supportive Member',
            'blurb'  => 'Shows up for other members, in the chat and outside it.',
            'prompt' => 'Who shows up for other members?',
            'accent' => 'teal',
            'winnerId' => '25',
            'nominees' => [
                ['id' => '10', 'fullname' => 'Nezuko Kamado', 'username' => 'nezuko', 'accent' => 'gold',   'reason' => 'Shows up quietly whenever someone needs support.'],
                ['id' => '25', 'fullname' => 'Maomao',        'username' => 'maomao', 'accent' => 'teal',   'reason' => 'Checks in on people the community sometimes forgets about.'],
                ['id' => '5',  'fullname' => 'Sasuke Uchiha', 'username' => 'sasuke', 'accent' => 'sakura', 'reason' => 'There when it actually matters, even if not loudly.'],
            ],
        ],
    ],

    /*
     * The three stages of the ANCA lifecycle. 'cta' is the label used
     * when a stage is currently open/available (see cta.php). Status per
     * stage (Open, Closed, Not open yet, Available, Not available yet)
     * is computed from 'phase' above via akd_anca_stage_status(), not
     * stored here, so there is exactly one place that logic lives.
     */
    'howItWorks' => [
        [
            'key'         => 'nominations',
            'label'       => 'Nominations',
            'description' => 'Community members nominate the people they think deserve recognition, in any category.',
            'link'        => '/member/community/awards/nominations',
            'cta'         => 'Nominate Someone',
        ],
        [
            'key'         => 'voting',
            'label'       => 'Voting',
            'description' => 'Once nominations close, the community votes for their favourite nominee in each category, once per day, until voting closes.',
            'link'        => '/member/community/awards/voting',
            'cta'         => 'Vote Now',
        ],
        [
            'key'         => 'winners',
            'label'       => 'Winners',
            'description' => 'Winners are announced for the current edition once voting closes.',
            'link'        => '/member/community/awards/winners',
            'cta'         => 'View Winners',
        ],
    ],

    // Current edition winners only. Historical recognition lives on the
    // separate Honoured Ones page, never here.
    'currentWinners' => [],

    'honouredOnesLink' => '/member/community/honoured-ones',
];