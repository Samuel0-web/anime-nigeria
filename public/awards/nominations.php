<?php
declare(strict_types=1);
$pageTitle = "Award Nominations | Anime Nigeria";
$pageDescription = "Submit your nominations for the Anime Nigeria Awards and help celebrate the best in anime!";
require_once __DIR__ . '/../../includes/header.php';
?>

<main id="main-content">
    <!-- ===================================================================
         HERO
         =================================================================== -->
    <section class="an-nomination-hero" aria-labelledby="an-awards-hero-heading">
        <div class="an-nomination-hero__glow" aria-hidden="true"></div>

        <div class="an-container an-nomination-hero__panel">
            <div class="an-nomination-hero__content an-reveal" style="--i:0">
                <span class="an-eyebrow an-nomination-hero__eyebrow">Anime Nigeria Anime Awards (ANAA)</span>
                <h1 class="an-nomination-hero__heading" id="an-awards-hero-heading">Nominate Your Favourite Anime Moments</h1>

                <p class="an-nomination-hero__paragraph">
                    Celebrate the anime, characters, creators and unforgettable moments that defined
                    the year. Submit your nominations and help shape the Anime Nigeria Awards before
                    community voting begins.
                </p>

                <span class="an-nomination-hero__status">
                    <span class="an-nomination-hero__status-dot" aria-hidden="true"></span>
                    Nominations Open
                </span>

                <div class="an-nomination-hero__actions">
                    <a href="#nominate" class="an-btn an-btn--primary">Start Nominating</a>
                    <a href="#categories" class="an-btn an-btn--ghost">View Categories</a>
                </div>

                <ul class="an-nomination-hero__trust">
                    <li class="an-nomination-hero__trust-item">
                        <i class="fa-solid fa-trophy an-nomination-hero__trust-icon" aria-hidden="true"></i>
                        Community Driven
                    </li>
                    <li class="an-nomination-hero__trust-item">
                        <i class="fa-solid fa-scale-balanced an-nomination-hero__trust-icon" aria-hidden="true"></i>
                        Fair Review Process
                    </li>
                    <li class="an-nomination-hero__trust-item">
                        <i class="fa-solid fa-layer-group an-nomination-hero__trust-icon" aria-hidden="true"></i>
                        Multiple Award Categories
                    </li>
                </ul>
            </div>

            <div class="an-nomination-hero__visual an-reveal" style="--i:1">
                <div class="an-nomination-hero__image-wrapper">
                    <img src="/uploads/upscalemedia-transformed (2).png"
                        alt="Abstract crystal award sculpture surrounded by premium glass panels."
                        class="an-nomination-hero__image" loading="eager" fetchpriority="high"
                        decoding="async">
                </div>
            </div>
        </div>
    </section>

    <!-- ===================================================================
         HOW NOMINATIONS WORK
         =================================================================== -->
    <section class="an-awards-steps" aria-labelledby="an-awards-steps-heading">
        <div class="an-awards-steps__glow" aria-hidden="true"></div>

        <div class="an-container an-awards-steps__inner">
            <div class="an-awards-steps__intro">
                <span class="an-eyebrow an-awards-steps__eyebrow">How It Works</span>
                <h2 class="an-awards-steps__heading" id="an-awards-steps-heading">Four Simple Steps</h2>
                <p class="an-awards-steps__subheading">
                    Submitting a nomination takes just a few moments. Once nominations close, eligible
                    entries are reviewed before official community voting begins.
                </p>
                <div class="an-awards-steps__rule" aria-hidden="true"></div>
            </div>

            <div class="an-awards-steps__timeline">
                <div class="an-awards-steps__track" aria-hidden="true"></div>

                <ol class="an-awards-steps__list">
                    <li class="an-awards-steps__step an-reveal" style="--i:0">
                        <span class="an-awards-steps__icon">
                            <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                            <span class="an-awards-steps__number" aria-hidden="true">01</span>
                        </span>
                        <h3 class="an-awards-steps__title">Choose a Category</h3>
                        <p class="an-awards-steps__text">Browse the available award categories and select the one you'd like to nominate for.</p>
                    </li>

                    <li class="an-awards-steps__step an-reveal" style="--i:1">
                        <span class="an-awards-steps__icon">
                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                            <span class="an-awards-steps__number" aria-hidden="true">02</span>
                        </span>
                        <h3 class="an-awards-steps__title">Submit Your Nomination</h3>
                        <p class="an-awards-steps__text">Enter your chosen nominee together with a short reason for your nomination.</p>
                    </li>

                    <li class="an-awards-steps__step an-reveal" style="--i:2">
                        <span class="an-awards-steps__icon">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <span class="an-awards-steps__number" aria-hidden="true">03</span>
                        </span>
                        <h3 class="an-awards-steps__title">Review Process</h3>
                        <p class="an-awards-steps__text">Eligible nominations are reviewed and duplicate entries are consolidated where necessary.</p>
                    </li>

                    <li class="an-awards-steps__step an-reveal" style="--i:3">
                        <span class="an-awards-steps__icon">
                            <i class="fa-solid fa-square-poll-vertical" aria-hidden="true"></i>
                            <span class="an-awards-steps__number" aria-hidden="true">04</span>
                        </span>
                        <h3 class="an-awards-steps__title">Community Voting</h3>
                        <p class="an-awards-steps__text">Official nominees move on to the voting stage where the Anime Nigeria community decides the winners.</p>
                    </li>
                </ol>
            </div>
        </div>
    </section>

    <!-- ===================================================================
     NOMINATION CATEGORIES
     =================================================================== -->
    <section class="an-awards-categories" id="categories" aria-labelledby="an-awards-categories-heading">
        <div class="an-awards-categories__glow" aria-hidden="true"></div>

        <div class="an-container an-awards-categories__inner">
            <div class="an-awards-categories__intro">
                <span class="an-eyebrow an-awards-categories__eyebrow">Award Categories</span>
                <h2 class="an-awards-categories__heading" id="an-awards-categories-heading">Choose Where Your Nomination Belongs</h2>
                <p class="an-awards-categories__subheading">
                    Explore every award category below. Select the one that best matches your nomination,
                    then complete the nomination form beneath.
                </p>
                <div class="an-awards-categories__rule" aria-hidden="true"></div>
            </div>

            <div class="an-awards-categories__grid" role="radiogroup" aria-label="Award categories">
                <label class="an-awards-categories__tile an-reveal" style="--i:0">
                    <input type="radio" name="category-preview" value="anime-of-the-year" class="an-awards-categories__tile-input" checked>
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-crown" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Anime of the Year</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:1">
                    <input type="radio" name="category-preview" value="best-action-anime" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-bolt" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Action Anime</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:2">
                    <input type="radio" name="category-preview" value="best-adventure-anime" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-mountain" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Adventure Anime</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:3">
                    <input type="radio" name="category-preview" value="best-comedy-anime" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-face-laugh-beam" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Comedy Anime</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:4">
                    <input type="radio" name="category-preview" value="best-drama-anime" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-masks-theater" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Drama Anime</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:5">
                    <input type="radio" name="category-preview" value="best-fantasy-anime" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Fantasy Anime</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:6">
                    <input type="radio" name="category-preview" value="best-romance-anime" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-heart" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Romance Anime</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:7">
                    <input type="radio" name="category-preview" value="best-slice-of-life-anime" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-leaf" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Slice of Life Anime</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:8">
                    <input type="radio" name="category-preview" value="best-mystery-anime" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Mystery Anime</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:9">
                    <input type="radio" name="category-preview" value="best-sci-fi-anime" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-satellite" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Sci-Fi Anime</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:10">
                    <input type="radio" name="category-preview" value="best-opening" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-music" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Opening</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:11">
                    <input type="radio" name="category-preview" value="best-ending" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-flag-checkered" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Ending</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:12">
                    <input type="radio" name="category-preview" value="best-soundtrack" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-headphones" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Soundtrack</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:13">
                    <input type="radio" name="category-preview" value="best-animation" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-film" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Animation</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:14">
                    <input type="radio" name="category-preview" value="best-studio" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-building" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Studio</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:15">
                    <input type="radio" name="category-preview" value="best-character" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-user" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Character</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:16">
                    <input type="radio" name="category-preview" value="best-protagonist" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-user-shield" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Protagonist</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:17">
                    <input type="radio" name="category-preview" value="best-antagonist" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-user-ninja" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Antagonist</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:18">
                    <input type="radio" name="category-preview" value="best-supporting-character" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-user-group" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Supporting Character</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:19">
                    <input type="radio" name="category-preview" value="best-voice-performance" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-microphone" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Voice Performance</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:20">
                    <input type="radio" name="category-preview" value="best-movie" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-ticket" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Movie</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:21">
                    <input type="radio" name="category-preview" value="best-new-anime" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-sparkles" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best New Anime</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:22">
                    <input type="radio" name="category-preview" value="best-continuing-series" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Best Continuing Series</span>
                </label>

                <label class="an-awards-categories__tile an-reveal" style="--i:23">
                    <input type="radio" name="category-preview" value="community-choice-award" class="an-awards-categories__tile-input">
                    <span class="an-awards-categories__tile-icon"><i class="fa-solid fa-people-group" aria-hidden="true"></i></span>
                    <span class="an-awards-categories__tile-name">Community Choice Award</span>
                </label>
            </div>
        </div>
    </section>

    <!-- ===================================================================
        NOMINATION FORM
        =================================================================== -->
    <section class="an-awards-form" id="nominate" aria-labelledby="an-awards-form-heading">
        <div class="an-awards-form__glow" aria-hidden="true"></div>

        <div class="an-container an-awards-form__inner">
            <div class="an-awards-form__intro">
                <span class="an-eyebrow an-awards-form__eyebrow">Submit Nomination</span>
                <h2 class="an-awards-form__heading" id="an-awards-form-heading">Cast Your Nomination</h2>
                <p class="an-awards-form__subheading">
                    Tell us who deserves recognition. Every valid nomination helps shape the official
                    shortlist for the Anime Nigeria Awards.
                </p>
                <div class="an-awards-form__rule" aria-hidden="true"></div>
            </div>

            <div class="an-awards-form__stage an-reveal" style="--i:0">

                <form class="an-awards-form__card an-awards-form__card--locked" novalidate inert aria-hidden="true">
                    <div class="an-awards-form__field">
                        <span class="an-awards-form__label" id="nom-category-label">Award Category</span>

                        <div class="an-awards-dropdown">
                            <input type="hidden" id="nom-category-value" name="award_category" value="anime-of-the-year">

                            <button
                                type="button"
                                class="an-awards-dropdown__trigger"
                                id="nom-category-trigger"
                                aria-haspopup="listbox"
                                aria-expanded="false"
                                aria-controls="nom-category-panel"
                                aria-labelledby="nom-category-label"
                            >
                                <span class="an-awards-dropdown__trigger-icon"><i class="fa-solid fa-crown" aria-hidden="true"></i></span>
                                <span class="an-awards-dropdown__trigger-text">Anime of the Year</span>
                                <i class="fa-solid fa-chevron-down an-awards-dropdown__trigger-caret" aria-hidden="true"></i>
                            </button>

                            <div class="an-awards-dropdown__panel" id="nom-category-panel">
                                <div class="an-awards-dropdown__search">
                                    <i class="fa-solid fa-magnifying-glass an-awards-dropdown__search-icon" aria-hidden="true"></i>
                                    <input type="text" class="an-awards-dropdown__search-input" placeholder="Search categories…" aria-label="Search award categories">
                                </div>

                                <ul class="an-awards-dropdown__list" id="nom-category-listbox" role="listbox" aria-labelledby="nom-category-label">
                                    <li class="an-awards-dropdown__option" role="option" data-value="anime-of-the-year" data-label="Anime of the Year" data-icon="fa-crown" tabindex="0" aria-selected="true">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-crown" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Anime of the Year</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-action-anime" data-label="Best Action Anime" data-icon="fa-bolt" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-bolt" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Action Anime</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-adventure-anime" data-label="Best Adventure Anime" data-icon="fa-mountain" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-mountain" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Adventure Anime</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-comedy-anime" data-label="Best Comedy Anime" data-icon="fa-face-laugh-beam" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-face-laugh-beam" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Comedy Anime</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-drama-anime" data-label="Best Drama Anime" data-icon="fa-masks-theater" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-masks-theater" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Drama Anime</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-fantasy-anime" data-label="Best Fantasy Anime" data-icon="fa-wand-magic-sparkles" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Fantasy Anime</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-romance-anime" data-label="Best Romance Anime" data-icon="fa-heart" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-heart" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Romance Anime</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-slice-of-life-anime" data-label="Best Slice of Life Anime" data-icon="fa-leaf" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-leaf" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Slice of Life Anime</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-mystery-anime" data-label="Best Mystery Anime" data-icon="fa-magnifying-glass" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Mystery Anime</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-sci-fi-anime" data-label="Best Sci-Fi Anime" data-icon="fa-satellite" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-satellite" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Sci-Fi Anime</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-opening" data-label="Best Opening" data-icon="fa-music" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-music" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Opening</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-ending" data-label="Best Ending" data-icon="fa-flag-checkered" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-flag-checkered" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Ending</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-soundtrack" data-label="Best Soundtrack" data-icon="fa-headphones" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-headphones" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Soundtrack</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-animation" data-label="Best Animation" data-icon="fa-film" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-film" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Animation</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-studio" data-label="Best Studio" data-icon="fa-building" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-building" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Studio</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-character" data-label="Best Character" data-icon="fa-user" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-user" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Character</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-protagonist" data-label="Best Protagonist" data-icon="fa-user-shield" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-user-shield" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Protagonist</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-antagonist" data-label="Best Antagonist" data-icon="fa-user-ninja" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-user-ninja" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Antagonist</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-supporting-character" data-label="Best Supporting Character" data-icon="fa-user-group" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-user-group" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Supporting Character</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-voice-performance" data-label="Best Voice Performance" data-icon="fa-microphone" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-microphone" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Voice Performance</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-movie" data-label="Best Movie" data-icon="fa-ticket" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-ticket" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Movie</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-new-anime" data-label="Best New Anime" data-icon="fa-sparkles" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-sparkles" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best New Anime</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="best-continuing-series" data-label="Best Continuing Series" data-icon="fa-arrows-rotate" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Best Continuing Series</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                    <li class="an-awards-dropdown__option" role="option" data-value="community-choice-award" data-label="Community Choice Award" data-icon="fa-people-group" tabindex="-1" aria-selected="false">
                                        <span class="an-awards-dropdown__option-icon"><i class="fa-solid fa-people-group" aria-hidden="true"></i></span>
                                        <span class="an-awards-dropdown__option-text">Community Choice Award</span>
                                        <i class="fa-solid fa-check an-awards-dropdown__option-check" aria-hidden="true"></i>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="an-awards-form__field">
                        <label class="an-awards-form__label" for="nom-nominee">Nominee</label>
                        <input
                            type="text"
                            id="nom-nominee"
                            name="nominee"
                            class="an-awards-form__input an-awards-form__input--large"
                            placeholder="Enter the anime, character, creator or title."
                        >
                    </div>

                    <div class="an-awards-form__field">
                        <label class="an-awards-form__label" for="nom-reason">Reason for Nomination</label>
                        <textarea
                            id="nom-reason"
                            name="reason"
                            class="an-awards-form__textarea"
                            rows="5"
                            maxlength="500"
                            placeholder="Tell us why this nominee deserves to win."
                        ></textarea>
                        <span class="an-awards-form__counter" id="nom-reason-counter">0 / 500</span>
                    </div>

                    <label class="an-awards-form__agreement">
                        <input type="checkbox" id="nom-agreement" name="agreement" class="an-awards-form__checkbox-input">
                        <span class="an-awards-form__checkbox-box" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                        <span class="an-awards-form__agreement-text">I confirm this nomination follows the Anime Nigeria Awards guidelines and is submitted in good faith.</span>
                    </label>

                    <button type="submit" class="an-btn an-btn--primary an-awards-form__submit" id="nom-submit" disabled aria-disabled="true">Submit Nomination</button>
                </form>

                <div class="an-awards-form__overlay" role="region" aria-label="Sign in to submit a nomination">
                    <div class="an-awards-form__overlay-card">
                        <div class="an-awards-form__overlay-glow" aria-hidden="true"></div>

                        <span class="an-eyebrow an-awards-form__overlay-eyebrow">Members Only</span>
                        <h3 class="an-awards-form__overlay-heading">Sign In to Cast Your Nomination</h3>
                        <p class="an-awards-form__overlay-text">
                            Create an Anime Nigeria account or sign in to submit nominations and help shape this
                            year's Anime Nigeria Awards.
                        </p>

                        <div class="an-awards-form__overlay-actions">
                            <a href="/login" class="an-btn an-btn--primary">Sign In</a>
                            <a href="/join" class="an-btn an-btn--ghost">Create Free Account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================================================================
     NOMINATION GUIDELINES
     =================================================================== -->
    <section class="an-awards-guidelines" aria-labelledby="an-awards-guidelines-heading">
        <div class="an-awards-guidelines__glow" aria-hidden="true"></div>

        <div class="an-container an-awards-guidelines__inner">
            <div class="an-awards-guidelines__intro">
                <span class="an-eyebrow an-awards-guidelines__eyebrow">Guidelines</span>
                <h2 class="an-awards-guidelines__heading" id="an-awards-guidelines-heading">Nominate Fairly. Celebrate Greatness.</h2>
                <p class="an-awards-guidelines__subheading">
                    To keep the Anime Nigeria Awards fair and enjoyable for everyone, please review these
                    simple nomination guidelines before submitting your entry.
                </p>
                <div class="an-awards-guidelines__rule" aria-hidden="true"></div>
            </div>

            <div class="an-awards-guidelines__grid">
                <article class="an-awards-guidelines__card an-reveal" style="--i:0">
                    <span class="an-awards-guidelines__icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
                    <h3 class="an-awards-guidelines__card-title">One Nomination Per Submission</h3>
                    <p class="an-awards-guidelines__card-text">Each submission should contain only one nominee for one award category.</p>
                </article>

                <article class="an-awards-guidelines__card an-reveal" style="--i:1">
                    <span class="an-awards-guidelines__icon"><i class="fa-solid fa-trophy" aria-hidden="true"></i></span>
                    <h3 class="an-awards-guidelines__card-title">Choose the Correct Category</h3>
                    <p class="an-awards-guidelines__card-text">Submit your nominee under the award category that best represents their achievement.</p>
                </article>

                <article class="an-awards-guidelines__card an-reveal" style="--i:2">
                    <span class="an-awards-guidelines__icon"><i class="fa-solid fa-handshake" aria-hidden="true"></i></span>
                    <h3 class="an-awards-guidelines__card-title">Be Honest &amp; Respectful</h3>
                    <p class="an-awards-guidelines__card-text">Nominate in good faith and avoid spam, jokes or offensive submissions.</p>
                </article>

                <article class="an-awards-guidelines__card an-reveal" style="--i:3">
                    <span class="an-awards-guidelines__icon"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i></span>
                    <h3 class="an-awards-guidelines__card-title">Eligible Entries Only</h3>
                    <p class="an-awards-guidelines__card-text">Only nominees that meet this year's eligibility criteria may be considered.</p>
                </article>

                <article class="an-awards-guidelines__card an-reveal" style="--i:4">
                    <span class="an-awards-guidelines__icon"><i class="fa-solid fa-clone" aria-hidden="true"></i></span>
                    <h3 class="an-awards-guidelines__card-title">Duplicate Entries</h3>
                    <p class="an-awards-guidelines__card-text">Multiple nominations for the same nominee may be combined during the review process.</p>
                </article>

                <article class="an-awards-guidelines__card an-reveal" style="--i:5">
                    <span class="an-awards-guidelines__icon"><i class="fa-solid fa-scale-balanced" aria-hidden="true"></i></span>
                    <h3 class="an-awards-guidelines__card-title">Final Review</h3>
                    <p class="an-awards-guidelines__card-text">Anime Nigeria reserves the right to remove invalid or ineligible nominations before voting begins.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- ===================================================================
        FREQUENTLY ASKED QUESTIONS
        =================================================================== -->
    <section class="an-nomination-faq an-reveal" aria-labelledby="an-nomination-faq-heading">
        <div class="an-container">
            <div class="an-section-heading">
                <span class="an-eyebrow">FAQ</span>
                <h2 id="an-nomination-faq-heading">Frequently Asked Questions</h2>
                <p>Everything you need to know about submitting nominations for the Anime Nigeria Awards.</p>
            </div>

            <div class="an-nomination-faq__list">

                <article class="an-nomination-faq__item">
                    <button class="an-nomination-faq__question" type="button" aria-expanded="false">
                        <span>Can I submit more than one nomination?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>

                    <div class="an-nomination-faq__answer">
                        <p>
                            Yes. You may submit multiple nominations, but each submission should focus on a
                            single category and nominee.
                        </p>
                    </div>
                </article>

                <article class="an-nomination-faq__item">
                    <button class="an-nomination-faq__question" type="button" aria-expanded="false">
                        <span>Can I nominate the same anime in different categories?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>

                    <div class="an-nomination-faq__answer">
                        <p>
                            Yes, provided the nominee is eligible for each selected category.
                        </p>
                    </div>
                </article>

                <article class="an-nomination-faq__item">
                    <button class="an-nomination-faq__question" type="button" aria-expanded="false">
                        <span>Can I edit my nomination after submitting it?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>

                    <div class="an-nomination-faq__answer">
                        <p>
                            At the moment, submitted nominations cannot be edited. Please review your
                            information carefully before submitting.
                        </p>
                    </div>
                </article>

                <article class="an-nomination-faq__item">
                    <button class="an-nomination-faq__question" type="button" aria-expanded="false">
                        <span>What happens after nominations close?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>

                    <div class="an-nomination-faq__answer">
                        <p>
                            Our team reviews all eligible submissions, consolidates duplicate nominations
                            where necessary and prepares the official shortlist for community voting.
                        </p>
                    </div>
                </article>

                <article class="an-nomination-faq__item">
                    <button class="an-nomination-faq__question" type="button" aria-expanded="false">
                        <span>Do I need an Anime Nigeria account?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>

                    <div class="an-nomination-faq__answer">
                        <p>
                            Yes. Only registered members can submit nominations to help maintain a fair and
                            secure awards process.
                        </p>
                    </div>
                </article>

                <article class="an-nomination-faq__item">
                    <button class="an-nomination-faq__question" type="button" aria-expanded="false">
                        <span>When does voting begin?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>

                    <div class="an-nomination-faq__answer">
                        <p>
                            Community voting opens after nominations have been reviewed and the official
                            nominees have been announced.
                        </p>
                    </div>
                </article>

            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php' ?>