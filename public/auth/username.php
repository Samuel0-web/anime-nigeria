<?php
require_once __DIR__ . "/partials/meta.php";

if (!isset($_SESSION['pending_username_user_id'])) {
    header('Location: /login');
    exit;
}

$pageTitle = "Choose Your Username | Anime Nigeria";
$pageDescription = "Choose a unique username to complete your Anime Nigeria account setup.";

?>
<body data-page="username">
    <main class="an-auth an-auth--username">
        <div class="an-auth__glow an-auth__glow--one" aria-hidden="true"></div>
        <div class="an-auth__glow an-auth__glow--two" aria-hidden="true"></div>

        <section class="an-auth__card">
            <a href="/" class="an-auth__logo" aria-label="Anime Nigeria Home">
                <img src="/uploads/Landscape-Anime-Nigeria-Logo.png" alt="Anime Nigeria">
            </a>

            <div class="an-auth__intro">
                <h1>Choose Your Username</h1>

                <p>
                    This is how the Anime Nigeria community will know you. It will appear on your
                    profile, trivia leaderboards, awards, and challenges.
                </p>
            </div>

            <div class="an-auth__username-preview" hidden>
                Your profile:
                <strong class="an-auth__username-preview-value">@</strong>
            </div>

            <form class="an-auth__form" action="" method="post" novalidate>

                <!-- Username -->
                <div class="an-auth__field an-auth__field--username">
                    <input type="text" id="username" name="username" class="an-auth__input" 
                        placeholder=" " autocomplete="username" maxlength="20"
                        spellcheck="false" autocapitalize="off" required>

                    <label for="username">Username</label>
                    <span class="an-auth__status" aria-hidden="true"></span>

                    <small class="an-auth__hint">
                        3–20 characters &bull; letters, numbers and underscores only.
                    </small>

                    <small class="an-auth__error"></small>
                </div>

                <button class="an-btn an-btn--primary an-auth__submit" type="submit">
                    Complete Setup
                </button>

                <p class="an-auth__note">
                    You can change your username later from your account settings.
                </p>
            </form>
        </section>
    </main>
</body>
</html>