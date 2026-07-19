<?php
$pageTitle = "Reset Password | Anime Nigeria";
$pageDescription = "Create a new password to securely regain access to your Anime Nigeria account.";

require_once __DIR__ . "/partials/meta.php";
?>

<main class="an-auth an-auth--register">
    <div class="an-auth__glow an-auth__glow--one" aria-hidden="true"></div>
    <div class="an-auth__glow an-auth__glow--two" aria-hidden="true"></div>

    <section class="an-auth__card">
        <a href="/" class="an-auth__logo" aria-label="Anime Nigeria Home">
            <img src="/uploads/Landscape-Anime-Nigeria-Logo.png" alt="Anime Nigeria">
        </a>

        <div class="an-auth__intro">
            <h1>Reset Your Password</h1>
            <p>Create a new password to regain access to your Anime Nigeria account.</p>
        </div>

        <form class="an-auth__form" action="" method="post" novalidate>
            
            <!-- PASSWORD -->
            <div class="an-auth__field">
                <input type="password" id="password" name="password" class="an-auth__input"
                    placeholder=" " autocomplete="new-password" required>

                <label for="password">New Password</label>

                <button class="an-auth__toggle-password" type="button">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>

            <ul class="an-auth__password-rules">
                <li data-rule="length">Minimum 8 characters</li>
                <li data-rule="upper">At least one uppercase letter</li>
                <li data-rule="number">At least one number</li>
                <li data-rule="symbol">At least one symbol (! @ # $ % & * ? ,)</li>
            </ul>

            <!-- CONFIRM PASSWORD -->
            <div class="an-auth__field">
                <input type="password" id="confirm-password" name="confirm_password"
                    class="an-auth__input" placeholder=" " autocomplete="new-password" required>

                <label for="confirm-password">Confirm New Password</label>
                <span class="an-auth__validation"></span>

                <button class="an-auth__toggle-password" type="button" aria-label="Show password">
                    <i class="fa-regular fa-eye"></i>
                </button>

                <small class="an-auth__error"></small>
            </div>

            <button class="an-btn an-btn--primary an-auth__submit" type="submit" disabled>
                Reset Password
            </button>
        </form>

        <div class="an-auth__success" hidden>
            <div class="an-auth__success-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <h2>Password Updated!</h2>

            <p>
                Your Anime Nigeria password has been successfully changed.
                You can now sign in using your new password.
            </p>

            <a href="/login" class="an-btn an-btn--primary">
                Continue to Sign In
            </a>

            <p class="an-auth__success-note">
                You may be asked to sign in again on your other devices.
            </p>
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