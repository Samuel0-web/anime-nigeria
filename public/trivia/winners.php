<?php
declare(strict_types=1);
$pageTitle = "Trivia Winners | Anime Nigeria";
$pageDescription = "Learn how trivia winners are selected.";
require_once __DIR__ . '/../../includes/header.php';
?>

<main id="main-content">
    <!-- ===================================================================
         HERO
         =================================================================== -->
    <section class="an-trivia-winners-hero" aria-labelledby="an-trivia-winners-hero-heading">
        <div class="an-trivia-winners-hero__glow" aria-hidden="true"></div>

        <div class="an-container an-trivia-winners-hero__inner">
            <div class="an-trivia-winners-hero__content an-reveal" style="--i:0">
                <span class="an-eyebrow an-trivia-winners-hero__eyebrow">Anime Trivia</span>

                <h1 class="an-trivia-winners-hero__heading" id="an-trivia-winners-hero-heading">How Trivia Winners Are Chosen</h1>

                <p class="an-trivia-winners-hero__paragraph">
                    Every Anime Trivia winner earns their place through knowledge, consistency and
                    strong performances across community quizzes. Discover how the winner selection
                    process works.
                </p>

                <div class="an-trivia-winners-hero__rule" aria-hidden="true"></div>

                <div class="an-trivia-winners-hero__actions">
                    <a href="#winner-selection" class="an-btn an-btn--primary">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================================================================
         HOW TRIVIA WINNERS ARE DETERMINED
         =================================================================== -->
    <section class="an-trivia-winners-steps" id="winner-selection" aria-labelledby="an-trivia-winners-steps-heading">
        <div class="an-trivia-winners-steps__glow" aria-hidden="true"></div>

        <div class="an-container an-trivia-winners-steps__inner">
            <div class="an-trivia-winners-steps__intro">
                <span class="an-eyebrow an-trivia-winners-steps__eyebrow">Winner Selection</span>
                <h2 class="an-trivia-winners-steps__heading" id="an-trivia-winners-steps-heading">Every Victory Is Earned</h2>
                <p class="an-trivia-winners-steps__subheading">
                    Anime Trivia winners are recognised through consistent performance, verified
                    scores and fair competition across every challenge.
                </p>
                <div class="an-trivia-winners-steps__rule" aria-hidden="true"></div>
            </div>

            <div class="an-trivia-winners-steps__timeline">
                <div class="an-trivia-winners-steps__track" aria-hidden="true"></div>

                <ol class="an-trivia-winners-steps__list">
                    <li class="an-trivia-winners-steps__step an-reveal" style="--i:0">
                        <span class="an-trivia-winners-steps__icon">
                            <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                            <span class="an-trivia-winners-steps__number" aria-hidden="true">01</span>
                        </span>
                        <h3 class="an-trivia-winners-steps__title">Complete Trivia Challenges</h3>
                        <p class="an-trivia-winners-steps__text">Participate in available Anime Trivia quizzes and answer every question before time expires.</p>
                    </li>

                    <li class="an-trivia-winners-steps__step an-reveal" style="--i:1">
                        <span class="an-trivia-winners-steps__icon">
                            <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                            <span class="an-trivia-winners-steps__number" aria-hidden="true">02</span>
                        </span>
                        <h3 class="an-trivia-winners-steps__title">Earn High Scores</h3>
                        <p class="an-trivia-winners-steps__text">Correct answers and faster response times contribute to stronger overall performances.</p>
                    </li>

                    <li class="an-trivia-winners-steps__step an-reveal" style="--i:2">
                        <span class="an-trivia-winners-steps__icon">
                            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                            <span class="an-trivia-winners-steps__number" aria-hidden="true">03</span>
                        </span>
                        <h3 class="an-trivia-winners-steps__title">Score Verification</h3>
                        <p class="an-trivia-winners-steps__text">Completed quiz results are reviewed to help ensure fair competition and accurate rankings.</p>
                    </li>

                    <li class="an-trivia-winners-steps__step an-reveal" style="--i:3">
                        <span class="an-trivia-winners-steps__icon">
                            <i class="fa-solid fa-trophy" aria-hidden="true"></i>
                            <span class="an-trivia-winners-steps__number" aria-hidden="true">04</span>
                        </span>
                        <h3 class="an-trivia-winners-steps__title">Winner Recognition</h3>
                        <p class="an-trivia-winners-steps__text">Members with the strongest verified performances are officially recognised as Anime Trivia winners.</p>
                    </li>
                </ol>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>