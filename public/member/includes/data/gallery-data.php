<?php
/**
 * Simulated Gallery data for the member Gallery page. Structure mirrors
 * what a service/repository would return later, swap this array for
 * real data without touching gallery.php or the gallery JavaScript.
 *
 * 'category' drives the filter pills (see gallery-support.php,
 * akd_gallery_categories()), it is not a separate hardcoded list.
 *
 * 'width'/'height' are the source image's approximate natural pixel
 * dimensions. They are used to size each item in the masonry layout
 * before the image itself has loaded, and set as the <img> width/height
 * attributes to avoid layout shift. These are estimated placeholder
 * values (the project has no confirmed dimensions for these assets
 * recorded anywhere yet), update them if the real dimensions differ.
 */

$galleryItems = [
    [
        'id'       => 1,
        'image'    => '/uploads/frieren-poster.webp',
        'title'    => 'ANAA 2026 Winners Revealed',
        'caption'  => "Frieren: Beyond Journey's End took home Best Anime at this year's Anime Nigeria Anime Awards.",
        'category' => 'Awards',
        'date'     => 'August 2026',
        'alt'      => "Frieren: Beyond Journey's End, ANAA 2026 Best Anime winner",
        'width'    => 680,
        'height'   => 1000,
    ],
    [
        'id'       => 2,
        'image'    => '/uploads/1783455914832.png',
        'title'    => 'ANCA 2026 Nominations Open',
        'caption'  => 'Members put forward the names they think deserve recognition at the Anime Nigeria Community Awards.',
        'category' => 'Awards',
        'date'     => 'August 2026',
        'alt'      => 'Members of the Anime Nigeria community',
        'width'    => 1200,
        'height'   => 800,
    ],
    [
        'id'       => 3,
        'image'    => '/uploads/bleach-tybw-poster.webp',
        'title'    => 'ANAA 2026 Best Opening',
        'caption'  => 'Bleach: Thousand Year Blood War took the Best Opening category this season.',
        'category' => 'Awards',
        'date'     => 'August 2026',
        'alt'      => 'Bleach: Thousand-Year Blood War, ANAA 2026 Best Opening winner',
        'width'    => 700,
        'height'   => 980,
    ],
    [
        'id'       => 4,
        'image'    => '/uploads/tensura-poster.webp',
        'title'    => 'Anime Voice Challenge, Round 1',
        'caption'  => 'Community members recorded their favourite anime lines for the Anime Voice Challenge.',
        'category' => 'Challenges',
        'date'     => 'September 2026',
        'alt'      => 'Anime Voice Challenge submissions',
        'width'    => 720,
        'height'   => 1080,
    ],
    [
        'id'       => 5,
        'image'    => '/uploads/clevatess-poster.webp',
        'title'    => 'Anime Art Challenge Highlights',
        'caption'  => 'A look at some of the original artwork submitted for the Anime Art Challenge.',
        'category' => 'Challenges',
        'date'     => 'September 2026',
        'alt'      => 'Anime Art Challenge submissions',
        'width'    => 640,
        'height'   => 960,
    ],
    [
        'id'       => 6,
        'image'    => '/uploads/kill-blue-poster.webp',
        'title'    => 'Community Challenge, Final Vote',
        'caption'  => 'The community narrowed the field down to two finalists in the latest Community Challenge.',
        'category' => 'Challenges',
        'date'     => 'September 2026',
        'alt'      => 'Community Challenge final round',
        'width'    => 660,
        'height'   => 900,
    ],
    [
        'id'       => 7,
        'image'    => '/uploads/liar-game-poster.webp',
        'title'    => 'Anime Trivia Night',
        'caption'  => 'Members went head to head during a weekly live Trivia Night.',
        'category' => 'Events',
        'date'     => 'August 2026',
        'alt'      => 'Anime Trivia Night event',
        'width'    => 700,
        'height'   => 1050,
    ],
    [
        'id'       => 8,
        'image'    => '/uploads/black-torch-poster.webp',
        'title'    => 'Seasonal Trivia, Summer Arc',
        'caption'  => 'The Summer Arc season of Trivia kicked off with a full house.',
        'category' => 'Events',
        'date'     => 'August 2026',
        'alt'      => 'Seasonal Trivia, Summer Arc event',
        'width'    => 640,
        'height'   => 900,
    ],
    [
        'id'       => 9,
        'image'    => '/uploads/frieren-poster.webp',
        'title'    => 'Live Trivia Grand Finale',
        'caption'  => 'The Trivia season wrapped up with a live grand finale round.',
        'category' => 'Events',
        'date'     => 'August 2026',
        'alt'      => 'Live Trivia Grand Finale event',
        'width'    => 680,
        'height'   => 1000,
    ],
    [
        'id'       => 10,
        'image'    => '/uploads/1783455914832.png',
        'title'    => 'Anime Nigeria Community Spotlight',
        'caption'  => 'A look at some of the members who make Anime Nigeria what it is.',
        'category' => 'Community',
        'date'     => 'August 2026',
        'alt'      => 'Members of the Anime Nigeria community',
        'width'    => 1200,
        'height'   => 800,
    ],
    [
        'id'       => 11,
        'image'    => '/uploads/bleach-tybw-poster.webp',
        'title'    => 'Honoured Ones 2026',
        'caption'  => 'New members joined the Honoured Ones for their contributions to the community.',
        'category' => 'Community',
        'date'     => 'August 2026',
        'alt'      => 'Honoured Ones recognition',
        'width'    => 700,
        'height'   => 980,
    ],
    [
        'id'       => 12,
        'image'    => '/uploads/tensura-poster.webp',
        'title'    => 'Community Meetup Moments',
        'caption'  => 'Members came together to celebrate their favourite anime and characters.',
        'category' => 'Community',
        'date'     => 'September 2026',
        'alt'      => 'Anime Nigeria community meetup',
        'width'    => 720,
        'height'   => 1080,
    ],
];