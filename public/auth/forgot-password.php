<?php
$pageTitle = "Forgot Password | Anime Nigeria";
$pageDescription = "Reset your Anime Nigeria account password securely.";

require_once __DIR__ . "/partials/meta.php";
?>

<main class="an-auth an-auth--forgot">
    <div class="an-auth__glow an-auth__glow--one" aria-hidden="true"></div>
    <div class="an-auth__glow an-auth__glow--two" aria-hidden="true"></div>

    <section class="an-auth__card">
        <a href="/" class="an-auth__logo" aria-label="Anime Nigeria Home">
            <img src="/uploads/Landscape-Anime-Nigeria-Logo.png" alt="Anime Nigeria">
        </a>

        <div class="an-auth__intro">
            <h1>Forgot Password</h1>
            <p>
                Enter the email address linked to your Anime Nigeria account and we'll email
                you a secure password reset link.
            </p>
        </div>

        <form class="an-auth__form" action="" method="post" novalidate>

            <!-- Email -->
            <div class="an-auth__field">
                <input type="email" id="email" name="email" class="an-auth__input" placeholder=" "
                    autocomplete="email" required aria-describedby="email-error">

                <label for="email">Email Address</label>
                <span class="an-auth__status" aria-hidden="true"></span>
                <small id="email-error" class="an-auth__error"></small>
            </div>

            <button class="an-btn an-btn--primary an-auth__submit" type="submit">
                Send Reset Link
            </button>
        </form>

        <div class="an-auth__success" hidden>
            <div class="an-auth__success-icon">
                <i class="fa-solid fa-envelope-circle-check"></i>
            </div>

            <h2>Check Your Email</h2>

            <p>
                We've sent a secure password reset link to:
                <strong class="an-auth__masked-email"></strong>
            </p>

            <p class="an-auth__success-note">
                The link expires in <strong>10 minutes</strong>.
                If you don't receive an email within a few minutes, check
                your spam folder or try again.
            </p>

            <button class="an-btn an-btn--primary an-auth__resend" disabled>
                Resend Email (<span class="an-auth__countdown">60</span>s)
            </button>
        </div>

        <div class="an-auth__divider2"></div>

        <div class="an-auth__footer">
            <a href="/login">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Sign in
            </a>
        </div>
    </section>
</main>
</body>
</html>