<?php
/**
 * The one-category-at-a-time ANCA voting experience. All interactivity
 * (category navigation, nominee selection, the preparing/review/submit
 * flow, and the shared confirm dialog at final submission) runs
 * client-side in resources/js/member/community-awards-voting.js. This
 * partial hands that script the category and nominee data via a JSON
 * script tag, plus the static DOM shell it renders into. Nothing here
 * persists a real vote, it is in-memory only, matching the rest of
 * ANCA. Voting follows the same one vote per entry per day rule as
 * ANAA, see the daily note below and community-awards-voting.js.
 *
 * Expects the following to be defined by the parent view (voting.php):
 *
 * @var array{
 *     phase: string,
 *     categories: list<array{slug: string, name: string, prompt: string, nominees: list<array{id: string, fullname: string, username: string, accent: string, reason?: string}>}>,
 *     voting: array{allowChangeBeforeClose: bool},
 * } $ancaOverview
 */

$winnersStatus = akd_anca_stage_status('winners', $ancaOverview['phase']);

$completion = $winnersStatus['state'] === 'available'
    ? [
        'description' => "You've cast today's votes across the available ANCA categories. Thank you for taking part.",
        'ctaLabel'    => 'View Winners',
        'ctaLink'     => '/member/community/awards/winners',
    ]
    : [
        'description' => "You've cast today's votes across the available ANCA categories. Thank you for taking part. You can come back and vote again once per day while voting remains open.",
        'ctaLabel'    => 'ANCA Overview',
        'ctaLink'     => '/member/community/awards/overview',
    ];

$voteData = [
    'categories' => array_map(
        static fn(array $c): array => [
            'slug'     => $c['slug'],
            'name'     => $c['name'],
            'prompt'   => $c['prompt'],
            'nominees' => $c['nominees'],
        ],
        $ancaOverview['categories']
    ),
    'allowChange' => $ancaOverview['voting']['allowChangeBeforeClose'],
    'completion'  => $completion,
];
?>
<header class="akd-anca-vote-topline" data-anca-vote-topline>
    <div class="akd-anca-vote-topline__brand">
        <p class="akd-anca-vote-topline__kicker">ANCA 2026</p>
        <p class="akd-anca-vote-topline__label">Community Awards Voting</p>
    </div>

    <div class="akd-anca-vote-topline__context">
        <p class="akd-anca-vote-topline__position" data-anca-vote-position></p>
        <a href="/member/community/awards/overview" class="akd-anca-vote-topline__back">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Overview
        </a>
    </div>
</header>

<section class="akd-anca-vote" data-anca-vote>
    <script type="application/json" data-anca-vote-data><?= json_encode($voteData, JSON_UNESCAPED_SLASHES) ?></script>

    <div class="akd-anca-vote-stage" data-anca-vote-stage>
        <header class="akd-anca-vote-stage__header">
            <button type="button" class="akd-anca-vote-nav-btn" data-anca-vote-prev aria-label="Previous category">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
            </button>

            <div class="akd-anca-vote-stage__center">
                <h1 class="akd-anca-vote-stage__title" data-anca-vote-title></h1>
                <p class="akd-anca-vote-stage__question" data-anca-vote-question></p>
            </div>

            <button type="button" class="akd-anca-vote-nav-btn" data-anca-vote-next aria-label="Next category">
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </button>
        </header>

        <ul class="akd-anca-nominees" data-anca-nominees role="list"></ul>

        <div class="akd-anca-vote-action" data-anca-vote-action></div>
    </div>

    <ol class="akd-anca-vote-dots" data-anca-vote-dots aria-label="Category progress"></ol>

    <div class="akd-anca-vote-preparing" data-anca-vote-preparing hidden role="status" aria-live="polite">
        <svg class="akd-anca-spinner" viewBox="0 0 50 50" aria-hidden="true">
            <circle class="akd-anca-spinner__track" cx="25" cy="25" r="20" fill="none" stroke-width="4"></circle>
            <circle class="akd-anca-spinner__arc" cx="25" cy="25" r="20" fill="none" stroke-width="4"></circle>
        </svg>
        <p class="akd-anca-vote-preparing__label">Preparing your review</p>
    </div>

    <div class="akd-anca-vote-review" data-anca-vote-review hidden>
        <h2 class="akd-anca-vote-review__title">Review your votes</h2>
        <p class="akd-anca-vote-review__desc">Check your picks below. You can change any of them before submitting.</p>
        <ul class="akd-anca-vote-review__list" data-anca-vote-review-list></ul>
        <div class="akd-anca-vote-review__actions">
            <button type="button" class="akd-award-btn akd-award-btn--primary akd-award-btn--lg" data-anca-submit-votes>
                Submit votes
            </button>
        </div>
    </div>

    <div class="akd-anca-vote-success-screen" data-anca-vote-success hidden>
        <div class="akd-anca-vote-success-screen__icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></div>
        <h2 class="akd-anca-vote-success-screen__title">Votes recorded</h2>
        <p class="akd-anca-vote-success-screen__desc" data-anca-vote-success-desc></p>
        <div class="akd-anca-vote-success-screen__actions">
            <a href="#" class="akd-award-btn akd-award-btn--primary" data-anca-vote-success-cta></a>
        </div>
    </div>
</section>

<p class="akd-anca-vote-daily-note" data-anca-vote-daily-note>
    <i class="fa-solid fa-rotate" aria-hidden="true"></i>
    Voting is daily. You can vote once for an entry in each category every day while 
    voting remains open.
</p>