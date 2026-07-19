<?php
declare(strict_types=1);
$pageTitle = "Award Categories | Anime Nigeria";
$pageDescription = "Explore every Anime Nigeria Awards category and discover what each award recognizes and celebrates.";
require_once __DIR__ . '/../../includes/header.php';
?>

<main id="main-content">
    <!-- =============================================================================
    GALLERY HERO
    ============================================================================= -->
    <section class="an-gallery-hero">
        <div class="an-gallery-hero__glow an-gallery-hero__glow--one" aria-hidden="true"></div>
        <div class="an-gallery-hero__glow an-gallery-hero__glow--two" aria-hidden="true"></div>

        <!-- Decorative collage -->
        <div class="an-gallery-hero__collage" aria-hidden="true">
            <div class="an-gallery-thumb">
                <img src="/uploads/1783455914832.png" alt="">
            </div>

            <div class="an-gallery-thumb">
                <img src="/uploads/frieren-poster.webp" alt="">
            </div>

            <div class="an-gallery-thumb">
                <img src="/uploads/Icon-Anime-Nigeria-Logo-768x752.png" alt="">
            </div>

            <div class="an-gallery-thumb">
                <img src="/uploads/Landscape-Anime-Nigeria-Logo.png" alt="">
            </div>

            <div class="an-gallery-thumb">
                <img src="/uploads/Landscape-Anime-Nigeria-Logo.png" alt="">
            </div>

            <div class="an-gallery-thumb">
                <img src="/uploads/upscalemedia-transformed (1).png" alt="">
            </div>
        </div>

        <div class="an-container">
            <div class="an-gallery-hero__inner an-reveal">
                <span class="an-eyebrow">
                    <i class="fa-solid fa-images"></i>
                    Community Gallery
                </span>

                <span class="an-gallery-hero__badge">
                    Showcasing Nigeria's Anime Creativity
                </span>

                <h1 class="an-gallery-hero__heading">
                    Every Creation Has a Story Worth Sharing.
                </h1>

                <p class="an-gallery-hero__description">
                    Discover fan art, cosplay, photography, challenge highlights and
                    unforgettable community moments. The Anime Nigeria Gallery is a
                    living showcase of the creativity, passion and talent within our
                    community.
                </p>

                <div class="an-gallery-hero__actions">
                    <a href="#collections" class="an-btn an-btn--primary">
                        Explore Gallery
                    </a>

                    <a href="/community/challenges.php" class="an-btn an-btn--secondary">
                        View Challenges
                    </a>
                </div>

            </div>
        </div>

    </section>

    <!-- =============================================================================
    GALLERY INTRO
    ============================================================================= -->
    <section class="an-gallery-intro">
        <div class="an-container">
            <div class="an-gallery-intro__grid">

                <!-- Content -->
                <div class="an-gallery-intro__content an-reveal">
                    <span class="an-eyebrow">Inside the Gallery</span>

                    <h2 class="an-gallery-intro__heading">
                        Celebrating Every Form of Anime Creativity.
                    </h2>

                    <p>
                        The Anime Nigeria Gallery is a showcase of the passion,
                        imagination and talent found throughout our community.
                        Every featured piece represents the creativity of fans who
                        continue to inspire others through their work.
                    </p>

                    <p>
                        From stunning illustrations and detailed cosplay to memorable
                        event photography and challenge highlights, each collection
                        captures a different side of what it means to be part of
                        Anime Nigeria.
                    </p>
                </div>

                <!-- Stats -->
                <div class="an-gallery-intro__stats">
                    <article class="an-gallery-stat an-reveal">
                        <h3>5</h3>
                        <p>Gallery Collections</p>
                    </article>

                    <article class="an-gallery-stat an-reveal">
                        <h3>Community</h3>
                        <p>Driven Showcase</p>
                    </article>

                    <article class="an-gallery-stat an-reveal">
                        <h3>100%</h3>
                        <p>Curated Quality</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <!-- =============================================================================
    GALLERY BROWSER
    ============================================================================= -->
    <section class="an-gallery-browser" id="collections">
        <div class="an-container">
            <div class="an-section-heading">
                <span class="an-eyebrow">Browse Gallery</span>
                <h2>Discover the Community's Best Work</h2>

                <p>
                    Filter the gallery by collection and explore creativity from
                    across the Anime Nigeria community.
                </p>
            </div>

            <!-- Filter Buttons -->
            <div class="an-gallery-filter">
                <button class="an-gallery-filter__btn is-active" data-filter="all">All</button>
                <button class="an-gallery-filter__btn" data-filter="fanart">Fan Art</button>
                <button class="an-gallery-filter__btn" data-filter="cosplay">Cosplay</button>
                <button class="an-gallery-filter__btn" data-filter="photo">Photography</button>
                <button class="an-gallery-filter__btn" data-filter="events">Community Moments</button>
                <button class="an-gallery-filter__btn" data-filter="challenge">Challenge Highlights</button>
            </div>

            <div class="an-gallery-grid">

                <!-- Fan Art -->
                <article class="an-gallery-item an-reveal" data-category="fanart">
                    <img src="/uploads/1783455914832.png" alt="">

                    <div class="an-gallery-item__overlay">
                        <span>Fan Art</span>
                        <h3>Beyond the Gate</h3>
                    </div>
                </article>

                <article class="an-gallery-item an-reveal" data-category="cosplay">
                    <img src="/uploads/frieren-poster.webp" alt="">

                    <div class="an-gallery-item__overlay">
                        <span>Cosplay</span>
                        <h3>Hashira Gathering</h3>
                    </div>
                </article>

                <article class="an-gallery-item an-reveal" data-category="photo">
                    <img src="/uploads/Icon-Anime-Nigeria-Logo-768x752.png" alt="">

                    <div class="an-gallery-item__overlay">
                        <span>Photography</span>
                        <h3>Anime Meetup</h3>
                    </div>
                </article>

                <article class="an-gallery-item an-reveal" data-category="events">
                    <img src="/uploads/Landscape-Anime-Nigeria-Logo.png" alt="">

                    <div class="an-gallery-item__overlay">
                        <span>Community</span>
                        <h3>Watch Party</h3>
                    </div>
                </article>

                <article class="an-gallery-item an-reveal" data-category="challenge">
                    <img src="/uploads/upscalemedia-transformed (1).png" alt="">

                    <div class="an-gallery-item__overlay">
                        <span>Challenge</span>
                        <h3>Voice Acting Winner</h3>
                    </div>
                </article>

                <article class="an-gallery-item an-reveal" data-category="fanart">
                    <img src="public/uploads/upscalemedia-transformed.png" alt="">

                    <div class="an-gallery-item__overlay">
                        <span>Fan Art</span>
                        <h3>Sky of Spirits</h3>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- =============================================================================
    COMMUNITY SPOTLIGHT
    ============================================================================= -->
    <section class="an-community-spotlight">
        <div class="an-container">
            <div class="an-section-heading an-reveal">
                <span class="an-eyebrow">Community Spotlight</span>
                <h2>Celebrating Outstanding Creativity</h2>

                <p>
                    Every so often, we shine a light on creators whose passion,
                    originality and love for anime inspire the Anime Nigeria community.
                </p>
            </div>

            <article class="an-cgallery-spotlight an-reveal">
                <div class="an-cgallery-spotlight__image">
                    <img
                        src="https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1200&q=80"
                        alt="Featured community artwork">
                </div>

                <div class="an-cgallery-spotlight__content an-reveal">
                    <span class="an-cgallery-spotlight__tag">Featured Fan Artist</span>
                    <h3 class="an-cgallery-spotlight__title">Beyond the Gate</h3>
                    <p class="an-cgallery-spotlight__creator">by <strong>Emmanuel Samuel</strong></p>

                    <p class="an-cgallery-spotlight__description">
                        Inspired by fantasy adventure anime, this digital illustration
                        combines vibrant colours, cinematic lighting and detailed
                        character design to create an unforgettable scene.
                    </p>

                    <div class="an-cgallery-spotlight__meta">
                        <div class="an-cgallery-spotlight__stat">
                            <i class="fa-solid fa-award"></i>
                            <span>Editor's Pick</span>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <!-- =============================================================================
    COMMUNITY GUIDELINES
    ============================================================================= -->
    <section class="an-gallery-guidelines">
        <div class="an-container">
            <div class="an-section-heading an-reveal">
                <span class="an-eyebrow">Community Guidelines</span>
                <h2>Built on Respect, Creativity and Community</h2>

                <p>
                    The Anime Nigeria Gallery is a place to celebrate creativity.
                    These simple guidelines help keep it welcoming, inspiring and
                    enjoyable for everyone.
                </p>
            </div>

            <div class="an-gallery-guidelines__grid">
                <article class="an-guideline an-reveal">
                    <div class="an-guideline__icon"><i class="fa-solid fa-heart"></i></div>
                    <h3>Respect Every Creator</h3>

                    <p>
                        Encourage artists, cosplayers and photographers with
                        constructive feedback and kindness.
                    </p>
                </article>

                <article class="an-guideline an-reveal">
                    <div class="an-guideline__icon"><i class="fa-solid fa-copyright"></i></div>
                    <h3>Share Original Work</h3>

                    <p>
                        Only upload creations you own or have permission to share.
                        Always give proper credit where it's due.
                    </p>
                </article>

                <article class="an-guideline an-reveal">
                    <div class="an-guideline__icon"><i class="fa-solid fa-shield-heart"></i></div>
                    <h3>Keep It Appropriate</h3>

                    <p>
                        Content should remain suitable for the Anime Nigeria
                        community and follow our community standards.
                    </p>

                </article>

                <article class="an-guideline an-reveal">
                    <div class="an-guideline__icon">
                        <i class="fa-solid fa-users"></i>
                    </div>

                    <h3>Celebrate Together</h3>

                    <p>
                        Cheer on fellow creators, participate in events and help
                        build a positive anime community across Nigeria.
                    </p>
                </article>
            </div>
        </div>
    </section>

    <!-- =============================================================================
    FINAL CTA
    ============================================================================= -->
    <section class="an-gallery-cta">
        <div class="an-gallery-cta__glow an-gallery-cta__glow--one" aria-hidden="true"></div>
        <div class="an-gallery-cta__glow an-gallery-cta__glow--two" aria-hidden="true"></div>

        <div class="an-container">
            <div class="an-gallery-cta__card an-reveal">
                <span class="an-eyebrow">Join the Journey</span>
                <h2>Become Part of Anime Nigeria's Story</h2>

                <p>
                    Every artwork, cosplay, photo and community memory helps tell the
                    story of anime fans across Nigeria. Explore, get inspired and join
                    the community to participate in future events, challenges and
                    showcases.
                </p>

                <div class="an-gallery-cta__actions">
                    <a href="/our-community" class="an-btn an-btn--primary">
                        Join the Community
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php' ?>