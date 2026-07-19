<?php
declare(strict_types=1);
$pageTitle = "Award Categories | Anime Nigeria";
$pageDescription = "Explore every Anime Nigeria Awards category and discover what each award recognizes and celebrates.";
require_once __DIR__ . '/../../includes/header.php';
?>

<main id="main-content">
    <!-- =============================================================================
    CATEGORIES HERO
    ============================================================================= -->
    <section class="an-awards-categories-hero">
        <div class="an-awards-categories-hero__glow an-awards-categories-hero__glow--one" aria-hidden="true"></div>
        <div class="an-awards-categories-hero__glow an-awards-categories-hero__glow--two" aria-hidden="true"></div>

        <div class="an-container">
            <div class="an-awards-categories-hero__inner an-reveal">

                <span class="an-eyebrow">
                    <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                    Award Categories
                </span>

                <span class="an-awards-categories-hero__badge">20+ Community Awards</span>
                <h1 class="an-awards-categories-hero__heading">Every Award Tells a Different Story.</h1>

                <p class="an-awards-categories-hero__description">
                    Anime is more than one trophy. From unforgettable protagonists and
                    breathtaking animation to iconic openings and emotional moments,
                    every category celebrates a different part of the anime experience
                    loved by the Anime Nigeria community.
                </p>

                <div class="an-awards-categories-hero__actions">
                    <a href="#categories" class="an-btn an-btn--primary">
                        Explore Categories
                    </a>

                    <a href="/awards-overview" class="an-btn an-btn--secondary">
                        About ANAA
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- =============================================================================
    UNDERSTANDING THE AWARDS
    ============================================================================= -->

    <section class="an-awards-categories-intro">
        <div class="an-container">
            <div class="an-awards-categories-intro__grid">

                <div class="an-awards-categories-intro__content an-reveal">
                    <span class="an-eyebrow">
                        <i class="fa-solid fa-circle-info"></i>
                        Understanding the Awards
                    </span>

                    <h2 class="an-awards-categories-intro__heading">
                        Every Category Exists for a Reason
                    </h2>

                    <p>
                        Anime is enjoyed in countless ways. Some fans admire incredible
                        animation, others remember unforgettable characters, emotional
                        moments, inspiring soundtracks or world-building.
                    </p>

                    <p>
                        The Anime Nigeria Awards recognises every part of that experience
                        through carefully selected categories that celebrate creativity,
                        storytelling and the moments that unite our community.
                    </p>

                    <p>
                        Whether it's the biggest award of the year or a fan-favourite
                        performance, every category represents a unique achievement worth
                        celebrating.
                    </p>
                </div>

                <aside class="an-awards-categories-intro__stats">

                    <article class="an-awards-stat an-reveal">
                        <span class="an-awards-stat__number">20+</span>

                        <div>
                            <h3>Award Categories</h3>
                            <p>Celebrating every aspect of anime.</p>
                        </div>
                    </article>

                    <article class="an-awards-stat an-reveal">
                        <span class="an-awards-stat__number">100%</span>

                        <div>
                            <h3>Community Driven</h3>
                            <p>Nominations and voting come from anime fans.</p>
                        </div>
                    </article>

                    <article class="an-awards-stat an-reveal">
                        <span class="an-awards-stat__number">∞</span>

                        <div>
                            <h3>Shared Passion</h3>
                            <p>Every category reflects what the community loves most.</p>
                        </div>
                    </article>

                </aside>

            </div>
        </div>
    </section>

    <!-- =============================================================================
    AWARD CATEGORY COLLECTIONS
    ============================================================================= -->

    <section class="an-category-groups" id="categories">
        <div class="an-container">

            <div class="an-section-heading an-reveal">
                <span class="an-eyebrow">Award Collections</span>

                <h2>Every Category Has a Place</h2>

                <p>
                    Rather than one long list, the Anime Nigeria Awards are organised
                    into collections that celebrate different parts of the anime
                    experience.
                </p>
            </div>

            <div class="an-category-groups__grid">

                <!-- ============================================================= -->
                <!-- STORY -->
                <!-- ============================================================= -->

                <article class="an-category-group an-category-group--gold an-reveal">

                    <header class="an-category-group__header">

                        <div class="an-category-group__icon">
                            <i class="fa-solid fa-book-open"></i>
                        </div>

                        <div>
                            <span class="an-category-group__label">
                                Story Awards
                            </span>

                            <h3>Characters & Storytelling</h3>
                        </div>

                    </header>

                    <ul class="an-category-group__list">
                        <li>Anime of the Year</li>
                        <li>Best Main Character</li>
                        <li>Best Supporting Character</li>
                        <li>Best Antagonist</li>
                        <li>Most Emotional Moment</li>
                    </ul>

                </article>

                <!-- ============================================================= -->
                <!-- VISUAL -->
                <!-- ============================================================= -->

                <article class="an-category-group an-category-group--sakura an-reveal">

                    <header class="an-category-group__header">

                        <div class="an-category-group__icon">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </div>

                        <div>
                            <span class="an-category-group__label">
                                Visual Awards
                            </span>

                            <h3>Animation & Art</h3>
                        </div>

                    </header>

                    <ul class="an-category-group__list">
                        <li>Best Animation</li>
                        <li>Best Fight</li>
                        <li>Best World Building</li>
                        <li>Best Anime Film</li>
                    </ul>

                </article>

                <!-- ============================================================= -->
                <!-- MUSIC -->
                <!-- ============================================================= -->

                <article class="an-category-group an-category-group--teal an-reveal">

                    <header class="an-category-group__header">

                        <div class="an-category-group__icon">
                            <i class="fa-solid fa-music"></i>
                        </div>

                        <div>
                            <span class="an-category-group__label">
                                Music Awards
                            </span>

                            <h3>Sound & Themes</h3>
                        </div>

                    </header>

                    <ul class="an-category-group__list">
                        <li>Best Opening Theme</li>
                        <li>Best Ending Theme</li>
                        <li>Best Soundtrack</li>
                        <li>Best Voice Performance</li>
                    </ul>

                </article>

                <!-- ============================================================= -->
                <!-- DISCOVERY -->
                <!-- ============================================================= -->

                <article class="an-category-group an-category-group--ember an-reveal">

                    <header class="an-category-group__header">

                        <div class="an-category-group__icon">
                            <i class="fa-solid fa-seedling"></i>
                        </div>

                        <div>
                            <span class="an-category-group__label">
                                Discovery Awards
                            </span>

                            <h3>New & Rising Series</h3>
                        </div>

                    </header>

                    <ul class="an-category-group__list">
                        <li>Best New Anime</li>
                        <li>Most Underrated Anime</li>
                        <li>Breakout Series</li>
                        <li>Best Surprise of the Year</li>
                    </ul>

                </article>

                <!-- ============================================================= -->
                <!-- GENRE -->
                <!-- ============================================================= -->

                <article class="an-category-group an-category-group--crimson an-reveal">

                    <header class="an-category-group__header">

                        <div class="an-category-group__icon">
                            <i class="fa-solid fa-tags"></i>
                        </div>

                        <div>
                            <span class="an-category-group__label">
                                Genre Awards
                            </span>

                            <h3>Celebrating Every Genre</h3>
                        </div>

                    </header>

                    <ul class="an-category-group__list">
                        <li>Best Action Anime</li>
                        <li>Best Romance Anime</li>
                        <li>Best Fantasy Anime</li>
                        <li>Best Slice of Life Anime</li>
                    </ul>

                </article>

                <!-- ============================================================= -->
                <!-- COMMUNITY -->
                <!-- ============================================================= -->

                <article class="an-category-group an-category-group--violet an-reveal">

                    <header class="an-category-group__header">

                        <div class="an-category-group__icon">
                            <i class="fa-solid fa-masks-theater"></i>
                        </div>

                        <div>
                            <span class="an-category-group__label">
                                Performance Awards
                            </span>

                            <h3>Voices That Brought Stories to Life</h3>
                        </div>

                    </header>

                    <ul class="an-category-group__list">
                        <li>Best Voice Performance (Japanese)</li>
                        <li>Best Voice Performance (English)</li>
                        <li>Best Ensemble Cast</li>
                    </ul>

                </article>

            </div>

        </div>
    </section>

    <!-- =============================================================================
    HOW CATEGORIES ARE JUDGED
    ============================================================================= -->

    <section class="an-category-judging">
        <div class="an-container">

            <div class="an-section-heading an-reveal">
                <span class="an-eyebrow">Judging Criteria</span>

                <h2>What Makes a Winner?</h2>

                <p>
                    Every category is evaluated differently, but these are the core
                    qualities voters and judges look for when recognising excellence.
                </p>
            </div>

            <div class="an-category-judging__grid">

                <article class="an-category-judging__item an-reveal">
                    <div class="an-category-judging__icon">
                        <i class="fa-solid fa-book-open"></i>
                    </div>

                    <h3>Story & Impact</h3>

                    <p>
                        Memorable storytelling, emotional weight and lasting impact.
                    </p>
                </article>

                <article class="an-category-judging__item an-reveal">
                    <div class="an-category-judging__icon">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <h3>Character Performance</h3>

                    <p>
                        Character growth, personality and unforgettable moments.
                    </p>
                </article>

                <article class="an-category-judging__item an-reveal">
                    <div class="an-category-judging__icon">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>

                    <h3>Visual Excellence</h3>

                    <p>
                        Animation, direction, cinematography and artistic quality.
                    </p>
                </article>

                <article class="an-category-judging__item an-reveal">
                    <div class="an-category-judging__icon">
                        <i class="fa-solid fa-users"></i>
                    </div>

                    <h3>Community Reception</h3>

                    <p>
                        How strongly the community connected with the nominee.
                    </p>
                </article>

                <article class="an-category-judging__item an-reveal">
                    <div class="an-category-judging__icon">
                        <i class="fa-solid fa-music"></i>
                    </div>

                    <h3>Sound & Music</h3>

                    <p>
                        Voice acting, soundtrack and memorable opening or ending themes.
                    </p>
                </article>

                <article class="an-category-judging__item an-reveal">
                    <div class="an-category-judging__icon">
                        <i class="fa-solid fa-earth-africa"></i>
                    </div>

                    <h3>Legacy</h3>

                    <p>
                        Influence, discussion and the impression left on the anime community.
                    </p>
                </article>

            </div>

        </div>
    </section>

    <!-- =============================================================================
    THE HALL AWAITS
    ============================================================================= -->

    <section class="an-category-hall">
        <div class="an-container">

            <div class="an-category-hall__card an-reveal">

                <div class="an-category-hall__badge">
                    <i class="fa-solid fa-trophy"></i>
                    Future Hall of Fame
                </div>

                <h2>The First Winners Haven't Been Crowned Yet.</h2>

                <p>
                    Every legendary award show starts somewhere. The first Anime Nigeria
                    Awards will celebrate the anime, characters and creators that defined
                    the year, with every winner becoming part of our permanent Hall of Fame.
                </p>

                <div class="an-category-hall__stats">

                    <div>
                        <strong>0</strong>
                        <span>Winners</span>
                    </div>

                    <div>
                        <strong>20+</strong>
                        <span>Categories</span>
                    </div>

                    <div>
                        <strong>∞</strong>
                        <span>Future Legends</span>
                    </div>

                </div>

            </div>

        </div>
    </section>

    <!-- =============================================================================
    FINAL CTA
    ============================================================================= -->

    <section class="an-category-cta">
        <div class="an-container">

            <div class="an-category-cta__content an-reveal">

                <span class="an-eyebrow">
                    See You At The Awards
                </span>

                <h2>The Stage Is Set</h2>

                <p>
                    Stay connected as nominations, voting and winners unfold throughout
                    the Anime Nigeria Awards season.
                </p>

                <div class="an-category-cta__actions">

                    <a href="/awards/nominations.php"
                    class="an-btn an-btn--primary">
                        Explore Nominations
                    </a>

                    <a href="/awards/overview.php"
                    class="an-btn an-btn--secondary">
                        Learn About ANAA
                    </a>

                </div>

            </div>

        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php' ?>