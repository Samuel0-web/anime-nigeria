<?php
/**
 * The interactive voting wizard. All step logic (category navigation,
 * selection, review, submission, download) runs client-side in
 * resources/js/member/awards-voting.js. This partial's only job is to
 * hand that script a pre-shaped dataset via a JSON script tag, plus the
 * static DOM shell the script renders into.
 *
 * Expects the following to be defined by the parent view (voting.php):
 *
 * @var array{
 *     categories: list<array{
 *         slug: string, name: string, description: string, accent: string,
 *         nominees: list<array{id: string, name: string, image: string|null}>,
 *     }>,
 * } $awardsOverview
 */

$categories = array_map(
    static fn(array $category): array => [
        'slug'        => $category['slug'],
        'name'        => $category['name'],
        'description' => $category['description'],
        'nominees'    => $category['nominees'],
    ],
    $awardsOverview['categories']
);

$voteData = ['categories' => $categories];
?>
<div class="akd-vote-topline">
    <p class="akd-vote-topline__label">ANAA 2026 
        <span class="akd-vote-topline__dot">&middot;</span> Voting
    </p>

    <a href="/member/awards" class="akd-vote-topline__back">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Overview
    </a>
</div>

<section class="akd-vote" data-vote-app>
    <script type="application/json" data-vote-data>
        <?= json_encode($voteData, JSON_UNESCAPED_SLASHES) ?>
    </script>

    <div class="akd-vote-step" data-vote-step>
        <header class="akd-vote-step__header">
            <button type="button" class="akd-vote-step__nav-btn
                akd-vote-step__nav-btn--prev" data-vote-prev
                aria-label="Previous category"
            >
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                <span class="akd-vote-step__nav-label">Previous</span>
            </button>

            <div class="akd-vote-step__center">
                <div class="akd-vote-step__title-row">
                    <h1 class="akd-vote-step__title" data-vote-title></h1>

                    <span class="akd-vote-tooltip-anchor" data-vote-tooltip-anchor>
                        <button type="button" class="akd-vote-step__info"
                            data-vote-info aria-haspopup="true" aria-expanded="false"
                            aria-label="What does this category mean?"
                        >
                            <i class="fa-solid fa-question" aria-hidden="true"></i>
                        </button>

                        <div class="akd-vote-tooltip" data-vote-tooltip role="tooltip"
                            hidden
                        >
                        </div>
                    </span>
                </div>

                <div class="akd-vote-progress">
                    <div class="akd-vote-progress__bar">
                        <div class="akd-vote-progress__fill" data-vote-progress-fill></div>
                    </div>
                    <p class="akd-vote-progress__label" data-vote-progress-label></p>
                </div>
            </div>

            <button type="button" class="akd-vote-step__nav-btn
                akd-vote-step__nav-btn--next" data-vote-next aria-label="Next category"
            >
                <span class="akd-vote-step__nav-label">Next</span>
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </button>
        </header>

        <p class="akd-vote-step__hint" data-vote-hint hidden>Select a nominee to continue.</p>
        <ul class="akd-vote-nominees" data-vote-nominees role="list"></ul>
    </div>

    <div class="akd-vote-review" data-vote-review hidden>
        <h2 class="akd-vote-review__title">Review Your Votes</h2>

        <p class="akd-vote-review__desc">
            Take a look before you cast today's votes. Tap any category to change your pick.
        </p>

        <ul class="akd-vote-review__grid" data-vote-review-grid></ul>

        <div class="akd-vote-review__actions">
            <button type="button" class="akd-award-btn akd-award-btn--primary
                akd-award-btn--lg" data-vote-submit
            >
                Cast Today's Votes
            </button>
        </div>
    </div>

    <div class="akd-vote-success" data-vote-success hidden>
        <div class="akd-vote-success__icon">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        </div>

        <h2 class="akd-vote-success__title">Your vote has been submitted.</h2>

        <p class="akd-vote-success__desc">
            You can vote again each day while ANAA voting remains open.
            Today's voting closes at midnight.
        </p>

        <div class="akd-vote-success__actions">
            <button type="button" class="akd-award-btn akd-award-btn--primary
                akd-award-btn--lg" data-vote-download
            >
                <i class="fa-solid fa-download" aria-hidden="true"></i> Download Vote Card
            </button>

            <a href="/member/awards" class="akd-award-btn akd-award-btn--ghost">
                ANAA Overview
            </a>
        </div>
    </div>
</section>

<!-- Off-screen template captured by html2canvas for the downloadable vote card. -->
<div class="akd-vote-card" data-vote-card aria-hidden="true">
    <div class="akd-vote-card__bg"></div>
    <div class="akd-vote-card__shape akd-vote-card__shape--tr" aria-hidden="true"></div>
    <div class="akd-vote-card__shape akd-vote-card__shape--bl" aria-hidden="true"></div>
    <div class="akd-vote-card__shape akd-vote-card__shape--mid" aria-hidden="true"></div>

    <div class="akd-vote-card__layout">
        <div class="akd-vote-card__votes">
            <div class="akd-vote-card__grid" data-vote-card-grid></div>
        </div>

        <div class="akd-vote-card__brand">
            <img src="/uploads/logos/Group-2.png" alt="" class="akd-vote-card__logo">
            <p class="akd-vote-card__brand-year">ANAA 2026</p>
            <span class="akd-vote-card__brand-rule" aria-hidden="true"></span>
            <p class="akd-vote-card__brand-label">My Votes</p>
        </div>
    </div>
</div>