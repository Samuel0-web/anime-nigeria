<?php
declare(strict_types=1);
$pageTitle = "Trivia Leaderboard | Anime Nigeria";
$pageDescription = "Learn how the Trivia Leaderboard works.";
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

    <!-- ===================================================================
     RANKING SYSTEM & FAIR PLAY
     =================================================================== -->
    <section class="an-leaderboard-rules" aria-labelledby="an-leaderboard-rules-heading">
        <div class="an-leaderboard-rules__glow" aria-hidden="true"></div>

        <div class="an-container an-leaderboard-rules__inner">
            <div class="an-leaderboard-rules__intro">
                <span class="an-eyebrow an-leaderboard-rules__eyebrow">Ranking System</span>
                <h2 class="an-leaderboard-rules__heading" id="an-leaderboard-rules-heading">Fair Competition. Real Rankings.</h2>
                <p class="an-leaderboard-rules__subheading">
                    The Anime Trivia Leaderboard rewards knowledge, speed and consistency while
                    maintaining a fair and competitive experience for every member.
                </p>
                <div class="an-leaderboard-rules__rule" aria-hidden="true"></div>
            </div>

            <div class="an-leaderboard-rules__grid">
                <article class="an-leaderboard-rules__card an-reveal" style="--i:0">
                    <span class="an-leaderboard-rules__icon"><i class="fa-solid fa-medal" aria-hidden="true"></i></span>
                    <h3 class="an-leaderboard-rules__card-title">Total Points Matter</h3>
                    <p class="an-leaderboard-rules__card-text">Your leaderboard position is determined by the total number of points you've earned across completed quizzes.</p>
                </article>

                <article class="an-leaderboard-rules__card an-reveal" style="--i:1">
                    <span class="an-leaderboard-rules__icon"><i class="fa-solid fa-bolt" aria-hidden="true"></i></span>
                    <h3 class="an-leaderboard-rules__card-title">Faster Answers Earn More</h3>
                    <p class="an-leaderboard-rules__card-text">Correct answers submitted more quickly receive higher scores than slower correct answers.</p>
                </article>

                <article class="an-leaderboard-rules__card an-reveal" style="--i:2">
                    <span class="an-leaderboard-rules__icon"><i class="fa-solid fa-bullseye" aria-hidden="true"></i></span>
                    <h3 class="an-leaderboard-rules__card-title">One Attempt Per Quiz</h3>
                    <p class="an-leaderboard-rules__card-text">Every quiz can only be completed once, ensuring a level playing field for all members.</p>
                </article>

                <article class="an-leaderboard-rules__card an-reveal" style="--i:3">
                    <span class="an-leaderboard-rules__icon"><i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i></span>
                    <h3 class="an-leaderboard-rules__card-title">Rankings Update Automatically</h3>
                    <p class="an-leaderboard-rules__card-text">Once a completed quiz has been processed, your leaderboard position updates automatically.</p>
                </article>

                <article class="an-leaderboard-rules__card an-reveal" style="--i:4">
                    <span class="an-leaderboard-rules__icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span>
                    <h3 class="an-leaderboard-rules__card-title">Fair Play Comes First</h3>
                    <p class="an-leaderboard-rules__card-text">Suspicious activity, cheating or attempts to manipulate scores may result in score removal or disqualification.</p>
                </article>

                <article class="an-leaderboard-rules__card an-reveal" style="--i:5">
                    <span class="an-leaderboard-rules__icon"><i class="fa-solid fa-rocket" aria-hidden="true"></i></span>
                    <h3 class="an-leaderboard-rules__card-title">Keep Improving</h3>
                    <p class="an-leaderboard-rules__card-text">Every new trivia challenge gives you another opportunity to earn points and move higher in the rankings.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- ===================================================================
        FREQUENTLY ASKED QUESTIONS
        =================================================================== -->
    <section class="an-leaderboard-faq an-reveal" aria-labelledby="an-leaderboard-faq-heading">
        <div class="an-container">
            <div class="an-section-heading">
                <span class="an-eyebrow">FAQ</span>
                <h2 id="an-leaderboard-faq-heading">Frequently Asked Questions</h2>
                <p>Everything you need to know about the Anime Trivia Leaderboard.</p>
            </div>

            <div class="an-leaderboard-faq__list">
                <article class="an-leaderboard-faq__item">
                    <button class="an-leaderboard-faq__question" type="button" aria-expanded="false">
                        <span>How is my leaderboard ranking calculated?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>
                    <div class="an-leaderboard-faq__answer">
                        <p>Your position is based on the total number of points you've earned from completed Anime Trivia quizzes.</p>
                    </div>
                </article>

                <article class="an-leaderboard-faq__item">
                    <button class="an-leaderboard-faq__question" type="button" aria-expanded="false">
                        <span>Do faster answers really earn more points?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>
                    <div class="an-leaderboard-faq__answer">
                        <p>Yes. Correct answers submitted more quickly receive higher scores than slower correct answers.</p>
                    </div>
                </article>

                <article class="an-leaderboard-faq__item">
                    <button class="an-leaderboard-faq__question" type="button" aria-expanded="false">
                        <span>Can I replay a quiz to improve my score?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>
                    <div class="an-leaderboard-faq__answer">
                        <p>No. Each quiz can only be attempted once to ensure fair competition.</p>
                    </div>
                </article>

                <article class="an-leaderboard-faq__item">
                    <button class="an-leaderboard-faq__question" type="button" aria-expanded="false">
                        <span>How often is the leaderboard updated?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>
                    <div class="an-leaderboard-faq__answer">
                        <p>Your ranking updates automatically after your completed quiz has been processed.</p>
                    </div>
                </article>

                <article class="an-leaderboard-faq__item">
                    <button class="an-leaderboard-faq__question" type="button" aria-expanded="false">
                        <span>What happens if two members finish with the same score?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>
                    <div class="an-leaderboard-faq__answer">
                        <p>If members have identical scores, additional ranking criteria, such as overall performance and completion timing, may be used to determine their positions.</p>
                    </div>
                </article>

                <article class="an-leaderboard-faq__item">
                    <button class="an-leaderboard-faq__question" type="button" aria-expanded="false">
                        <span>How does Anime Nigeria prevent cheating?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>
                    <div class="an-leaderboard-faq__answer">
                        <p>Anime Nigeria monitors quiz activity and may review or remove suspicious scores to help maintain a fair and competitive leaderboard.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>