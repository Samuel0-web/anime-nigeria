<?php
declare(strict_types=1);
$pageTitle = "Community Awards | Anime Nigeria";
$pageDescription = "Celebrating outstanding members and recognise the fans shaping Anime Nigeria";
require_once __DIR__ . '/../../includes/header.php';
?>

<main id="main-content">
    <!-- ===================================================================
         HERO
         =================================================================== -->
    <section class="an-leaderboard-hero" aria-labelledby="an-leaderboard-hero-heading">
        <div class="an-leaderboard-hero__glow" aria-hidden="true"></div>

        <div class="an-container an-leaderboard-hero__inner">
            <div class="an-leaderboard-hero__content an-reveal" style="--i:0">
                <span class="an-eyebrow an-leaderboard-hero__eyebrow">Anime Trivia</span>

                <h1 class="an-leaderboard-hero__heading" id="an-leaderboard-hero-heading">Climb the Community Leaderboard</h1>

                <p class="an-leaderboard-hero__paragraph">
                    Every Anime Trivia quiz is an opportunity to earn points, improve your ranking
                    and compete with fellow anime fans. Discover how the leaderboard works and what
                    it takes to reach the top.
                </p>

                <div class="an-leaderboard-hero__rule" aria-hidden="true"></div>

                <div class="an-leaderboard-hero__actions">
                    <a href="#how-it-works" class="an-btn an-btn--primary">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================================================================
         HOW THE LEADERBOARD WORKS
         =================================================================== -->
    <section class="an-leaderboard-steps" id="how-it-works" aria-labelledby="an-leaderboard-steps-heading">
        <div class="an-leaderboard-steps__glow" aria-hidden="true"></div>

        <div class="an-container an-leaderboard-steps__inner">
            <div class="an-leaderboard-steps__intro">
                <span class="an-eyebrow an-leaderboard-steps__eyebrow">How It Works</span>
                <h2 class="an-leaderboard-steps__heading" id="an-leaderboard-steps-heading">Every Point Counts</h2>
                <p class="an-leaderboard-steps__subheading">
                    The Anime Trivia Leaderboard rewards consistency, accuracy and quick thinking.
                    Every completed quiz helps shape your position in the rankings.
                </p>
                <div class="an-leaderboard-steps__rule" aria-hidden="true"></div>
            </div>

            <div class="an-leaderboard-steps__timeline">
                <div class="an-leaderboard-steps__track" aria-hidden="true"></div>

                <ol class="an-leaderboard-steps__list">
                    <li class="an-leaderboard-steps__step an-reveal" style="--i:0">
                        <span class="an-leaderboard-steps__icon">
                            <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                            <span class="an-leaderboard-steps__number" aria-hidden="true">01</span>
                        </span>
                        <h3 class="an-leaderboard-steps__title">Complete Trivia Quizzes</h3>
                        <p class="an-leaderboard-steps__text">Participate in available Anime Trivia challenges and answer every question before time runs out.</p>
                    </li>

                    <li class="an-leaderboard-steps__step an-reveal" style="--i:1">
                        <span class="an-leaderboard-steps__icon">
                            <i class="fa-solid fa-star" aria-hidden="true"></i>
                            <span class="an-leaderboard-steps__number" aria-hidden="true">02</span>
                        </span>
                        <h3 class="an-leaderboard-steps__title">Earn Points</h3>
                        <p class="an-leaderboard-steps__text">Correct answers contribute to your total score, while faster responses earn even more points.</p>
                    </li>

                    <li class="an-leaderboard-steps__step an-reveal" style="--i:2">
                        <span class="an-leaderboard-steps__icon">
                            <i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i>
                            <span class="an-leaderboard-steps__number" aria-hidden="true">03</span>
                        </span>
                        <h3 class="an-leaderboard-steps__title">Build Your Ranking</h3>
                        <p class="an-leaderboard-steps__text">Your accumulated points determine your position on the Anime Trivia Leaderboard.</p>
                    </li>

                    <li class="an-leaderboard-steps__step an-reveal" style="--i:3">
                        <span class="an-leaderboard-steps__icon">
                            <i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i>
                            <span class="an-leaderboard-steps__number" aria-hidden="true">04</span>
                        </span>
                        <h3 class="an-leaderboard-steps__title">Keep Competing</h3>
                        <p class="an-leaderboard-steps__text">Complete new quizzes as they become available to continue improving your ranking and challenge other community members.</p>
                    </li>
                </ol>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>