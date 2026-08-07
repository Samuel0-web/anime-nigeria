<?php
/**
 * Public player-profile data for the leaderboard's read-only profile page.
 *
 * There's no players/stats table yet, so the roster mirrors the same
 * hardcoded list used by the React leaderboard (resources/js/react/data/leaderboard.js).
 * Everything beyond rank/xp/level is derived deterministically from
 * level + a per-field seed — same "same seed, same result" approach as
 * akdAvatarColor() in profile.php — so numbers stay stable across requests
 * instead of reshuffling every page load. Swap akdBuildPlayerProfile()
 * for a real query once a stats table exists; player.php only depends
 * on the array shape returned here.
 */

function akdLeaderboardRoster(): array {
    return [
        ['id' => 1,  'rank' => 1,  'previousRank' => 1,  'fullname' => 'Naruto Uzumaki',      'username' => 'naruto',        'xp' => 15400, 'level' => 42, 'avatar' => null],
        ['id' => 2,  'rank' => 2,  'previousRank' => 3,  'fullname' => 'Roronoa Zoro',        'username' => 'zoro',          'xp' => 14820, 'level' => 41, 'avatar' => null],
        ['id' => 3,  'rank' => 3,  'previousRank' => 2,  'fullname' => 'Gojo Satoru',         'username' => 'gojo',          'xp' => 14510, 'level' => 40, 'avatar' => null],
        ['id' => 4,  'rank' => 4,  'previousRank' => 4,  'fullname' => 'Monkey D. Luffy',     'username' => 'luffy',         'xp' => 14270, 'level' => 39, 'avatar' => null],
        ['id' => 5,  'rank' => 5,  'previousRank' => 7,  'fullname' => 'Sasuke Uchiha',       'username' => 'sasuke',        'xp' => 14080, 'level' => 39, 'avatar' => null],
        ['id' => 6,  'rank' => 6,  'previousRank' => 6,  'fullname' => 'Ichigo Kurosaki',     'username' => 'ichigo',        'xp' => 13740, 'level' => 38, 'avatar' => null],
        ['id' => 7,  'rank' => 7,  'previousRank' => 5,  'fullname' => 'Levi Ackerman',       'username' => 'levi',          'xp' => 13520, 'level' => 37, 'avatar' => null],
        ['id' => 8,  'rank' => 8,  'previousRank' => 9,  'fullname' => 'Mikasa Ackerman',     'username' => 'mikasa',        'xp' => 13260, 'level' => 36, 'avatar' => null],
        ['id' => 9,  'rank' => 9,  'previousRank' => 8,  'fullname' => 'Tanjiro Kamado',      'username' => 'tanjiro',       'xp' => 12990, 'level' => 36, 'avatar' => null],
        ['id' => 10, 'rank' => 10, 'previousRank' => 12, 'fullname' => 'Nezuko Kamado',       'username' => 'nezuko',        'xp' => 12670, 'level' => 35, 'avatar' => null],
        ['id' => 11, 'rank' => 11, 'previousRank' => 11, 'fullname' => 'Eren Yeager',         'username' => 'eren',          'xp' => 12380, 'level' => 34, 'avatar' => null],
        ['id' => 12, 'rank' => 12, 'previousRank' => 10, 'fullname' => 'Killua Zoldyck',      'username' => 'killua',        'xp' => 12120, 'level' => 34, 'avatar' => null],
        ['id' => 13, 'rank' => 13, 'previousRank' => 15, 'fullname' => 'Gon Freecss',         'username' => 'gon',           'xp' => 11850, 'level' => 33, 'avatar' => null],
        ['id' => 14, 'rank' => 14, 'previousRank' => 13, 'fullname' => 'Yuji Itadori',        'username' => 'yuji',          'xp' => 11620, 'level' => 33, 'avatar' => null],
        ['id' => 15, 'rank' => 15, 'previousRank' => 14, 'fullname' => 'Megumi Fushiguro',    'username' => 'megumi',        'xp' => 11390, 'level' => 32, 'avatar' => null],
        ['id' => 16, 'rank' => 16, 'previousRank' => 18, 'fullname' => 'Shoto Todoroki',      'username' => 'todoroki',      'xp' => 11130, 'level' => 31, 'avatar' => null],
        ['id' => 17, 'rank' => 17, 'previousRank' => 16, 'fullname' => 'Izuku Midoriya',      'username' => 'deku',          'xp' => 10920, 'level' => 31, 'avatar' => null],
        ['id' => 18, 'rank' => 18, 'previousRank' => 17, 'fullname' => 'Kakashi Hatake',      'username' => 'kakashi',       'xp' => 10680, 'level' => 30, 'avatar' => null],
        ['id' => 19, 'rank' => 19, 'previousRank' => 22, 'fullname' => 'Emmanuel Samuel',     'username' => 'zoro_itachi43', 'xp' => 10450, 'level' => 30, 'avatar' => null, 'isCurrentUser' => true],
        ['id' => 20, 'rank' => 20, 'previousRank' => 19, 'fullname' => 'Natsu Dragneel',      'username' => 'natsu',         'xp' => 10190, 'level' => 29, 'avatar' => null],
        ['id' => 21, 'rank' => 21, 'previousRank' => 21, 'fullname' => 'Asta',                'username' => 'asta',          'xp' => 9950,  'level' => 29, 'avatar' => null],
        ['id' => 22, 'rank' => 22, 'previousRank' => 20, 'fullname' => 'Yuno',                'username' => 'yuno',          'xp' => 9720,  'level' => 28, 'avatar' => null],
        ['id' => 23, 'rank' => 23, 'previousRank' => 25, 'fullname' => 'Sung Jinwoo',         'username' => 'jinwoo',        'xp' => 9480,  'level' => 28, 'avatar' => null],
        ['id' => 24, 'rank' => 24, 'previousRank' => 23, 'fullname' => 'Frieren',             'username' => 'frieren',       'xp' => 9240,  'level' => 27, 'avatar' => null],
        ['id' => 25, 'rank' => 25, 'previousRank' => 24, 'fullname' => 'Maomao',              'username' => 'maomao',        'xp' => 9010,  'level' => 27, 'avatar' => null],
    ];
}

function akdFindPlayerByUsername(string $username): ?array {
    foreach (akdLeaderboardRoster() as $player) {
        if (strcasecmp($player['username'], $username) === 0) {
            return $player;
        }
    }
    return null;
}

/** Deterministic pseudo-random int in [0, $mod) from a string seed. */
function akdSeedNumber(string $seed, int $mod): int {
    return $mod > 0 ? (crc32($seed) % $mod) : 0;
}

/** Same palette/approach as profile.php's akdAvatarColor(), kept local to avoid a cross-file symbol dependency. */
function akdPlayerAvatarColor(string $seed): string {
    static $palette = [
        '#3457D5', '#0F9B8E', '#1E8E5A', '#C23B5D',
        '#5B4B8A', '#8B5FBF', '#D97B29', '#C2447A',
    ];
    return $palette[crc32($seed) % count($palette)];
}

function akdBuildPlayerProfile(array $player): array {
    $username = $player['username'];
    $level    = $player['level'];
    $rank     = $player['rank'];

    // ---- Community & trivia stats — formulaic, not hand-authored per player ----
    $votesCast       = 40 + ($level * 3) + akdSeedNumber($username . 'votes', 15);
    $comments        = 15 + ($level * 2) + akdSeedNumber($username . 'comments', 20);
    $galleryPosts    = akdSeedNumber($username . 'gallery', 6);
    $triviaCompleted = 10 + $level + akdSeedNumber($username . 'trivia', 10);
    $winRatePercent  = 30 + akdSeedNumber($username . 'winrate', 45);
    $triviaWins      = (int) round($triviaCompleted * ($winRatePercent / 100));

    // ---- Achievement badges — deterministic subset of the shared pool ----
    $achievementPool = require __DIR__ . '/achievements.php';
    $badgeCount = 3 + akdSeedNumber($username . 'badgecount', 7); // 3–9
    $badges = $achievementPool;

    // Deterministically shuffle based on username
    usort($badges, function ($a, $b) use ($username) {
        return akdSeedNumber($username . 'badge' . $a['id'], 1000)
            <=> akdSeedNumber($username . 'badge' . $b['id'], 1000);
    });

    $badges = array_slice($badges, 0, $badgeCount);

    // ---- Awards earned — deterministic subset of a small award pool ----
    $awardPool = [
        ['icon' => 'fa-solid fa-trophy',      'name' => 'Fan Favorite'],
        ['icon' => 'fa-solid fa-star',        'name' => 'Rising Talent'],
        ['icon' => 'fa-solid fa-users',       'name' => 'Community Choice'],
        ['icon' => 'fa-solid fa-check-square','name' => 'Top Voter'],
        ['icon' => 'fa-solid fa-brain',       'name' => 'Trivia Legend'],
    ];
    $awards = [];
    foreach ($awardPool as $i => $award) {
        if (akdSeedNumber($username . 'award' . $i, 100) < 35) {
            $awards[] = $award;
        }
    }

    // ---- Joined date — earlier rank loosely implies longer tenure ----
    $monthPool = ['Jan 2025', 'Feb 2025', 'Mar 2025', 'Apr 2025', 'May 2025', 'Jun 2025',
                  'Jul 2025', 'Aug 2025', 'Sep 2025', 'Oct 2025', 'Nov 2025', 'Dec 2025', 'Jan 2026'];
    $monthIndex = min((int) floor(($rank - 1) / 2), count($monthPool) - 1);
    $joinedDate = $monthPool[$monthIndex];

    // ---- Peak rank — best position they've ever held; always ≥ current rank ----
    $highestRank = max(1, $rank - akdSeedNumber($username . 'peak', 5));

    // ---- Last active ----
    $lastActivePool = ['2 hours ago', '5 hours ago', 'Yesterday', '2 days ago', '3 days ago', '5 days ago', 'A week ago'];
    $lastActive = $lastActivePool[akdSeedNumber($username . 'lastactive', count($lastActivePool))];

    // ---- Challenge participation ----
    $challengesJoined = 2 + akdSeedNumber($username . 'challenges', 10);

    // ---- Recent activity — small deterministic sample, not a full log ----
    $activityPool = [
        ['icon' => 'fa-solid fa-check-square', 'title' => 'Voted in the Anime Awards', 'desc' => 'Cast a vote in an ongoing category.'],
        ['icon' => 'fa-solid fa-brain',        'title' => 'Completed a trivia round',  'desc' => 'Finished a themed trivia set.'],
        ['icon' => 'fa-solid fa-crown',        'title' => 'Won a trivia challenge',    'desc' => 'Placed first in a weekly trivia challenge.'],
        ['icon' => 'fa-solid fa-medal',        'title' => 'Earned a new badge',        'desc' => 'Unlocked another achievement.'],
        ['icon' => 'fa-solid fa-comments',     'title' => 'Joined a discussion',       'desc' => 'Commented in the community feed.'],
        ['icon' => 'fa-solid fa-images',       'title' => 'Shared fan art',            'desc' => 'Posted to the community gallery.'],
        ['icon' => 'fa-solid fa-bolt',         'title' => 'Leveled up',                'desc' => "Reached level {$level}."],
    ];
    $activityTimePool = ['2 hours ago', 'Yesterday', '3 days ago', '5 days ago', 'Last week'];

    $recentActivity = [];
    $activityCount = 3 + akdSeedNumber($username . 'activitycount', 2); // 3–4 items
    for ($i = 0; $i < $activityCount; $i++) {
        $item = $activityPool[akdSeedNumber($username . 'activity' . $i, count($activityPool))];
        $item['time'] = $activityTimePool[min($i, count($activityTimePool) - 1)];
        $recentActivity[] = $item;
    }

    return array_merge($player, [
        'votesCast'       => $votesCast,
        'comments'        => $comments,
        'galleryPosts'    => $galleryPosts,
        'triviaCompleted' => $triviaCompleted,
        'triviaWins'      => $triviaWins,
        'winRatePercent'  => $winRatePercent,
        'badges'          => $badges,
        'awards'          => $awards,
        'joinedDate'      => $joinedDate,
        'highestRank'     => $highestRank,
        'lastActive'      => $lastActive,
        'challengesJoined' => $challengesJoined,
        'recentActivity'  => $recentActivity,
    ]);
}