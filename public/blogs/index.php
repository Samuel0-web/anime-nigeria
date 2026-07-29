<?php
declare(strict_types=1);
$pageTitle = "Blog | Anime Nigeria";
$pageDescription = "Anime news, reviews, recommendations, guides and community stories from Anime Nigeria.";
require_once __DIR__ . '/../../includes/header.php';
?>

<main id="main-content">
    <!-- ===================================================================
         BLOG HERO
         =================================================================== -->
    <section class="an-blog-hero" aria-labelledby="an-blog-hero-heading">
        <div class="an-blog-hero__glow" aria-hidden="true"></div>

        <div class="an-container an-blog-hero__inner">
            <div class="an-blog-hero__content an-reveal" style="--i:0">
                <span class="an-eyebrow an-blog-hero__eyebrow">Anime Nigeria Blog</span>

                <h1 class="an-blog-hero__heading" id="an-blog-hero-heading">Stories for Every Anime Fan</h1>

                <p class="an-blog-hero__paragraph">
                    Discover the latest Anime Nigeria news, anime reviews, recommendations, guides
                    and community stories all in one place.
                </p>

                <form class="an-blog-hero__search" role="search" aria-label="Search blog articles" id="blog-search-form">
                    <i class="fa-solid fa-magnifying-glass an-blog-hero__search-icon" aria-hidden="true"></i>
                    <input
                        type="search"
                        name="q"
                        class="an-blog-hero__search-input"
                        placeholder="Search articles..."
                        aria-label="Search articles"
                        id="blog-search-input"
                    >
                    <button type="submit" class="an-blog-hero__search-button">
                        <span>Search</span>
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

                <p class="an-blog-hero__search-feedback" id="blog-search-feedback" aria-live="polite"></p>

                <div class="an-blog-hero__categories" role="radiogroup" aria-label="Filter articles by category">
                    <button type="button" class="an-blog-hero__category" role="radio" aria-checked="true" tabindex="0" data-category="all">All Posts</button>
                    <button type="button" class="an-blog-hero__category" role="radio" aria-checked="false" tabindex="-1" data-category="news">News &amp; Announcements</button>
                    <button type="button" class="an-blog-hero__category" role="radio" aria-checked="false" tabindex="-1" data-category="reviews">Reviews</button>
                    <button type="button" class="an-blog-hero__category" role="radio" aria-checked="false" tabindex="-1" data-category="recommendations">Recommendations</button>
                    <button type="button" class="an-blog-hero__category" role="radio" aria-checked="false" tabindex="-1" data-category="guides">Guides</button>
                    <button type="button" class="an-blog-hero__category" role="radio" aria-checked="false" tabindex="-1" data-category="features">Features</button>
                    <button type="button" class="an-blog-hero__category" role="radio" aria-checked="false" tabindex="-1" data-category="community">Community</button>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>