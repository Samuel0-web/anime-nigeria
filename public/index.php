<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/header.php';
?>

<main id="main-content">
    <section class="an-hero" aria-labelledby="an-hero-heading">
        <div class="an-hero__media" aria-hidden="true">
            <img src="uploads/upscalemedia-transformed.png" alt="" class="an-hero__image">
            <div class="an-hero__vignette"></div>
            <div class="an-hero__scrim"></div>
        </div>

        <div class="an-hero__content">
            <span class="an-eyebrow an-hero__badge">🇳🇬 Nigeria's Anime Community</span>

            <h1 class="an-hero__heading" id="an-hero-heading">Welcome Home, Otaku.</h1>

            <p class="an-hero__paragraph">
                Anime Nigeria is where anime and Japanese culture meet community — a place to debate
                theories after the newest episode, take on anime challenges, test your knowledge at
                trivia night, celebrate the best of the year at our community awards, and read stories
                from fans just like you. However you found anime, you'll find your people here.
            </p>

            <div class="an-hero__actions">
                <a href="/join" class="an-btn an-btn--primary">Join Community</a>
                <a href="/about" class="an-btn an-btn--ghost">Explore Anime Nigeria</a>
            </div>
        </div>

        <div class="an-hero__fade" aria-hidden="true"></div>
    </section>

    <!-- ===================================================================
         COMMUNITY FEATURES
         =================================================================== -->
    <section class="an-features" aria-labelledby="an-features-heading">
        <div class="an-features__glow an-features__glow--one" aria-hidden="true"></div>
        <div class="an-features__glow an-features__glow--two" aria-hidden="true"></div>

        <div class="an-container an-features__inner">
            <div class="an-features__intro">
                <h2 class="an-features__heading" id="an-features-heading">Everything an Otaku Needs, All in One Place</h2>
                <p class="an-features__subheading">
                    Anime Nigeria is more than a discussion forum — it's a home for anime fans to connect,
                    compete, learn, and celebrate together.
                </p>
                <div class="an-features__rule" aria-hidden="true"></div>
            </div>

            <div class="an-features__grid">
                <article class="an-features__card an-reveal" style="--i:0">
                    <div class="an-features__card-glow an-features__card-glow--sakura" aria-hidden="true"></div>
                    <div class="an-features__card-head">
                        <span class="an-features__icon an-features__icon--sakura"><i class="fa-solid fa-comments" aria-hidden="true"></i></span>
                        <h3 class="an-features__card-title">Community Discussions</h3>
                    </div>
                    <p class="an-features__card-text">Join conversations about the latest episodes, manga chapters, and anime news.</p>
                </article>

                <article class="an-features__card an-reveal" style="--i:1">
                    <div class="an-features__card-glow an-features__card-glow--ember" aria-hidden="true"></div>
                    <div class="an-features__card-head">
                        <span class="an-features__icon an-features__icon--ember"><i class="fa-solid fa-bolt" aria-hidden="true"></i></span>
                        <h3 class="an-features__card-title">Anime Challenges</h3>
                    </div>
                    <p class="an-features__card-text">Complete weekly challenges and earn recognition within the community.</p>
                </article>

                <article class="an-features__card an-reveal" style="--i:2">
                    <div class="an-features__card-glow an-features__card-glow--violet" aria-hidden="true"></div>
                    <div class="an-features__card-head">
                        <span class="an-features__icon an-features__icon--violet"><i class="fa-solid fa-brain" aria-hidden="true"></i></span>
                        <h3 class="an-features__card-title">Trivia Nights</h3>
                    </div>
                    <p class="an-features__card-text">Test your anime knowledge against other fans in live events.</p>
                </article>

                <article class="an-features__card an-reveal" style="--i:3">
                    <div class="an-features__card-glow an-features__card-glow--gold" aria-hidden="true"></div>
                    <div class="an-features__card-head">
                        <span class="an-features__icon an-features__icon--gold"><i class="fa-solid fa-pen-nib" aria-hidden="true"></i></span>
                        <h3 class="an-features__card-title">Blogs &amp; Reviews</h3>
                    </div>
                    <p class="an-features__card-text">Discover articles, reviews, and editorials written by passionate community members.</p>
                </article>

                <article class="an-features__card an-reveal" style="--i:4">
                    <div class="an-features__card-glow an-features__card-glow--teal" aria-hidden="true"></div>
                    <div class="an-features__card-head">
                        <span class="an-features__icon an-features__icon--teal"><i class="fa-solid fa-calendar-days" aria-hidden="true"></i></span>
                        <h3 class="an-features__card-title">Events &amp; Meetups</h3>
                    </div>
                    <p class="an-features__card-text">Stay updated with online gatherings and physical anime events across Nigeria.</p>
                </article>

                <article class="an-features__card an-reveal" style="--i:5">
                    <div class="an-features__card-glow an-features__card-glow--crimson" aria-hidden="true"></div>
                    <div class="an-features__card-head">
                        <span class="an-features__icon an-features__icon--crimson"><i class="fa-solid fa-trophy" aria-hidden="true"></i></span>
                        <h3 class="an-features__card-title">Community Awards</h3>
                    </div>
                    <p class="an-features__card-text">Vote for your favorite anime, characters, openings, and creators during annual community awards.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- ===================================================================
         BLOG
         =================================================================== -->
    <section class="an-blog" aria-labelledby="an-blog-heading">
        <div class="an-container an-blog__inner">
            <div class="an-blog__intro">
                <h2 class="an-blog__heading" id="an-blog-heading">Latest from the Blog</h2>
                <p class="an-blog__subheading">
                    Discover anime news, reviews, editorials, recommendations, community stories, and
                    Japanese pop culture updates curated for Nigerian anime fans.
                </p>
                <div class="an-blog__rule" aria-hidden="true"></div>
            </div>

            <div class="an-blog__grid">
                <article class="an-blog__featured-post an-reveal" style="--i:0">
                    <div class="an-blog__featured-media" aria-hidden="true">
                        <!-- Swap for a real <img class="an-blog__featured-image" src="..." alt="..."> once article images exist -->
                        <i class="fa-solid fa-clapperboard an-blog__media-icon" aria-hidden="true"></i>
                    </div>
                    <div class="an-blog__featured-body">
                        <span class="an-blog__category an-blog__category--news">News</span>
                        <h3 class="an-blog__featured-title">
                            <a href="/blog/best-anime-of-2026-so-far" class="an-blog__featured-title-link">The Best Anime of 2026 So Far</a>
                        </h3>
                        <p class="an-blog__featured-excerpt">
                            From breakout newcomers to long-awaited sequels, we round up the standout series
                            defining the year — and the ones quietly building a following worth watching.
                        </p>
                        <div class="an-blog__meta">
                            <span class="an-blog__meta-author">By Ada N.</span>
                            <span class="an-blog__meta-sep" aria-hidden="true">&middot;</span>
                            <time class="an-blog__meta-date" datetime="2026-07-02">Jul 2, 2026</time>
                            <span class="an-blog__meta-sep" aria-hidden="true">&middot;</span>
                            <span class="an-blog__meta-read">7 min read</span>
                        </div>
                        <span class="an-blog__cta" aria-hidden="true">Read Article <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                    </div>
                </article>

                <div class="an-blog__secondary">
                    <article class="an-blog__post an-reveal" style="--i:1">
                        <div class="an-blog__post-media" aria-hidden="true">
                            <i class="fa-solid fa-fire an-blog__media-icon" aria-hidden="true"></i>
                        </div>
                        <div class="an-blog__post-body">
                            <span class="an-blog__category an-blog__category--opinion">Opinion</span>
                            <h3 class="an-blog__post-title">
                                <a href="/blog/why-kagurabachi-became-a-global-hit" class="an-blog__post-title-link">Why Kagurabachi Became a Global Hit</a>
                            </h3>
                            <p class="an-blog__post-excerpt">What separates a breakout series from the rest of the season — and why this one stuck.</p>
                            <div class="an-blog__meta">
                                <time class="an-blog__meta-date" datetime="2026-06-27">Jun 27, 2026</time>
                                <span class="an-blog__meta-sep" aria-hidden="true">&middot;</span>
                                <span class="an-blog__meta-read">5 min read</span>
                            </div>
                        </div>
                    </article>

                    <article class="an-blog__post an-reveal" style="--i:2">
                        <div class="an-blog__post-media" aria-hidden="true">
                            <i class="fa-solid fa-gem an-blog__media-icon" aria-hidden="true"></i>
                        </div>
                        <div class="an-blog__post-body">
                            <span class="an-blog__category an-blog__category--guide">Guide</span>
                            <h3 class="an-blog__post-title">
                                <a href="/blog/10-hidden-anime-gems-every-fan-should-watch" class="an-blog__post-title-link">10 Hidden Anime Gems Every Fan Should Watch</a>
                            </h3>
                            <p class="an-blog__post-excerpt">Underrated series worth your watchlist, picked by fans who dig past the algorithm.</p>
                            <div class="an-blog__meta">
                                <time class="an-blog__meta-date" datetime="2026-06-21">Jun 21, 2026</time>
                                <span class="an-blog__meta-sep" aria-hidden="true">&middot;</span>
                                <span class="an-blog__meta-read">6 min read</span>
                            </div>
                        </div>
                    </article>

                    <article class="an-blog__post an-reveal" style="--i:3">
                        <div class="an-blog__post-media" aria-hidden="true">
                            <i class="fa-solid fa-users an-blog__media-icon" aria-hidden="true"></i>
                        </div>
                        <div class="an-blog__post-body">
                            <span class="an-blog__category an-blog__category--community">Community</span>
                            <h3 class="an-blog__post-title">
                                <a href="/blog/community-spotlight-nigerias-biggest-anime-fan" class="an-blog__post-title-link">Community Spotlight: Meet One of Nigeria's Biggest Anime Fans</a>
                            </h3>
                            <p class="an-blog__post-excerpt">A conversation about cosplay, collecting, and building a fandom from scratch in Lagos.</p>
                            <div class="an-blog__meta">
                                <time class="an-blog__meta-date" datetime="2026-06-14">Jun 14, 2026</time>
                                <span class="an-blog__meta-sep" aria-hidden="true">&middot;</span>
                                <span class="an-blog__meta-read">4 min read</span>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <div class="an-blog__footer">
                <a href="/blog" class="an-btn an-btn--ghost">View All Articles</a>
            </div>
        </div>
    </section>

    <section class="an-challenge" aria-labelledby="an-challenge-heading">
        <div class="an-challenge__glow an-challenge__glow--one" aria-hidden="true"></div>
        <div class="an-challenge__glow an-challenge__glow--two" aria-hidden="true"></div>

        <div class="an-container an-challenge__inner">
            <div class="an-challenge__intro">
                <span class="an-eyebrow an-challenge__eyebrow"><i class="fa-solid fa-trophy" aria-hidden="true"></i> Featured Challenge</span>
                <h2 class="an-challenge__heading" id="an-challenge-heading">Think You're a True Otaku?</h2>
                <p class="an-challenge__subheading">
                    Every week the Anime Nigeria community takes on a new challenge. Test your anime
                    knowledge, climb the leaderboard, earn badges, and prove you're among Nigeria's
                    finest anime fans.
                </p>
                <div class="an-challenge__rule" aria-hidden="true"></div>
            </div>

            <div class="an-challenge__panel">
                <div class="an-challenge__info an-reveal" style="--i:0">
                    <span class="an-challenge__status">
                        <span class="an-challenge__status-dot" aria-hidden="true"></span>
                        LIVE
                    </span>

                    <h3 class="an-challenge__title">Weekly Anime Gauntlet</h3>

                    <p class="an-challenge__description">
                        A new test of anime knowledge every week — trivia, quotes, visuals, and more.
                        Score high enough and you'll earn a spot on the leaderboard.
                    </p>

                    <div class="an-challenge__meta">
                        <div class="an-challenge__meta-item an-challenge__difficulty">
                            <span class="an-challenge__meta-label">Difficulty</span>
                            <span class="an-challenge__difficulty-dots" role="img" aria-label="Difficulty: Medium">
                                <span class="an-challenge__difficulty-dot an-challenge__difficulty-dot--filled"></span>
                                <span class="an-challenge__difficulty-dot an-challenge__difficulty-dot--filled"></span>
                                <span class="an-challenge__difficulty-dot"></span>
                            </span>
                            <span class="an-challenge__meta-value">Medium</span>
                        </div>

                        <div class="an-challenge__meta-item">
                            <i class="fa-regular fa-clock an-challenge__meta-icon" aria-hidden="true"></i>
                            <span class="an-challenge__meta-label">Time Remaining</span>
                            <span class="an-challenge__meta-value">4 Days Left</span>
                        </div>

                        <div class="an-challenge__meta-item">
                            <i class="fa-solid fa-user-group an-challenge__meta-icon" aria-hidden="true"></i>
                            <span class="an-challenge__meta-label">Participants</span>
                            <span class="an-challenge__meta-value">127 joined</span>
                        </div>
                    </div>

                    <div class="an-challenge__reward">
                        <span class="an-challenge__reward-icon" aria-hidden="true"><i class="fa-solid fa-medal"></i></span>
                        <span class="an-challenge__reward-text">
                            <span class="an-challenge__reward-label">Reward</span>
                            <span class="an-challenge__reward-value">"Gauntlet Champion" Badge</span>
                        </span>
                    </div>

                    <div class="an-challenge__actions">
                        <a href="/challenges/weekly-anime-gauntlet" class="an-btn an-btn--primary">Take Challenge</a>
                    </div>
                </div>

                <div class="an-challenge__visual an-reveal" style="--i:1">
                    <div class="an-challenge__preview">
                        <div class="an-challenge__preview-glow" aria-hidden="true"></div>
                        <div class="an-challenge__preview-shape an-challenge__preview-shape--diamond" aria-hidden="true"></div>
                        <div class="an-challenge__preview-shape an-challenge__preview-shape--ring" aria-hidden="true"></div>
                        <span class="an-challenge__preview-particle an-challenge__preview-particle--one" aria-hidden="true"></span>
                        <span class="an-challenge__preview-particle an-challenge__preview-particle--two" aria-hidden="true"></span>
                        <span class="an-challenge__preview-particle an-challenge__preview-particle--three" aria-hidden="true"></span>

                        <div class="an-challenge__preview-card" role="group" aria-label="Preview of this week's challenge">
                            <div class="an-challenge__preview-header">
                                <span class="an-challenge__status">
                                    <span class="an-challenge__status-dot" aria-hidden="true"></span>
                                    LIVE
                                </span>
                                <span class="an-challenge__preview-round">Round 1 of 10</span>
                            </div>

                            <blockquote class="an-challenge__preview-quote">
                                <p>“People's dreams… never end.”</p>
                            </blockquote>

                            <p class="an-challenge__preview-question">Who said this?</p>

                            <ul class="an-challenge__preview-choices">
                                <li class="an-challenge__preview-choice">
                                    <span class="an-challenge__preview-choice-letter">A</span>
                                    Luffy
                                </li>
                                <li class="an-challenge__preview-choice">
                                    <span class="an-challenge__preview-choice-letter">B</span>
                                    Whitebeard
                                </li>
                                <li class="an-challenge__preview-choice">
                                    <span class="an-challenge__preview-choice-letter">C</span>
                                    Blackbeard
                                </li>
                                <li class="an-challenge__preview-choice">
                                    <span class="an-challenge__preview-choice-letter">D</span>
                                    Gol D. Roger
                                </li>
                            </ul>

                            <div class="an-challenge__preview-footer">
                                <span class="an-challenge__preview-footer-item">
                                    <span class="an-challenge__difficulty-dots" role="img" aria-label="Difficulty: Medium">
                                        <span class="an-challenge__difficulty-dot an-challenge__difficulty-dot--filled"></span>
                                        <span class="an-challenge__difficulty-dot an-challenge__difficulty-dot--filled"></span>
                                        <span class="an-challenge__difficulty-dot"></span>
                                    </span>
                                </span>
                                <span class="an-challenge__preview-footer-item">
                                    <i class="fa-regular fa-clock an-challenge__meta-icon" aria-hidden="true"></i>
                                    4 Days Left
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="an-spotlight" aria-labelledby="an-spotlight-heading">
        <div class="an-spotlight__glow an-spotlight__glow--one" aria-hidden="true"></div>
        <div class="an-spotlight__glow an-spotlight__glow--two" aria-hidden="true"></div>

        <div class="an-container an-spotlight__inner">
            <div class="an-spotlight__panel">
                <div class="an-spotlight__content an-reveal" style="--i:0">
                    <span class="an-eyebrow an-spotlight__eyebrow"><i class="fa-solid fa-bullseye" aria-hidden="true"></i> This Week's Spotlight</span>
                    <span class="an-spotlight__category">Anime Recommendation</span>

                    <h2 class="an-spotlight__heading" id="an-spotlight-heading">Frieren: Beyond Journey's End</h2>

                    <p class="an-spotlight__description">
                        If you haven't watched Frieren yet, this is your sign. Beautiful storytelling,
                        unforgettable characters, and one of the highest-rated fantasy anime in recent years.
                    </p>

                    <div class="an-spotlight__actions">
                        <a href="/spotlight/frieren-beyond-journeys-end" class="an-btn an-btn--primary">Watch Now</a>
                    </div>
                </div>

                <div class="an-spotlight__showcase an-reveal" style="--i:1">
                    <div class="an-spotlight__image-frame">
                        <img class="an-spotlight__image" src="/uploads/frieren-poster.webp" alt="Frieren: Beyond Journey's End Poster" loading="lazy">
                        <div class="an-spotlight__image-overlay" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="an-gallery" aria-labelledby="an-gallery-heading">
        <div class="an-gallery__glow an-gallery__glow--one" aria-hidden="true"></div>
        <div class="an-gallery__glow an-gallery__glow--two" aria-hidden="true"></div>

        <div class="an-container an-gallery__inner">
            <div class="an-gallery__intro">
                <span class="an-eyebrow an-gallery__eyebrow">Community Gallery</span>
                <h2 class="an-gallery__heading" id="an-gallery-heading">Moments That Bring Our Community Together</h2>
                <p class="an-gallery__description">
                    From meetups to watch parties, every gathering tells a story — step into the moments
                    Anime Nigeria members have shared together.
                </p>
                <div class="an-gallery__actions">
                    <a href="/gallery" class="an-btn an-btn--primary">View Full Gallery</a>
                </div>
            </div>

            <div class="an-gallery__grid">
                <a href="/gallery/lagos-anime-meetup" class="an-gallery__item an-gallery__item--tall an-reveal" style="--i:0">
                    <img src="https://picsum.photos/seed/an-lagos-meetup/600/900" alt="" class="an-gallery__image" loading="lazy">
                    <span class="an-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-gallery__arrow" aria-hidden="true"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                    <span class="an-gallery__caption">
                        <span class="an-gallery__title">Lagos Anime Meetup</span>
                        <span class="an-gallery__date">July 2026</span>
                    </span>
                </a>

                <a href="/gallery/otaku-hangout" class="an-gallery__item an-gallery__item--square an-reveal" style="--i:2">
                    <img src="https://picsum.photos/seed/an-otaku-hangout/600/900" alt="" class="an-gallery__image" loading="lazy">
                    <span class="an-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-gallery__arrow" aria-hidden="true"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                    <span class="an-gallery__caption">
                        <span class="an-gallery__title">Otaku Hangout</span>
                        <span class="an-gallery__date">October 2026</span>
                    </span>
                </a>

                <a href="/gallery/cosplay-competition" class="an-gallery__item an-gallery__item--wide an-reveal" style="--i:1">
                    <img src="https://picsum.photos/seed/an-cosplay-comp/900/500" alt="" class="an-gallery__image" loading="lazy">
                    <span class="an-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-gallery__arrow" aria-hidden="true"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                    <span class="an-gallery__caption">
                        <span class="an-gallery__title">Cosplay Competition</span>
                        <span class="an-gallery__date">Anime Expo 2026</span>
                    </span>
                </a>

                <a href="/gallery/manga-reading-circle" class="an-gallery__item an-gallery__item--portrait an-reveal" style="--i:2">
                    <img src="https://picsum.photos/seed/an-manga-reading/700/700" alt="" class="an-gallery__image" loading="lazy">
                    <span class="an-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-gallery__arrow" aria-hidden="true"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                    <span class="an-gallery__caption">
                        <span class="an-gallery__title">Manga Reading Circle</span>
                        <span class="an-gallery__date">April 2026</span>
                    </span>
                </a>

                <a href="/gallery/one-piece-watch-party" class="an-gallery__item an-gallery__item--square an-reveal" style="--i:2">
                    <img src="https://picsum.photos/seed/an-op-watch-party/700/700" alt="" class="an-gallery__image" loading="lazy">
                    <span class="an-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-gallery__arrow" aria-hidden="true"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                    <span class="an-gallery__caption">
                        <span class="an-gallery__title">One Piece Watch Party</span>
                        <span class="an-gallery__date">March 2026</span>
                    </span>
                </a>

                <a href="/gallery/community-game-night" class="an-gallery__item an-gallery__item--tall an-reveal" style="--i:3">
                    <img src="https://picsum.photos/seed/an-game-night/600/900" alt="" class="an-gallery__image" loading="lazy">
                    <span class="an-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-gallery__arrow" aria-hidden="true"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                    <span class="an-gallery__caption">
                        <span class="an-gallery__title">Community Game Night</span>
                        <span class="an-gallery__date">August 2026</span>
                    </span>
                </a>

                <a href="/gallery/anime-awards-volunteers" class="an-gallery__item an-gallery__item--landscape an-reveal" style="--i:4">
                    <img src="https://picsum.photos/seed/an-awards-volunteers/1200/560" alt="" class="an-gallery__image" loading="lazy">
                    <span class="an-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-gallery__arrow" aria-hidden="true"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                    <span class="an-gallery__caption">
                        <span class="an-gallery__title">Anime Awards Volunteers</span>
                        <span class="an-gallery__date">September 2026</span>
                    </span>
                </a>

                <a href="/gallery/artist-alley-showcase" class="an-gallery__item an-gallery__item--portrait an-reveal" style="--i:5">
                    <img src="https://picsum.photos/seed/an-artist-alley/600/900" alt="" class="an-gallery__image" loading="lazy">
                    <span class="an-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-gallery__arrow" aria-hidden="true"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                    <span class="an-gallery__caption">
                        <span class="an-gallery__title">Artist Alley Showcase</span>
                        <span class="an-gallery__date">November 2026</span>
                    </span>
                </a>
            </div>
        </div>
    </section>
    
    <section class="an-cta" aria-labelledby="an-cta-heading">
        <div class="an-cta__glow an-cta__glow--one" aria-hidden="true"></div>
        <div class="an-cta__glow an-cta__glow--two" aria-hidden="true"></div>

        <div class="an-container an-cta__inner">
            <span class="an-eyebrow an-cta__eyebrow">Join Anime Nigeria</span>

            <h2 class="an-cta__heading" id="an-cta-heading">Ready to Become Part of Nigeria's Anime Community?</h2>

            <p class="an-cta__description">
                Create your free Anime Nigeria account to join discussions, complete weekly challenges,
                earn exclusive badges, discover amazing anime content, and become part of a growing
                community of anime fans across Nigeria.
            </p>

            <div class="an-cta__primary-actions">
                <a href="/register" class="an-btn an-btn--primary">Create Free Account</a>
            </div>

            <div class="an-cta__divider" aria-hidden="true"></div>

            <div class="an-cta__secondary">
                <p class="an-cta__secondary-text">Prefer jumping straight into the conversation?</p>
                <a
                    href="https://chat.whatsapp.com/ExampleInviteCode"
                    class="an-btn an-btn--whatsapp"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                    Join WhatsApp Community
                </a>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>