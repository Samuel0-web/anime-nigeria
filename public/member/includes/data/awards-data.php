<?php
declare(strict_types=1);

/**
 * Simulated ANAA 2026 data. No database, no API, just mock content for
 * the member Overview, Nominations and Voting UI.
 *
 * 'phase' is the single source of truth for the current award state,
 * shared by every ANAA page. Everything else (hero copy, CTA labels and
 * links, timeline state, category actions, Community Picks blurb, and
 * the Nominations/Voting pages' own status/copy) is derived from it via
 * the matching entry in 'phases' and the helpers in awards-support.php.
 * To simulate a different point in the season, change 'phase' below.
 * Nothing else needs to change.
 *
 * @return array<string, mixed>
 */
return [
    'year'  => 2026,
    'phase' => 'voting', // coming_soon | nominations | voting | winners

    /*
     * Per-phase copy and calls to action, shared across ANAA pages.
     * 'nominationsPage' and 'votingPage' nest copy specific to those
     * pages so each stays driven by the same phase without duplicating
     * a second state system. Each 'formActive' flag is what its page
     * uses to decide whether to render the interactive experience or a
     * closed-state message.
     */
    'phases' => [
        'coming_soon' => [
            'badge'                     => 'Coming Soon',
            'heroDescription'           => 'The Anime Nigeria Anime Awards return soon, chosen entirely by the community. Nominations open shortly.',
            'primaryCtaLabel'           => 'How ANAA Works',
            'primaryCtaLink'            => '/community/awards',
            'secondaryCtaLabel'         => null,
            'secondaryCtaLink'          => null,
            'ctaHeading'                => 'The countdown has started.',
            'ctaDescription'            => "Nominations open soon. Get ready to put this year's favourites forward.",
            'ctaButtonLabel'            => 'How ANAA Works',
            'ctaButtonLink'             => '/community/awards',
            'categoryCtaLabel'          => 'Coming soon',
            'categoryLink'              => null,
            'communityPicksDescription' => 'A look back at the anime, characters and moments the community rallied behind in ANAA 2025.',
            'nominationsPage'           => [
                'statusLabel' => 'Nominations Open Soon',
                'heading'     => 'Get ready to put your favourites forward.',
                'description' => "Nominations for ANAA 2026 haven't opened yet. Here's what you'll be able to nominate once they do.",
                'formActive'  => false,
                'ctaLabel'    => 'ANAA Overview',
                'ctaLink'     => '/member/awards',
            ],
            'votingPage' => [
                'statusLabel' => 'Voting Not Open',
                'heading'     => 'Voting hasn\'t started yet.',
                'description' => 'Nominations open first, then the community narrows the field, then voting begins. Check back once nominations are underway.',
                'formActive'  => false,
                'ctaLabel'    => 'ANAA Overview',
                'ctaLink'     => '/member/awards',
            ],
        ],
        'nominations' => [
            'badge'                     => 'Nominations Open',
            'heroDescription'           => 'Celebrating the anime, characters and moments chosen by the Anime Nigeria community.',
            'primaryCtaLabel'           => 'Nominate Now',
            'primaryCtaLink'            => '/member/awards/nominations',
            'secondaryCtaLabel'         => null,
            'secondaryCtaLink'          => null,
            'ctaHeading'                => 'Put your favourites forward.',
            'ctaDescription'            => 'Nominations are entirely community driven. If you loved it this year, now is the time to say so.',
            'ctaButtonLabel'            => 'Nominate Now',
            'ctaButtonLink'             => '/member/awards/nominations',
            'categoryCtaLabel'          => 'Nominate now',
            'categoryLink'              => '/member/awards/nominations',
            'communityPicksDescription' => 'A preview of the anime and characters already generating buzz this season.',
            'nominationsPage'           => [
                'statusLabel' => 'Nominations Open',
                'heading'     => 'Nominate your favourites',
                'description' => 'Think an anime, character or moment deserves recognition? Put it forward for the Anime Nigeria Anime Awards.',
                'formActive'  => true,
                'ctaLabel'    => null,
                'ctaLink'     => null,
            ],
            'votingPage' => [
                'statusLabel' => 'Voting Not Open',
                'heading'     => 'Voting opens once nominations close.',
                'description' => 'The community is still putting forward nominees. Voting begins as soon as nominations wrap up.',
                'formActive'  => false,
                'ctaLabel'    => 'View Nominations',
                'ctaLink'     => '/member/awards/nominations',
            ],
        ],
        'voting' => [
            'badge'                     => 'Voting Open',
            'heroDescription'           => 'Celebrating the anime, characters and moments chosen by the Anime Nigeria community.',
            'primaryCtaLabel'           => 'Vote Now',
            'primaryCtaLink'            => '/member/awards/voting',
            'secondaryCtaLabel'         => 'View Nominees',
            'secondaryCtaLink'          => '/member/awards/nominations',
            'ctaHeading'                => 'Make your voice heard.',
            'ctaDescription'            => "The community has chosen the nominees. Now it's time to decide who takes home the award.",
            'ctaButtonLabel'            => 'Vote in ANAA',
            'ctaButtonLink'             => '/member/awards/voting',
            'categoryCtaLabel'          => 'Vote now',
            'categoryLink'              => '/member/awards/voting',
            'communityPicksDescription' => "The anime, characters and moments the Anime Nigeria community can't stop talking about this season.",
            'nominationsPage'           => [
                'statusLabel' => 'Nominations Closed',
                'heading'     => 'Nominations are closed.',
                'description' => 'The community has submitted its picks. Voting is now open.',
                'formActive'  => false,
                'ctaLabel'    => 'Vote Now',
                'ctaLink'     => '/member/awards/voting',
            ],
            'votingPage' => [
                'statusLabel' => 'Voting Open',
                'heading'     => 'Cast your vote',
                'description' => 'One nominee per category. Your picks stay editable until you cast today\'s votes.',
                'formActive'  => true,
                'ctaLabel'    => null,
                'ctaLink'     => null,
            ],
        ],
        'winners' => [
            'badge'                     => 'Winners Live',
            'heroDescription'           => 'The results are in. See who the Anime Nigeria community crowned this year.',
            'primaryCtaLabel'           => 'View Winners',
            'primaryCtaLink'            => '/member/awards/winners',
            'secondaryCtaLabel'         => 'See Nominees',
            'secondaryCtaLink'          => '/member/awards/nominations',
            'ctaHeading'                => 'The results are in.',
            'ctaDescription'            => 'See every category winner, chosen from start to finish by the Anime Nigeria community.',
            'ctaButtonLabel'            => 'View Winners',
            'ctaButtonLink'             => '/member/awards/winners',
            'categoryCtaLabel'          => 'See the winner',
            'categoryLink'              => '/member/awards/winners',
            'communityPicksDescription' => "The nominees and moments that captured the community's attention throughout ANAA 2026.",
            'nominationsPage'           => [
                'statusLabel' => 'Nominations Closed',
                'heading'     => 'The nomination period has ended.',
                'description' => 'The award season has moved to the results stage.',
                'formActive'  => false,
                'ctaLabel'    => 'View Winners',
                'ctaLink'     => '/member/awards/winners',
            ],
            'votingPage' => [
                'statusLabel' => 'Voting Closed',
                'heading'     => 'Voting has ended.',
                'description' => 'The results are in. See who the community crowned this year.',
                'formActive'  => false,
                'ctaLabel'    => 'View Winners',
                'ctaLink'     => '/member/awards/winners',
            ],
        ],
    ],

    'stats' => [
        'categories' => 12,
        'nominees'   => 48,
    ],

    'hero' => [
        'kicker'   => 'Anime Nigeria Anime Awards',
        'headline' => 'ANAA 2026',
        'image'    => '/uploads/frieren-poster.webp',
        'imageAlt' => "Frieren: Beyond Journey's End",
    ],

    'timelineSteps' => [
        ['key' => 'nominations', 'label' => 'Nominations'],
        ['key' => 'voting',      'label' => 'Voting'],
        ['key' => 'winners',     'label' => 'Winners'],
    ],

    /*
     * Nomination submission window and per-category limit, used only by
     * the Nominations page. Change 'closesAt' here, not in the view.
     */
    'nomination' => [
        'opensAt'          => '2026-08-01',
        'closesAt'         => '2026-10-15',
        'limitPerCategory' => 1,
    ],

    /*
     * Eligible release window and the plain-language rules shown under
     * "Before you nominate", grouped by entryType so they line up with
     * 'eligibleEntries' below.
     */
    'eligibility' => [
        'start' => '2025-01-01',
        'end'   => '2025-12-31',
        'rules' => [
            'anime' => [
                'label' => 'Anime',
                'items' => [
                    'Must have officially released during the eligible period.',
                    'Must be a series, film, OVA or other supported format.',
                    'Must have had an eligible public release.',
                ],
            ],
            'character' => [
                'label' => 'Characters',
                'items' => [
                    'Must appear in an eligible anime.',
                    "The character's appearance must fall within the eligible award scope.",
                ],
            ],
            'openingEnding' => [
                'label' => 'Openings & Endings',
                'items' => [
                    'Must belong to an eligible anime.',
                    'Must have been released as part of the eligible anime during the award period.',
                ],
            ],
            'fight' => [
                'label' => 'Fights & Moments',
                'items' => [
                    'Must occur in an eligible anime.',
                    'Must have occurred within the eligible award period.',
                ],
            ],
        ],
    ],

    /*
     * Every category the community can nominate into and vote on.
     * 'entryType' + optional 'filter' select from 'eligibleEntries' for
     * the Nominations search workspace. 'description' and 'nominees' are
     * used only by the Voting page: 'description' is the tooltip copy,
     * 'nominees' is the curated shortlist of finalists (a separate,
     * smaller list from the full eligibleEntries pool, since voting
     * happens on the finalists that came out of nominations, not the
     * entire eligible field).
     */
    'categories' => [
        [
            'slug'        => 'best-anime',
            'name'        => 'Best Anime',
            'blurb'       => 'The series that defined the year, start to finish.',
            'description' => 'The anime that defined the year.',
            'count'       => 14,
            'accent'      => 'sakura',
            'image'       => '/uploads/frieren-poster.webp',
            'featured'    => true,
            'entryType'   => 'anime',
            'filter'      => null,
            'nominees'    => [
                ['id' => 'anime-frieren',    'name' => "Frieren: Beyond Journey's End", 'image' => '/uploads/frieren-poster.webp'],
                ['id' => 'anime-solo',       'name' => 'Solo Leveling',                 'image' => null],
                ['id' => 'anime-apothecary', 'name' => 'The Apothecary Diaries',        'image' => null],
                ['id' => 'anime-jjk',        'name' => 'Jujutsu Kaisen',                'image' => null],
                ['id' => 'anime-csm',        'name' => 'Chainsaw Man',                  'image' => null],
            ],
        ],
        [
            'slug'        => 'best-male-character',
            'name'        => 'Best Male Character',
            'blurb'       => 'The leads, rivals and scene stealers.',
            'description' => 'The male character who stood out most this season.',
            'count'       => 9,
            'accent'      => 'teal',
            'image'       => null,
            'entryType'   => 'character',
            'filter'      => ['gender' => 'male'],
            'nominees'    => [
                ['id' => 'char-jinwoo', 'name' => 'Sung Jin-Woo', 'image' => null],
                ['id' => 'char-gojo',   'name' => 'Gojo Satoru',  'image' => null],
                ['id' => 'char-aqua',   'name' => 'Aqua Hoshino', 'image' => null],
                ['id' => 'char-denji',  'name' => 'Denji',        'image' => null],
                ['id' => 'char-okarun', 'name' => 'Okarun',       'image' => null],
            ],
        ],
        [
            'slug'        => 'best-female-character',
            'name'        => 'Best Female Character',
            'blurb'       => 'The women who carried entire arcs.',
            'description' => 'The female character who stood out most this season.',
            'count'       => 11,
            'accent'      => 'crimson',
            'image'       => null,
            'entryType'   => 'character',
            'filter'      => ['gender' => 'female'],
            'nominees'    => [
                ['id' => 'char-frieren', 'name' => 'Frieren',       'image' => '/uploads/frieren-poster.webp'],
                ['id' => 'char-maomao',  'name' => 'Maomao',        'image' => null],
                ['id' => 'char-power',   'name' => 'Power',         'image' => null],
                ['id' => 'char-nobara',  'name' => 'Nobara Kugisaki', 'image' => null],
                ['id' => 'char-momo',    'name' => 'Momo Ayase',    'image' => null],
            ],
        ],
        [
            'slug'        => 'best-supporting-character',
            'name'        => 'Best Supporting Character',
            'blurb'       => 'The scene partners and side characters who made the story land.',
            'description' => 'The supporting character who elevated the story.',
            'count'       => 13,
            'accent'      => 'teal',
            'image'       => null,
            'entryType'   => 'character',
            'filter'      => ['role' => 'supporting'],
            'nominees'    => [
                ['id' => 'char-power-supp',  'name' => 'Power',           'image' => null],
                ['id' => 'char-nobara-supp', 'name' => 'Nobara Kugisaki', 'image' => null],
                ['id' => 'char-maki',        'name' => 'Maki Zenin',      'image' => null],
                ['id' => 'char-todo',        'name' => 'Todo Aoi',        'image' => null],
            ],
        ],
        [
            'slug'        => 'best-opening',
            'name'        => 'Best Opening',
            'blurb'       => "The intro sequences stuck in everyone's head.",
            'description' => 'The opening that best set the tone for its anime.',
            'count'       => 7,
            'accent'      => 'gold',
            'image'       => null,
            'entryType'   => 'openingEnding',
            'filter'      => ['kind' => 'opening'],
            'nominees'    => [
                ['id' => 'oe-idol',     'name' => '"Idol" — Oshi no Ko',            'image' => null],
                ['id' => 'oe-kickback', 'name' => '"Kick Back" — Chainsaw Man',     'image' => null],
                ['id' => 'oe-specialz', 'name' => '"SPECIALZ" — Jujutsu Kaisen',    'image' => null],
                ['id' => 'oe-otonoke',  'name' => '"Otonoke" — Dandadan',           'image' => null],
            ],
        ],
        [
            'slug'        => 'best-ending',
            'name'        => 'Best Ending',
            'blurb'       => 'The credits roll nobody skipped.',
            'description' => 'The ending that closed out its anime best.',
            'count'       => 6,
            'accent'      => 'violet',
            'image'       => null,
            'entryType'   => 'openingEnding',
            'filter'      => ['kind' => 'ending'],
            'nominees'    => [
                ['id' => 'oe-kawaki',    'name' => '"Kawaki wo Ameku" — Chainsaw Man',              'image' => null],
                ['id' => 'oe-lady',      'name' => '"LADY" — Jujutsu Kaisen',                       'image' => null],
                ['id' => 'oe-backlight', 'name' => '"Backlight" — Dandadan',                        'image' => null],
                ['id' => 'oe-yuuyake',   'name' => "\"Yuuyake\" — Frieren: Beyond Journey's End",   'image' => '/uploads/frieren-poster.webp'],
            ],
        ],
        [
            'slug'        => 'best-fight',
            'name'        => 'Best Fight',
            'blurb'       => 'The showdowns that broke group chats.',
            'description' => 'The fight scene that had everyone talking.',
            'count'       => 10,
            'accent'      => 'ember',
            'image'       => null,
            'entryType'   => 'fight',
            'filter'      => ['kind' => 'fight'],
            'nominees'    => [
                ['id' => 'fight-gojo-sukuna',  'name' => 'Gojo vs. Sukuna',       'image' => null],
                ['id' => 'fight-denji-katana', 'name' => 'Denji vs. Katana Man',  'image' => null],
                ['id' => 'fight-jinwoo-beru',  'name' => 'Sung Jin-Woo vs. Beru', 'image' => null],
                ['id' => 'fight-kafka-no9',    'name' => 'Kafka vs. Kaiju No. 9', 'image' => null],
            ],
        ],
        [
            'slug'        => 'best-anime-moment',
            'name'        => 'Best Anime Moment',
            'blurb'       => 'The single scenes that stopped the conversation cold.',
            'description' => 'The single scene that defined the season.',
            'count'       => 9,
            'accent'      => 'sakura',
            'image'       => null,
            'entryType'   => 'fight',
            'filter'      => ['kind' => 'moment'],
            'nominees'    => [
                ['id' => 'moment-frieren', 'name' => "Frieren's Farewell to Himmel",  'image' => '/uploads/frieren-poster.webp'],
                ['id' => 'moment-maomao',  'name' => 'Maomao Solves the Poison Case', 'image' => null],
                ['id' => 'moment-aqua',    'name' => "Aqua's Reveal",                 'image' => null],
                ['id' => 'moment-okarun',  'name' => "Okarun's Transformation",       'image' => null],
            ],
        ],
        [
            'slug'        => 'best-new-anime',
            'name'        => 'Best New Anime',
            'blurb'       => "The freshest arrivals that earned a place in the community's heart this year.",
            'description' => 'The strongest first-time arrival this year.',
            'count'       => 8,
            'accent'      => 'gold',
            'image'       => null,
            'entryType'   => 'anime',
            'filter'      => ['isNew' => true],
            'nominees'    => [
                ['id' => 'anime-dandadan',  'name' => 'Dandadan',           'image' => null],
                ['id' => 'anime-kaiju8',    'name' => 'Kaiju No. 8',        'image' => null],
                ['id' => 'anime-sakamoto',  'name' => 'Sakamoto Days',      'image' => null],
                ['id' => 'anime-bluelock2', 'name' => 'Blue Lock Season 2', 'image' => null],
            ],
        ],
    ],

    /*
     * The shared pool the Nominations workspace searches. Grouped by
     * entryType. Each category above filters into one of these buckets.
     */
    'eligibleEntries' => [
        'anime' => [
            ['id' => 'anime-frieren',    'title' => "Frieren: Beyond Journey's End", 'year' => 2025, 'format' => 'TV Series', 'studio' => 'Madhouse',            'genres' => ['Fantasy', 'Adventure'],           'image' => '/uploads/frieren-poster.webp', 'isNew' => false],
            ['id' => 'anime-solo',       'title' => 'Solo Leveling',                 'year' => 2025, 'format' => 'TV Series', 'studio' => 'A-1 Pictures',         'genres' => ['Action', 'Fantasy'],              'image' => null, 'isNew' => false],
            ['id' => 'anime-apothecary', 'title' => 'The Apothecary Diaries',        'year' => 2025, 'format' => 'TV Series', 'studio' => 'TOHO animation',       'genres' => ['Mystery', 'Drama'],               'image' => null, 'isNew' => false],
            ['id' => 'anime-jjk',        'title' => 'Jujutsu Kaisen',                'year' => 2025, 'format' => 'TV Series', 'studio' => 'MAPPA',                'genres' => ['Action', 'Supernatural'],         'image' => null, 'isNew' => false],
            ['id' => 'anime-onk',        'title' => 'Oshi no Ko',                    'year' => 2025, 'format' => 'TV Series', 'studio' => 'Doga Kobo',            'genres' => ['Drama', 'Showbiz'],               'image' => null, 'isNew' => false],
            ['id' => 'anime-csm',        'title' => 'Chainsaw Man',                  'year' => 2025, 'format' => 'TV Series', 'studio' => 'MAPPA',                'genres' => ['Action', 'Horror'],               'image' => null, 'isNew' => false],
            ['id' => 'anime-dandadan',   'title' => 'Dandadan',                      'year' => 2025, 'format' => 'TV Series', 'studio' => 'Science SARU',         'genres' => ['Action', 'Comedy', 'Supernatural'],'image' => null, 'isNew' => true],
            ['id' => 'anime-kaiju8',     'title' => 'Kaiju No. 8',                   'year' => 2025, 'format' => 'TV Series', 'studio' => 'Production I.G',       'genres' => ['Action', 'Sci-Fi'],               'image' => null, 'isNew' => true],
            ['id' => 'anime-sakamoto',   'title' => 'Sakamoto Days',                 'year' => 2025, 'format' => 'TV Series', 'studio' => 'TMS Entertainment',    'genres' => ['Action', 'Comedy'],               'image' => null, 'isNew' => true],
            ['id' => 'anime-bluelock2',  'title' => 'Blue Lock Season 2',            'year' => 2025, 'format' => 'TV Series', 'studio' => '8bit',                 'genres' => ['Sports', 'Drama'],                'image' => null, 'isNew' => false],
        ],
        'character' => [
            ['id' => 'char-frieren',  'title' => 'Frieren',        'animeTitle' => "Frieren: Beyond Journey's End", 'gender' => 'female', 'role' => 'lead',       'image' => '/uploads/frieren-poster.webp'],
            ['id' => 'char-jinwoo',   'title' => 'Sung Jin-Woo',   'animeTitle' => 'Solo Leveling',                 'gender' => 'male',   'role' => 'lead',       'image' => null],
            ['id' => 'char-maomao',   'title' => 'Maomao',         'animeTitle' => 'The Apothecary Diaries',        'gender' => 'female', 'role' => 'lead',       'image' => null],
            ['id' => 'char-gojo',     'title' => 'Gojo Satoru',    'animeTitle' => 'Jujutsu Kaisen',                'gender' => 'male',   'role' => 'lead',       'image' => null],
            ['id' => 'char-aqua',     'title' => 'Aqua Hoshino',   'animeTitle' => 'Oshi no Ko',                    'gender' => 'male',   'role' => 'lead',       'image' => null],
            ['id' => 'char-denji',    'title' => 'Denji',          'animeTitle' => 'Chainsaw Man',                  'gender' => 'male',   'role' => 'lead',       'image' => null],
            ['id' => 'char-power',    'title' => 'Power',          'animeTitle' => 'Chainsaw Man',                  'gender' => 'female', 'role' => 'supporting', 'image' => null],
            ['id' => 'char-nobara',   'title' => 'Nobara Kugisaki','animeTitle' => 'Jujutsu Kaisen',                'gender' => 'female', 'role' => 'supporting', 'image' => null],
            ['id' => 'char-momo',     'title' => 'Momo Ayase',     'animeTitle' => 'Dandadan',                      'gender' => 'female', 'role' => 'lead',       'image' => null],
            ['id' => 'char-okarun',   'title' => 'Okarun',         'animeTitle' => 'Dandadan',                      'gender' => 'male',   'role' => 'lead',       'image' => null],
            ['id' => 'char-kafka',    'title' => 'Kafka Hibino',   'animeTitle' => 'Kaiju No. 8',                   'gender' => 'male',   'role' => 'lead',       'image' => null],
            ['id' => 'char-sakamoto', 'title' => 'Taro Sakamoto',  'animeTitle' => 'Sakamoto Days',                 'gender' => 'male',   'role' => 'lead',       'image' => null],
        ],
        'openingEnding' => [
            ['id' => 'oe-idol',      'title' => '"Idol"',            'animeTitle' => 'Oshi no Ko',      'kind' => 'opening', 'artist' => 'YOASOBI',            'image' => null],
            ['id' => 'oe-kickback',  'title' => '"Kick Back"',       'animeTitle' => 'Chainsaw Man',    'kind' => 'opening', 'artist' => 'Kenshi Yonezu',      'image' => null],
            ['id' => 'oe-specialz',  'title' => '"SPECIALZ"',        'animeTitle' => 'Jujutsu Kaisen',  'kind' => 'opening', 'artist' => 'King Gnu',           'image' => null],
            ['id' => 'oe-otonoke',   'title' => '"Otonoke"',         'animeTitle' => 'Dandadan',        'kind' => 'opening', 'artist' => 'Creepy Nuts',        'image' => null],
            ['id' => 'oe-kawaki',    'title' => '"Kawaki wo Ameku"', 'animeTitle' => 'Chainsaw Man',    'kind' => 'ending',  'artist' => 'Aimer',              'image' => null],
            ['id' => 'oe-lady',      'title' => '"LADY"',            'animeTitle' => 'Jujutsu Kaisen',  'kind' => 'ending',  'artist' => 'Suis from Yorushika','image' => null],
            ['id' => 'oe-backlight', 'title' => '"Backlight"',       'animeTitle' => 'Dandadan',        'kind' => 'ending',  'artist' => 'Zutomayo',           'image' => null],
            ['id' => 'oe-yuuyake',   'title' => '"Yuuyake"',         'animeTitle' => "Frieren: Beyond Journey's End", 'kind' => 'ending', 'artist' => 'MYRIAD', 'image' => '/uploads/frieren-poster.webp'],
        ],
        'fight' => [
            ['id' => 'fight-gojo-sukuna', 'title' => 'Gojo vs. Sukuna',              'animeTitle' => 'Jujutsu Kaisen',                'kind' => 'fight',  'image' => null],
            ['id' => 'fight-denji-katana','title' => 'Denji vs. Katana Man',         'animeTitle' => 'Chainsaw Man',                  'kind' => 'fight',  'image' => null],
            ['id' => 'fight-jinwoo-beru', 'title' => 'Sung Jin-Woo vs. Beru',        'animeTitle' => 'Solo Leveling',                 'kind' => 'fight',  'image' => null],
            ['id' => 'fight-kafka-no9',   'title' => 'Kafka vs. Kaiju No. 9',        'animeTitle' => 'Kaiju No. 8',                   'kind' => 'fight',  'image' => null],
            ['id' => 'moment-frieren',    'title' => "Frieren's Farewell to Himmel", 'animeTitle' => "Frieren: Beyond Journey's End", 'kind' => 'moment', 'image' => '/uploads/frieren-poster.webp'],
            ['id' => 'moment-maomao',     'title' => 'Maomao Solves the Poison Case','animeTitle' => 'The Apothecary Diaries',        'kind' => 'moment', 'image' => null],
            ['id' => 'moment-aqua',       'title' => "Aqua's Reveal",                'animeTitle' => 'Oshi no Ko',                    'kind' => 'moment', 'image' => null],
            ['id' => 'moment-okarun',     'title' => "Okarun's Transformation",      'animeTitle' => 'Dandadan',                      'kind' => 'moment', 'image' => null],
        ],
    ],

    /*
     * Community Picks: a curated, informational highlight reel on the
     * Overview page. Not the nominations list, not the winners list.
     * This section has no section-level link by design.
     */
    'communityPicks' => [
        [
            'title'    => "Frieren: Beyond Journey's End",
            'subtitle' => 'Frieren',
            'category' => 'Best Anime',
            'accent'   => 'sakura',
            'image'    => '/uploads/frieren-poster.webp',
        ],
        [
            'title'    => 'Solo Leveling',
            'subtitle' => 'Sung Jin-Woo',
            'category' => 'Best Anime',
            'accent'   => 'teal',
            'image'    => null,
        ],
        [
            'title'    => 'The Apothecary Diaries',
            'subtitle' => 'Maomao',
            'category' => 'Best Female Character',
            'accent'   => 'crimson',
            'image'    => null,
        ],
        [
            'title'    => 'Jujutsu Kaisen',
            'subtitle' => 'Gojo Satoru',
            'category' => 'Best Male Character',
            'accent'   => 'gold',
            'image'    => null,
        ],
        [
            'title'    => 'Oshi no Ko',
            'subtitle' => '"Idol"',
            'category' => 'Best Opening',
            'accent'   => 'violet',
            'image'    => null,
        ],
        [
            'title'    => 'Chainsaw Man',
            'subtitle' => 'Denji vs. Katana Man',
            'category' => 'Best Fight',
            'accent'   => 'ember',
            'image'    => null,
        ],
    ],

    'previousWinners' => [
        [
            'category' => 'Best Anime',
            'title'    => "Frieren: Beyond Journey's End",
            'accent'   => 'sakura',
            'image'    => '/uploads/frieren-poster.webp',
        ],
        [
            'category' => 'Best Male Character',
            'title'    => 'Denji, Chainsaw Man',
            'accent'   => 'ember',
            'image'    => null,
        ],
        [
            'category' => 'Best Female Character',
            'title'    => 'Power, Chainsaw Man',
            'accent'   => 'crimson',
            'image'    => null,
        ],
        [
            'category' => 'Best Fight',
            'title'    => 'Gojo vs. Sukuna, Jujutsu Kaisen',
            'accent'   => 'gold',
            'image'    => null,
        ],
    ],
];