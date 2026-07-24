<?php
$pageTitle = "Complete Registration | Anime Nigeria";
$pageDescription = "Complete your Anime Nigeria account.";

require_once __DIR__ . "/../partials/meta.php";

$auth->requireGuest();

if (empty($_SESSION['google_register'])) {
    header("Location: /join");
    exit;
}

$google = $_SESSION['google_register'];
?>

<body data-page="google-register">
    <main class="an-auth an-auth--register">
        <div class="an-auth__glow an-auth__glow--one"></div>
        <div class="an-auth__glow an-auth__glow--two"></div>

        <section class="an-auth__card">
            <a href="/" class="an-auth__logo">
                <img src="/uploads/Landscape-Anime-Nigeria-Logo.png" alt="Anime Nigeria">
            </a>

            <div class="an-auth__intro">
                <img class="an-auth__avatar"
                    src="<?= htmlspecialchars($google['avatar'] ?? '/uploads/default-avatar.png') ?>"
                    alt="Profile photo"
                >

                <h1>Almost there!</h1>
                <p>Welcome <strong><?= htmlspecialchars($google['fullname']) ?></strong></p>
                <p><?= htmlspecialchars($google['email']) ?></p>

                <p>
                    Your Google account has been verified.
                    Please accept our Terms and Privacy Policy to continue.
                </p>
            </div>

            <form class="an-auth__form" novalidate>
                <label class="an-auth__checkbox">
                    <input type="checkbox" name="terms" id="terms">
                    <span class="an-auth__checkbox-box"></span>

                    <span class="an-auth__checkbox-text">
                        I agree to the <a href="/terms">Terms</a> &
                        <a href="/privacy">Privacy Policy</a>
                    </span>
                </label>

                <small class="an-auth__checkbox-error"></small>

                <button class="an-btn an-btn--primary an-auth__submit" type="submit" disabled>
                    Continue
                </button>
            </form>
        </section>
    </main>
</body>
</html>