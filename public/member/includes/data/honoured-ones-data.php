<?php
declare(strict_types=1);

/**
 * Historical ANCA editions, mock data only. The current edition is not
 * duplicated here: it is derived live from community-awards-data.php
 * in honoured-ones-support.php, so this file only ever needs to hold
 * genuinely past years. Kept deliberately small, this is a prototype,
 * not a fabricated multi-year archive. Every winner here is drawn from
 * the existing roster in players.php, no invented people.
 *
 * @return list<array{year: int, edition: string, winners: list<array{category: string, fullname: string, username: string, accent: string}>}>
 */
return [
    [
        'year'    => 2025,
        'edition' => 'ANCA 2025',
        'winners' => [
            ['category' => 'Community MVP',       'fullname' => 'Sasuke Uchiha',  'username' => 'sasuke',  'accent' => 'gold'],
            ['category' => 'Most Active Member',   'fullname' => 'Nezuko Kamado',  'username' => 'nezuko',  'accent' => 'teal'],
            ['category' => 'Funniest Member',      'fullname' => 'Yuno',           'username' => 'yuno',    'accent' => 'sakura'],
            ['category' => 'Most Helpful Member',  'fullname' => 'Kakashi Hatake', 'username' => 'kakashi', 'accent' => 'ember'],
        ],
    ],
    [
        'year'    => 2024,
        'edition' => 'ANCA 2024',
        'winners' => [
            ['category' => 'Community MVP',          'fullname' => 'Levi Ackerman',   'username' => 'levi',   'accent' => 'violet'],
            ['category' => 'Most Active Admin',      'fullname' => 'Mikasa Ackerman', 'username' => 'mikasa', 'accent' => 'teal'],
            ['category' => 'Most Supportive Member', 'fullname' => 'Maomao',          'username' => 'maomao', 'accent' => 'crimson'],
        ],
    ],
];