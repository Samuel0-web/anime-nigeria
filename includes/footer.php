<?php
?>

<section class="an-newsletter" aria-labelledby="an-newsletter-heading">
    <div class="an-newsletter__glow an-newsletter__glow--one" aria-hidden="true"></div>
    <div class="an-newsletter__glow an-newsletter__glow--two" aria-hidden="true"></div>

    <div class="an-container">
        <div class="an-newsletter__card">
            <span class="an-eyebrow">
                <i class="fa-solid fa-envelope" aria-hidden="true"></i>Newsletter
            </span>

            <h2 class="an-newsletter__heading" id="an-newsletter-heading">Stay in the Loop</h2>

            <p class="an-newsletter__description">
                Get the latest anime news, reviews, community updates and recommendations delivered
                straight to your inbox.
            </p>

            <form class="an-newsletter__form">
                <input class="an-newsletter__input" type="email" name="email"
                    placeholder="Enter your email" autocomplete="email" required>

                <button class="an-btn an-btn--primary" type="submit">Join Community</button>
            </form>
        </div>
    </div>
</section>

<footer class="an-footer">
    <div class="an-container an-footer__inner">
        <a href="/" class="an-footer__brand" aria-label="Anime Nigeria">
            <img src="/uploads/Landscape-Anime-Nigeria-Logo.png" alt="Anime Nigeria">
        </a>

        <ul class="an-footer__socials" aria-label="Anime Nigeria on social media">
            <li><a href="#" class="an-footer__social-link" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a></li>
            <li><a href="#" class="an-footer__social-link" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a></li>
            <li><a href="#" class="an-footer__social-link" aria-label="LinkedIn"><i class="fa-brands fa-linkedin"></i></a></li>
            <li><a href="#" class="an-footer__social-link" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a></li>
        </ul>

        <!-- New Legal Links Section -->
        <ul class="an-footer__legal" aria-label="Legal policies">
            <li><a href="/terms" class="an-footer__legal-link">Terms of Use</a></li> &middot;
            <li><a href="/privacy" class="an-footer__legal-link">Privacy Policy</a></li>
        </ul>

        <p class="an-footer__copy">&copy; <?= date('Y') ?> Anime Nigeria. All rights reserved.</p>
    </div>
</footer>

</body>
</html>