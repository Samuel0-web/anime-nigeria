<?php
declare(strict_types=1);
$pageTitle = "Community | Anime Nigeria";
$pageDescription = "Connect with fellow anime fans, join discussions, share your passion, and become part of Nigeria's growing otaku community.";
require_once __DIR__ . '/../../includes/header.php';
?>

<main id="main-content">
    <section class="an-community-hero" aria-labelledby="an-community-hero-heading">
        <div class="an-community-hero__glow an-community-hero__glow--one" aria-hidden="true"></div>
        <div class="an-community-hero__glow an-community-hero__glow--two" aria-hidden="true"></div>

        <div class="an-container an-community-hero__panel">
            <div class="an-community-hero__content">
                <span class="an-eyebrow an-community-hero__eyebrow">
                    <i class="fa-solid fa-people-group" aria-hidden="true"></i>
                    Community
                </span>

                <h1 class="an-community-hero__heading" id="an-community-hero-heading">Welcome to the Anime Nigeria Community</h1>

                <p class="an-community-hero__paragraph">
                    Anime Nigeria is more than a website—it's a growing community of anime fans across
                    Nigeria. Whether you're here to discuss your favourite series, compete in weekly
                    challenges, make new friends, or simply celebrate anime together, you'll always
                    have a place here.
                </p>

                <div class="an-community-hero__actions">
                    <a href="/community/discussions" class="an-btn an-btn--primary">Explore Discussions</a>
                    <a href="/community/challenges" class="an-btn an-btn--ghost">Join Anime Challenges</a>
                </div>

                <ul class="an-community-hero__trust">
                    <li class="an-community-hero__trust-item">
                        <i class="fa-solid fa-comments an-community-hero__trust-icon an-community-hero__trust-icon--sakura" aria-hidden="true"></i>
                        Active Discussions
                    </li>
                    <li class="an-community-hero__trust-item">
                        <i class="fa-solid fa-trophy an-community-hero__trust-icon an-community-hero__trust-icon--gold" aria-hidden="true"></i>
                        Weekly Challenges
                    </li>
                    <li class="an-community-hero__trust-item">
                        <i class="fa-solid fa-seedling an-community-hero__trust-icon an-community-hero__trust-icon--teal" aria-hidden="true"></i>
                        Growing Community
                    </li>
                </ul>
            </div>

            <div class="an-community-hero__visual">

                <!-- Background glow -->
                <div class="an-community-hero__glow"></div>

                <!-- Rotating Rings -->
                <div class="an-community-hero__orbit an-community-hero__orbit--1"></div>
                <div class="an-community-hero__orbit an-community-hero__orbit--2"></div>
                <div class="an-community-hero__orbit an-community-hero__orbit--3"></div>

                <!-- Connection SVG -->
                <svg class="an-community-hero__links"
                    viewBox="0 0 500 500"
                    aria-hidden="true">

                    <defs>

                        <linearGradient id="beam">

                            <stop offset="0%" stop-color="transparent"/>

                            <stop offset="50%" stop-color="#e8a3b3"/>

                            <stop offset="100%" stop-color="transparent"/>

                        </linearGradient>

                    </defs>

                    <line x1="250" y1="250" x2="250" y2="58"/>
                    <line x1="250" y1="250" x2="410" y2="145"/>
                    <line x1="250" y1="250" x2="420" y2="350"/>
                    <line x1="250" y1="250" x2="250" y2="442"/>
                    <line x1="250" y1="250" x2="82" y2="350"/>
                    <line x1="250" y1="250" x2="90" y2="145"/>

                </svg>

                <!-- Center Hub -->
                <div class="an-community-hero__hub">

                    <img
                        src="/uploads/upscalemedia-transformed (1).png"
                        alt="Anime Nigeria"
                        class="an-community-hero__logo">

                </div>

                <div class="an-community-hero__system">
                    <div class="an-community-hero__planet an-community-hero__planet--1">
                        <div class="an-community-hero__item">
                            <i class="fa-solid fa-palette"></i>
                            <span>Fan Art</span>
                        </div>
                    </div>

                    <div class="an-community-hero__planet an-community-hero__planet--2">
                        <div class="an-community-hero__item">
                            <i class="fa-solid fa-gamepad"></i>
                            <span>Gaming</span>
                        </div>
                    </div>

                    <div class="an-community-hero__planet an-community-hero__planet--3">
                        <div class="an-community-hero__item">
                            <i class="fa-solid fa-masks-theater"></i>
                            <span>Cosplay</span>
                        </div>
                    </div>

                    <div class="an-community-hero__planet an-community-hero__planet--4">
                        <div class="an-community-hero__item">
                            <i class="fa-solid fa-trophy"></i>
                            <span>Awards</span>
                        </div>
                    </div>

                    <div class="an-community-hero__planet an-community-hero__planet--5">
                        <div class="an-community-hero__item">
                            <i class="fa-solid fa-users"></i>
                            <span>Community</span>
                        </div>
                    </div>

                    <div class="an-community-hero__planet an-community-hero__planet--6">
                        <div class="an-community-hero__item">
                            <i class="fa-solid fa-tv"></i>
                            <span>Watch</span>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- ===================================================================
     OUR STORY & MISSION
     =================================================================== -->
    <section class="an-community-story" aria-labelledby="an-community-story-heading">
        <div class="an-community-story__glow an-community-story__glow--one" aria-hidden="true"></div>
        <div class="an-community-story__glow an-community-story__glow--two" aria-hidden="true"></div>

        <div class="an-container an-community-story__panel">
            <div class="an-community-story__content an-reveal" style="--i:1">
                <span class="an-eyebrow an-community-story__eyebrow">Our Story</span>

                <h2 class="an-community-story__heading" id="an-community-story-heading">Built by Anime Fans. Made for the Community.</h2>

                <p class="an-community-story__paragraph">
                    Anime Nigeria was created with one simple idea: every anime fan deserves a place
                    where they can belong.
                </p>

                <p class="an-community-story__paragraph">
                    What started as a shared passion has grown into a welcoming community where fans
                    can discover new anime, join discussions, participate in challenges, celebrate
                    achievements, attend community events, and build lasting friendships with people
                    who share the same love for anime.
                </p>

                <div class="an-community-story__cards">
                    <div class="an-community-story__card an-community-story__card--mission">
                        <span class="an-community-story__card-label">
                            <i class="fa-solid fa-compass" aria-hidden="true"></i>
                            Mission
                        </span>
                        <p class="an-community-story__card-text">
                            To build Nigeria's most welcoming and engaging anime community where every
                            fan feels at home.
                        </p>
                    </div>

                    <div class="an-community-story__card an-community-story__card--vision">
                        <span class="an-community-story__card-label">
                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                            Vision
                        </span>
                        <p class="an-community-story__card-text">
                            To unite anime fans across Nigeria through meaningful experiences,
                            creativity, friendship and a shared passion for anime.
                        </p>
                    </div>
                </div>
            </div>

            <div class="an-community-story__visual an-reveal" style="--i:2">
                <div class="an-community-story__visual-glow" aria-hidden="true"></div>

                <img src="/uploads/Icon-Anime-Nigeria-Logo-768x752.png"
                    alt="Anime fans gathering together through friendship, creativity and community."
                    class="an-community-story__image">

                <div class="an-community-story__grain" aria-hidden="true"></div>

                <span class="an-community-story__badge">
                    <i class="fa-solid fa-star"></i>
                    Since 2025
                </span>

                <span class="an-community-story__petal an-community-story__petal--1"></span>
                <span class="an-community-story__petal an-community-story__petal--2"></span>
                <span class="an-community-story__petal an-community-story__petal--3"></span>
                <span class="an-community-story__petal an-community-story__petal--4"></span>

            </div>
        </div>
    </section>

    <!-- ===================================================================
     COMMUNITY VALUES
     =================================================================== -->
    <section class="an-community-values" aria-labelledby="an-community-values-heading">
        <div class="an-community-values__glow an-community-values__glow--one" aria-hidden="true"></div>
        <div class="an-community-values__glow an-community-values__glow--two" aria-hidden="true"></div>

        <div class="an-container an-community-values__inner">
            <div class="an-community-values__intro">
                <span class="an-eyebrow an-community-values__eyebrow">Our Values</span>
                <h2 class="an-community-values__heading" id="an-community-values-heading">What Brings Our Community Together</h2>
                <p class="an-community-values__subheading">
                    Anime Nigeria is built on more than a shared love for anime. Our community is guided
                    by values that encourage friendship, creativity, respect and meaningful connections
                    between fans across the country.
                </p>
                <div class="an-community-values__rule" aria-hidden="true"></div>
            </div>

            <div class="an-community-values__grid">
                <article class="an-community-values__card an-reveal" style="--i:0">
                    <span class="an-community-values__card-glow an-community-values__card-glow--sakura" aria-hidden="true"></span>
                    <span class="an-community-values__icon an-community-values__icon--sakura"><i class="fa-solid fa-handshake" aria-hidden="true"></i></span>
                    <h3 class="an-community-values__card-title">Respect Everyone</h3>
                    <p class="an-community-values__card-text">Every anime fan deserves to feel welcome. We celebrate different opinions, tastes and experiences with kindness and mutual respect.</p>
                </article>

                <article class="an-community-values__card an-reveal" style="--i:1">
                    <span class="an-community-values__card-glow an-community-values__card-glow--violet" aria-hidden="true"></span>
                    <span class="an-community-values__icon an-community-values__icon--violet"><i class="fa-solid fa-palette" aria-hidden="true"></i></span>
                    <h3 class="an-community-values__card-title">Celebrate Creativity</h3>
                    <p class="an-community-values__card-text">From cosplay and fan art to photography and writing, we encourage members to express their creativity and share what they love.</p>
                </article>

                <article class="an-community-values__card an-reveal" style="--i:2">
                    <span class="an-community-values__card-glow an-community-values__card-glow--teal" aria-hidden="true"></span>
                    <span class="an-community-values__icon an-community-values__icon--teal"><i class="fa-solid fa-comments" aria-hidden="true"></i></span>
                    <h3 class="an-community-values__card-title">Build Friendships</h3>
                    <p class="an-community-values__card-text">Anime is better together. We create opportunities for meaningful conversations, lasting friendships and unforgettable memories.</p>
                </article>

                <article class="an-community-values__card an-reveal" style="--i:3">
                    <span class="an-community-values__card-glow an-community-values__card-glow--gold" aria-hidden="true"></span>
                    <span class="an-community-values__icon an-community-values__icon--gold"><i class="fa-solid fa-star" aria-hidden="true"></i></span>
                    <h3 class="an-community-values__card-title">Share the Passion</h3>
                    <p class="an-community-values__card-text">Whether you're new to anime or a lifelong fan, everyone has something valuable to contribute to the community.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- ===================================================================
     WAYS TO GET INVOLVED
     =================================================================== -->
    <section class="an-community-involve" aria-labelledby="an-community-involve-heading">
        <div class="an-community-involve__glow an-community-involve__glow--one" aria-hidden="true"></div>
        <div class="an-community-involve__glow an-community-involve__glow--two" aria-hidden="true"></div>

        <div class="an-container an-community-involve__inner">
            <div class="an-community-involve__intro">
                <span class="an-eyebrow an-community-involve__eyebrow">Get Involved</span>
                <h2 class="an-community-involve__heading" id="an-community-involve-heading">There's a Place for Every Anime Fan</h2>
                <p class="an-community-involve__subheading">
                    Whether you enjoy discussing your favourite anime, competing in weekly challenges,
                    testing your knowledge, or simply connecting with fellow fans, Anime Nigeria has
                    something for everyone.
                </p>
                <div class="an-community-involve__rule" aria-hidden="true"></div>
            </div>

            <div class="an-community-involve__grid">
                <a href="/community/discussions" class="an-community-involve__card an-reveal" style="--i:0" aria-label="Join Discussions — Explore Discussions">
                    <span class="an-community-involve__card-glow an-community-involve__card-glow--sakura" aria-hidden="true"></span>
                    <span class="an-community-involve__icon an-community-involve__icon--sakura"><i class="fa-solid fa-comments" aria-hidden="true"></i></span>
                    <h3 class="an-community-involve__card-title">Join Discussions</h3>
                    <p class="an-community-involve__card-text">Share opinions, ask questions, discover recommendations and connect with fellow anime fans.</p>
                    <span class="an-community-involve__cta">Explore Discussions <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                </a>

                <a href="/community/challenges" class="an-community-involve__card an-reveal" style="--i:1" aria-label="Weekly Challenges — View Challenges">
                    <span class="an-community-involve__card-glow an-community-involve__card-glow--ember" aria-hidden="true"></span>
                    <span class="an-community-involve__icon an-community-involve__icon--ember"><i class="fa-solid fa-trophy" aria-hidden="true"></i></span>
                    <h3 class="an-community-involve__card-title">Weekly Challenges</h3>
                    <p class="an-community-involve__card-text">Take part in fun anime challenges, compete with others and unlock achievements.</p>
                    <span class="an-community-involve__cta">View Challenges <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                </a>

                <a href="/trivia" class="an-community-involve__card an-reveal" style="--i:2" aria-label="Anime Trivia — Play Trivia">
                    <span class="an-community-involve__card-glow an-community-involve__card-glow--violet" aria-hidden="true"></span>
                    <span class="an-community-involve__icon an-community-involve__icon--violet"><i class="fa-solid fa-brain" aria-hidden="true"></i></span>
                    <h3 class="an-community-involve__card-title">Anime Trivia</h3>
                    <p class="an-community-involve__card-text">Test your anime knowledge and climb the leaderboard with new quizzes every week.</p>
                    <span class="an-community-involve__cta">Play Trivia <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                </a>

                <a href="/community/awards" class="an-community-involve__card an-reveal" style="--i:3" aria-label="Community Awards — Discover Awards">
                    <span class="an-community-involve__card-glow an-community-involve__card-glow--crimson" aria-hidden="true"></span>
                    <span class="an-community-involve__icon an-community-involve__icon--crimson"><i class="fa-solid fa-award" aria-hidden="true"></i></span>
                    <h3 class="an-community-involve__card-title">Community Awards</h3>
                    <p class="an-community-involve__card-text">Celebrate outstanding community members and recognise the people making Anime Nigeria special.</p>
                    <span class="an-community-involve__cta">Discover Awards <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                </a>

                <a href="/gallery" class="an-community-involve__card an-reveal" style="--i:4" aria-label="Community Gallery — Browse Gallery">
                    <span class="an-community-involve__card-glow an-community-involve__card-glow--gold" aria-hidden="true"></span>
                    <span class="an-community-involve__icon an-community-involve__icon--gold"><i class="fa-solid fa-images" aria-hidden="true"></i></span>
                    <h3 class="an-community-involve__card-title">Community Gallery</h3>
                    <p class="an-community-involve__card-text">Explore memorable moments from meetups, cosplay events, watch parties and community activities.</p>
                    <span class="an-community-involve__cta">Browse Gallery <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                </a>

                <a
                    href="https://chat.whatsapp.com/ExampleInviteCode"
                    class="an-community-involve__card an-reveal"
                    style="--i:5"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Join the Conversation — Join WhatsApp (opens in a new tab)"
                >
                    <span class="an-community-involve__card-glow an-community-involve__card-glow--teal" aria-hidden="true"></span>
                    <span class="an-community-involve__icon an-community-involve__icon--teal"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span>
                    <h3 class="an-community-involve__card-title">Join the Conversation</h3>
                    <p class="an-community-involve__card-text">Connect instantly with fellow anime fans through our official WhatsApp community.</p>
                    <span class="an-community-involve__cta">Join WhatsApp <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                </a>
            </div>
        </div>
    </section>

    <!-- ===================================================================
     STUDENT COUNCIL
     =================================================================== -->
    <section class="an-community-council" aria-labelledby="an-community-council-heading">
        <div class="an-community-council__glow an-community-council__glow--one" aria-hidden="true"></div>
        <div class="an-community-council__glow an-community-council__glow--two" aria-hidden="true"></div>

        <div class="an-container an-community-council__inner">
            <div class="an-community-council__intro">
                <span class="an-eyebrow an-community-council__eyebrow">Leadership</span>
                <h2 class="an-community-council__heading" id="an-community-council-heading">Meet the Student Council</h2>
                <div class="an-community-council__rule" aria-hidden="true"></div>
            </div>

            <p class="an-community-council__note an-reveal" style="--i:0">
                <i class="fa-solid fa-sparkles" aria-hidden="true"></i>
                Interested in helping the community? As Anime Nigeria grows, dedicated members may have
                opportunities to join the Student Council.
            </p>

            <div class="an-community-council__grid">
                <article class="an-community-council__card an-reveal" style="--i:1">
                    <span class="an-community-council__avatar an-community-council__avatar--gold">
                        <img src="https://i.pravatar.cc/300?u=an-council-mosh-kim" alt="" class="an-community-council__photo">
                        <span class="an-community-council__crown" aria-hidden="true"><i class="fa-solid fa-crown"></i></span>
                    </span>
                    <span class="an-community-council__role">Student Council President</span>
                    <span class="an-community-council__name">Mosh Kim</span>
                </article>

                <article class="an-community-council__card an-reveal" style="--i:2">
                    <span class="an-community-council__avatar an-community-council__avatar--sakura">
                        <img src="https://i.pravatar.cc/300?u=an-council-charles-odigbo" alt="" class="an-community-council__photo">
                    </span>
                    <span class="an-community-council__role">Student Council Member</span>
                    <span class="an-community-council__name">Charles Odigbo</span>
                </article>

                <article class="an-community-council__card an-reveal" style="--i:3">
                    <span class="an-community-council__avatar an-community-council__avatar--ember">
                        <img src="https://i.pravatar.cc/300?u=an-council-jumoke" alt="" class="an-community-council__photo">
                    </span>
                    <span class="an-community-council__role">Student Council Member</span>
                    <span class="an-community-council__name">Jumoke</span>
                </article>

                <article class="an-community-council__card an-reveal" style="--i:4">
                    <span class="an-community-council__avatar an-community-council__avatar--violet">
                        <img src="https://i.pravatar.cc/300?u=an-council-bella" alt="" class="an-community-council__photo">
                    </span>
                    <span class="an-community-council__role">Student Council Member</span>
                    <span class="an-community-council__name">Bella</span>
                </article>

                <article class="an-community-council__card an-reveal" style="--i:5">
                    <span class="an-community-council__avatar an-community-council__avatar--teal">
                        <img src="https://i.pravatar.cc/300?u=an-council-kami" alt="" class="an-community-council__photo">
                    </span>
                    <span class="an-community-council__role">Student Council Member</span>
                    <span class="an-community-council__name">Kami</span>
                </article>
            </div>
        </div>
    </section>

    <!-- ===================================================================
     COMMUNITY GALLERY
     =================================================================== -->
    <section class="an-community-gallery" aria-labelledby="an-community-gallery-heading">
        <div class="an-community-gallery__glow an-community-gallery__glow--one" aria-hidden="true"></div>
        <div class="an-community-gallery__glow an-community-gallery__glow--two" aria-hidden="true"></div>

        <div class="an-container an-community-gallery__inner">
            <div class="an-community-gallery__intro">
                <span class="an-eyebrow an-community-gallery__eyebrow">Community Gallery</span>
                <h2 class="an-community-gallery__heading" id="an-community-gallery-heading">Memories We've Made Together</h2>
                <p class="an-community-gallery__subheading">
                    Every meetup, cosplay event, watch party and community gathering tells part of the
                    Anime Nigeria story. Here are some of the moments we've shared together.
                </p>
                <div class="an-community-gallery__rule" aria-hidden="true"></div>
            </div>

            <div class="an-community-gallery__masonry">
                <a href="/gallery/lagos-anime-meetup" class="an-community-gallery__item an-community-gallery__item--portrait an-reveal" style="--i:0">
                    <img src="https://picsum.photos/seed/an2-lagos-meetup/500/650" alt="Lagos Anime Meetup" class="an-community-gallery__image" loading="lazy">
                    <span class="an-community-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-community-gallery__caption">
                        <span class="an-community-gallery__title">Lagos Anime Meetup</span>
                        <span class="an-community-gallery__date">July 2026</span>
                    </span>
                </a>

                <a href="/gallery/cosplay-competition" class="an-community-gallery__item an-community-gallery__item--square an-reveal" style="--i:1">
                    <img src="https://picsum.photos/seed/an2-cosplay-comp/600/600" alt="Cosplay Competition" class="an-community-gallery__image" loading="lazy">
                    <span class="an-community-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-community-gallery__caption">
                        <span class="an-community-gallery__title">Cosplay Competition</span>
                        <span class="an-community-gallery__date">Anime Expo 2026</span>
                    </span>
                </a>

                <a href="/gallery/one-piece-watch-party" class="an-community-gallery__item an-community-gallery__item--landscape an-reveal" style="--i:2">
                    <img src="https://picsum.photos/seed/an2-op-watch-party/700/525" alt="One Piece Watch Party" class="an-community-gallery__image" loading="lazy">
                    <span class="an-community-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-community-gallery__caption">
                        <span class="an-community-gallery__title">One Piece Watch Party</span>
                        <span class="an-community-gallery__date">March 2026</span>
                    </span>
                </a>

                <a href="/gallery/anime-awards-night" class="an-community-gallery__item an-community-gallery__item--tall an-reveal" style="--i:3">
                    <img src="https://picsum.photos/seed/an2-awards-night/500/750" alt="Anime Awards Night" class="an-community-gallery__image" loading="lazy">
                    <span class="an-community-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-community-gallery__caption">
                        <span class="an-community-gallery__title">Anime Awards Night</span>
                        <span class="an-community-gallery__date">September 2026</span>
                    </span>
                </a>

                <a href="/gallery/community-game-night" class="an-community-gallery__item an-community-gallery__item--square an-reveal" style="--i:4">
                    <img src="https://picsum.photos/seed/an2-game-night/600/600" alt="Community Game Night" class="an-community-gallery__image" loading="lazy">
                    <span class="an-community-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-community-gallery__caption">
                        <span class="an-community-gallery__title">Community Game Night</span>
                        <span class="an-community-gallery__date">August 2026</span>
                    </span>
                </a>

                <a href="/gallery/abuja-fan-gathering" class="an-community-gallery__item an-community-gallery__item--landscape an-reveal" style="--i:5">
                    <img src="https://picsum.photos/seed/an2-abuja-gathering/700/525" alt="Abuja Fan Gathering" class="an-community-gallery__image" loading="lazy">
                    <span class="an-community-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-community-gallery__caption">
                        <span class="an-community-gallery__title">Abuja Fan Gathering</span>
                        <span class="an-community-gallery__date">May 2026</span>
                    </span>
                </a>

                <a href="/gallery/convention-highlights" class="an-community-gallery__item an-community-gallery__item--portrait an-reveal" style="--i:6">
                    <img src="https://picsum.photos/seed/an2-convention/500/650" alt="Convention Highlights" class="an-community-gallery__image" loading="lazy">
                    <span class="an-community-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-community-gallery__caption">
                        <span class="an-community-gallery__title">Convention Highlights</span>
                        <span class="an-community-gallery__date">October 2026</span>
                    </span>
                </a>

                <a href="/gallery/otaku-hangout" class="an-community-gallery__item an-community-gallery__item--square an-reveal" style="--i:7">
                    <img src="https://picsum.photos/seed/an2-otaku-hangout/600/600" alt="Otaku Hangout" class="an-community-gallery__image" loading="lazy">
                    <span class="an-community-gallery__overlay" aria-hidden="true"></span>
                    <span class="an-community-gallery__caption">
                        <span class="an-community-gallery__title">Otaku Hangout</span>
                        <span class="an-community-gallery__date">November 2026</span>
                    </span>
                </a>
            </div>

            <div class="an-community-gallery__footer">
                <a href="/gallery" class="an-btn an-btn--ghost">View Full Gallery</a>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>