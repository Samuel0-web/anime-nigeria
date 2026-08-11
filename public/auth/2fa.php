<?php
$pageTitle = "Two-Factor Authentication | Anime Nigeria";
$pageDescription = "Verify your identity with two-factor authentication.";

require_once __DIR__ . "/partials/meta.php";

if (!$auth->hasPendingTwoFactor()) {
    header("Location: /login");
    exit;
}
?>
<body data-page="2fa">
    <main class="an-auth an-auth--2fa">
        <div class="an-auth__glow an-auth__glow--one" aria-hidden="true"></div>
        <div class="an-auth__glow an-auth__glow--two" aria-hidden="true"></div>

        <section class="an-auth__card">
            <a href="/" class="an-auth__logo" aria-label="Anime Nigeria Home">
                <img src="/uploads/Landscape-Anime-Nigeria-Logo.png" alt="Anime Nigeria">
            </a>

            <div class="an-auth__2fa-icon" aria-hidden="true">
                <i class="fa-solid fa-shield-halved"></i>
            </div>

            <div class="an-auth__intro">
                <h1>Two-Factor Authentication</h1>

                <p id="2fa-description" aria-live="polite">
                    Enter the 6-digit code from your authenticator app to verify your identity.
                </p>
            </div>

            <!-- Authenticator code -->
            <form class="an-auth__form an-auth__form--totp" id="totp-form" novalidate>
                <div class="an-auth__field an-auth__field--otp">
                    <fieldset class="an-auth__otp-fieldset">
                        <legend class="an-auth__sr-only">
                            Enter the 6-digit authentication code
                        </legend>

                        <div class="an-auth__otp" role="group" aria-label="6-digit
                            authentication code"
                        >
                            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                                class="an-auth__otp-input" autocomplete="one-time-code"
                                aria-label="Digit 1 of 6" aria-describedby="totp-error"
                                data-otp-index="0"
                            >

                            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                                class="an-auth__otp-input" autocomplete="one-time-code"
                                aria-label="Digit 2 of 6" aria-describedby="totp-error"
                                data-otp-index="1"
                            >

                            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                                class="an-auth__otp-input" autocomplete="one-time-code"
                                aria-label="Digit 3 of 6" aria-describedby="totp-error"
                                data-otp-index="2"
                            >

                            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                                class="an-auth__otp-input" autocomplete="one-time-code"
                                aria-label="Digit 4 of 6" aria-describedby="totp-error"
                                data-otp-index="3"
                            >

                            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                                class="an-auth__otp-input" autocomplete="one-time-code"
                                aria-label="Digit 5 of 6" aria-describedby="totp-error"
                                data-otp-index="4"
                            >

                            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                                class="an-auth__otp-input" autocomplete="one-time-code"
                                aria-label="Digit 6 of 6" aria-describedby="totp-error"
                                data-otp-index="5"
                            >
                        </div>
                    </fieldset>

                    <small class="an-auth__error" id="totp-error"></small>
                </div>

                <div class="an-auth__message" role="alert" aria-live="assertive"></div>

                <button class="an-btn an-btn--primary an-auth__submit" type="submit">
                    Verify Code
                </button>
            </form>

            <!-- Recovery code -->
            <form class="an-auth__form an-auth__form--recovery" id="recovery-form" 
                novalidate hidden>

                <div class="an-auth__field">
                    <input type="text" id="recovery-code" name="recovery_code"
                        class="an-auth__input an-auth__recovery-field" placeholder=" "
                        autocomplete="off" autocapitalize="characters" spellcheck="false"
                        aria-describedby="recovery-error"
                    >

                    <label for="recovery-code">Recovery Code</label>
                    <small class="an-auth__error" id="recovery-error"></small>
                </div>

                <div class="an-auth__message" role="alert" aria-live="assertive"></div>

                <button class="an-btn an-btn--primary an-auth__submit" type="submit">
                    Verify Recovery Code
                </button>
            </form>

            <button type="button" class="an-auth__switch" id="switch-mode">
                Use a recovery code instead
            </button>
        </section>
    </main>
</body>
</html>