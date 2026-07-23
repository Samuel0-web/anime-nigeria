<?php
declare(strict_types=1);
$pageTitle = "Anime Trivia | Anime Nigeria";
$pageDescription = "Test your anime knowledge with Anime Trivia — timed quizzes, points, and a community leaderboard on Anime Nigeria.";
require_once __DIR__ . '/../../includes/header.php';
?>

<main id="main-content">
    <!-- ===================================================================
         HERO
         =================================================================== -->
    <section class="an-trivia-hero" aria-labelledby="an-trivia-hero-heading">
        <div class="an-trivia-hero__glow" aria-hidden="true"></div>

        <div class="an-container an-trivia-hero__inner">
            <div class="an-trivia-hero__content an-reveal" style="--i:0">
                <span class="an-eyebrow an-trivia-hero__eyebrow">Anime Trivia</span>

                <h1 class="an-trivia-hero__heading" id="an-trivia-hero-heading">Put Your Anime Knowledge to the Test</h1>

                <p class="an-trivia-hero__paragraph">
                    Challenge yourself with exciting anime quizzes, earn points for every correct
                    answer and see how you rank against fellow anime fans across the Anime Nigeria
                    community.
                </p>

                <div class="an-trivia-hero__rule" aria-hidden="true"></div>

                <div class="an-trivia-hero__actions">
                    <a href="#how-it-works" class="an-btn an-btn--primary">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================================================================
         HOW ANIME TRIVIA WORKS
         =================================================================== -->
    <section class="an-trivia-steps" id="how-it-works" aria-labelledby="an-trivia-steps-heading">
        <div class="an-trivia-steps__glow" aria-hidden="true"></div>

        <div class="an-container an-trivia-steps__inner">
            <div class="an-trivia-steps__intro">
                <span class="an-eyebrow an-trivia-steps__eyebrow">How It Works</span>
                <h2 class="an-trivia-steps__heading" id="an-trivia-steps-heading">From Quiz to Leaderboard</h2>
                <p class="an-trivia-steps__subheading">
                    Getting started is simple. Sign in, take on exciting trivia challenges, earn
                    points and compete with other anime fans on the leaderboard.
                </p>
                <div class="an-trivia-steps__rule" aria-hidden="true"></div>
            </div>

            <div class="an-trivia-steps__timeline">
                <div class="an-trivia-steps__track" aria-hidden="true"></div>

                <ol class="an-trivia-steps__list">
                    <li class="an-trivia-steps__step an-reveal" style="--i:0">
                        <span class="an-trivia-steps__icon">
                            <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                            <span class="an-trivia-steps__number" aria-hidden="true">01</span>
                        </span>
                        <h3 class="an-trivia-steps__title">Sign In</h3>
                        <p class="an-trivia-steps__text">Access Anime Trivia by signing in to your Anime Nigeria account.</p>
                    </li>

                    <li class="an-trivia-steps__step an-reveal" style="--i:1">
                        <span class="an-trivia-steps__icon">
                            <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                            <span class="an-trivia-steps__number" aria-hidden="true">02</span>
                        </span>
                        <h3 class="an-trivia-steps__title">Choose a Quiz</h3>
                        <p class="an-trivia-steps__text">Browse the available trivia challenges and pick one to begin.</p>
                    </li>

                    <li class="an-trivia-steps__step an-reveal" style="--i:2">
                        <span class="an-trivia-steps__icon">
                            <i class="fa-regular fa-clock" aria-hidden="true"></i>
                            <span class="an-trivia-steps__number" aria-hidden="true">03</span>
                        </span>
                        <h3 class="an-trivia-steps__title">Answer Before Time Runs Out</h3>
                        <p class="an-trivia-steps__text">Each quiz is timed. Answer every question as quickly and accurately as possible to maximise your score.</p>
                    </li>

                    <li class="an-trivia-steps__step an-reveal" style="--i:3">
                        <span class="an-trivia-steps__icon">
                            <i class="fa-solid fa-ranking-star" aria-hidden="true"></i>
                            <span class="an-trivia-steps__number" aria-hidden="true">04</span>
                        </span>
                        <h3 class="an-trivia-steps__title">Earn Points &amp; Climb the Leaderboard</h3>
                        <p class="an-trivia-steps__text">Correct answers earn points, while faster responses reward even higher scores. Complete quizzes to improve your ranking on the leaderboard.</p>
                    </li>
                </ol>
            </div>
        </div>
    </section>

    <!-- ===================================================================
     TRIVIA RULES & SCORING
     =================================================================== -->
    <section class="an-trivia-rules" aria-labelledby="an-trivia-rules-heading">
        <div class="an-trivia-rules__glow" aria-hidden="true"></div>

        <div class="an-container an-trivia-rules__inner">
            <div class="an-trivia-rules__intro">
                <span class="an-eyebrow an-trivia-rules__eyebrow">Rules &amp; Scoring</span>
                <h2 class="an-trivia-rules__heading" id="an-trivia-rules-heading">Know the Rules. Maximise Your Score.</h2>
                <p class="an-trivia-rules__subheading">
                    Anime Trivia rewards both knowledge and speed. Keep these simple rules in mind
                    before taking on your next challenge.
                </p>
                <div class="an-trivia-rules__rule" aria-hidden="true"></div>
            </div>

            <div class="an-trivia-rules__grid">
                <article class="an-trivia-rules__card an-reveal" style="--i:0">
                    <span class="an-trivia-rules__icon"><i class="fa-solid fa-stopwatch" aria-hidden="true"></i></span>
                    <h3 class="an-trivia-rules__card-title">Timed Questions</h3>
                    <p class="an-trivia-rules__card-text">Every quiz has a time limit. Answer each question before the timer expires.</p>
                </article>

                <article class="an-trivia-rules__card an-reveal" style="--i:1">
                    <span class="an-trivia-rules__icon"><i class="fa-solid fa-bullseye" aria-hidden="true"></i></span>
                    <h3 class="an-trivia-rules__card-title">One Attempt Per Quiz</h3>
                    <p class="an-trivia-rules__card-text">Each quiz can only be attempted once, so make every answer count.</p>
                </article>

                <article class="an-trivia-rules__card an-reveal" style="--i:2">
                    <span class="an-trivia-rules__icon"><i class="fa-solid fa-bolt" aria-hidden="true"></i></span>
                    <h3 class="an-trivia-rules__card-title">Speed Matters</h3>
                    <p class="an-trivia-rules__card-text">Correct answers submitted more quickly earn higher scores than slower correct answers.</p>
                </article>

                <article class="an-trivia-rules__card an-reveal" style="--i:3">
                    <span class="an-trivia-rules__icon"><i class="fa-solid fa-medal" aria-hidden="true"></i></span>
                    <h3 class="an-trivia-rules__card-title">Earn Points</h3>
                    <p class="an-trivia-rules__card-text">Every correct answer contributes to your total score and overall progress.</p>
                </article>

                <article class="an-trivia-rules__card an-reveal" style="--i:4">
                    <span class="an-trivia-rules__icon"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span>
                    <h3 class="an-trivia-rules__card-title">Climb the Leaderboard</h3>
                    <p class="an-trivia-rules__card-text">Perform well across multiple quizzes to improve your position on the community leaderboard.</p>
                </article>

                <article class="an-trivia-rules__card an-reveal" style="--i:5">
                    <span class="an-trivia-rules__icon"><i class="fa-solid fa-handshake" aria-hidden="true"></i></span>
                    <h3 class="an-trivia-rules__card-title">Play Fair</h3>
                    <p class="an-trivia-rules__card-text">Cheating, exploiting bugs or attempting to manipulate scores may result in disqualification.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- ===================================================================
        FREQUENTLY ASKED QUESTIONS
        =================================================================== -->
    <section class="an-trivia-faq an-reveal" aria-labelledby="an-trivia-faq-heading">
        <div class="an-container">
            <div class="an-section-heading">
                <span class="an-eyebrow">FAQ</span>
                <h2 id="an-trivia-faq-heading">Frequently Asked Questions</h2>
                <p>Everything you need to know before taking on your first Anime Trivia challenge.</p>
            </div>

            <div class="an-trivia-faq__list">
                <article class="an-trivia-faq__item">
                    <button class="an-trivia-faq__question" type="button" aria-expanded="false">
                        <span>Do I need an Anime Nigeria account to play?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>
                    <div class="an-trivia-faq__answer">
                        <p>Yes. Anime Trivia is available exclusively to registered Anime Nigeria members.</p>
                    </div>
                </article>

                <article class="an-trivia-faq__item">
                    <button class="an-trivia-faq__question" type="button" aria-expanded="false">
                        <span>Can I replay a quiz?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>
                    <div class="an-trivia-faq__answer">
                        <p>No. Each quiz can only be attempted once to ensure fair competition.</p>
                    </div>
                </article>

                <article class="an-trivia-faq__item">
                    <button class="an-trivia-faq__question" type="button" aria-expanded="false">
                        <span>How is my score calculated?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>
                    <div class="an-trivia-faq__answer">
                        <p>Scores are based on both the number of correct answers and how quickly you answer each question.</p>
                    </div>
                </article>

                <article class="an-trivia-faq__item">
                    <button class="an-trivia-faq__question" type="button" aria-expanded="false">
                        <span>Will new quizzes be added?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>
                    <div class="an-trivia-faq__answer">
                        <p>Yes. New Anime Trivia challenges will be added regularly to keep the experience fresh and exciting.</p>
                    </div>
                </article>

                <article class="an-trivia-faq__item">
                    <button class="an-trivia-faq__question" type="button" aria-expanded="false">
                        <span>Can I appear on the leaderboard?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>
                    <div class="an-trivia-faq__answer">
                        <p>Yes. High-scoring members can climb the leaderboard by performing well across available quizzes.</p>
                    </div>
                </article>

                <article class="an-trivia-faq__item">
                    <button class="an-trivia-faq__question" type="button" aria-expanded="false">
                        <span>What happens if my time runs out?</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>
                    <div class="an-trivia-faq__answer">
                        <p>Any unanswered questions will be marked as unanswered, so answering accurately and quickly is important.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- ===================================================================
     MEMBER ACCESS
     =================================================================== -->
    <section class="an-trivia-access" aria-labelledby="an-trivia-access-heading">
        <div class="an-trivia-access__glow" aria-hidden="true"></div>

        <div class="an-container an-trivia-access__inner">
            <div class="an-trivia-access__card an-reveal" style="--i:0">
                <span class="an-trivia-access__label">Members Only</span>

                <h2 class="an-trivia-access__heading" id="an-trivia-access-heading">Ready to Take on Your First Challenge?</h2>

                <p class="an-trivia-access__text">
                    Sign in to access Anime Trivia, test your anime knowledge, compete for higher
                    scores and climb the community leaderboard one quiz at a time.
                </p>

                <div class="an-trivia-access__actions">
                    <a href="/login" class="an-btn an-btn--primary">Start Playing</a>
                    <a href="/register" class="an-btn an-btn--ghost">Create Free Account</a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>