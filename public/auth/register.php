<?php
$pageTitle = "Join The Community | Anime Nigeria";
$pageDescription = "Create your Anime Nigeria account and join a growing community of anime fans across Nigeria.";

require_once __DIR__ . "/partials/meta.php";

$auth->requireGuest();
?>
<body data-page="register">
    <main class="an-auth an-auth--register">
        <div class="an-auth__glow an-auth__glow--one" aria-hidden="true"></div>
        <div class="an-auth__glow an-auth__glow--two" aria-hidden="true"></div>

        <section class="an-auth__card">
            <a href="/" class="an-auth__logo" aria-label="Anime Nigeria Home">
                <img src="/uploads/Landscape-Anime-Nigeria-Logo.png" alt="Anime Nigeria">
            </a>

            <div class="an-auth__intro">
                <h1>Join Anime Nigeria</h1>
                <p>Create your account and become part of Nigeria's growing anime community.</p>
            </div>

            <form class="an-auth__form" action="" method="post" novalidate>

                <!-- FULL NAME -->
                <div class="an-auth__field">
                    <input type="text" id="fullname" name="fullname" class="an-auth__input" placeholder=" "
                        autocomplete="fullname" required>

                    <label for="name">Full Name</label>
                    <span class="an-auth__status"></span>
                    <small class="an-auth__error"></small>
                </div>

                <!-- EMAIL -->
                <div class="an-auth__field">
                    <input type="email" id="email" name="email" class="an-auth__input" placeholder=" "
                        autocomplete="email" required aria-describedby="email-error">

                    <label for="email">Email Address</label>
                    <span class="an-auth__status"></span>
                    <small id="email-error" class="an-auth__error"></small>
                </div>


                <!-- PASSWORD -->
                <div class="an-auth__field">
                    <input type="password" id="password" name="password" class="an-auth__input"
                        placeholder=" " autocomplete="new-password" required>

                    <label for="password">Password</label>

                    <button class="an-auth__toggle-password" type="button">
                        <i class="fa-regular fa-eye"></i>
                    </button>

                    <small class="an-auth__error"></small>
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

                    <label for="confirm-password">Confirm Password</label>
                    <span class="an-auth__validation"></span>

                    <button class="an-auth__toggle-password" type="button" aria-label="Show password">
                        <i class="fa-regular fa-eye"></i>
                    </button>

                    <small class="an-auth__error"></small>
                </div>

                <label class="an-auth__checkbox">
                    <input type="checkbox" id="terms" name="terms" value="1" required>
                    <span class="an-auth__checkbox-box"></span>

                    <span class="an-auth__checkbox-text">I agree to the <a href="/terms">Terms</a> &
                        <a href="/privacy">Privacy Policy</a>
                    </span>
                </label>

                <small class="an-auth__checkbox-error"></small>

                <button class="an-btn an-btn--primary an-auth__submit" type="submit" disabled>
                    Create Account
                </button>
            </form>

            <div class="an-auth__success" hidden>
                <div class="an-auth__success-icon">
                    <i class="fa-solid fa-envelope-circle-check"></i>
                </div>

                <h2>Verify your email</h2>

                <p>
                    We've sent a verification link to
                    <strong class="an-auth__masked-email"></strong>
                </p>

                <p class="an-auth__success-note">
                    Click the link in your email to verify your account.
                    The link expires in <strong>24 hours</strong>.
                </p>

                <button class="an-btn an-btn--primary an-auth__resend" disabled type="button">
                    Resend Email (<span class="an-auth__countdown">60</span>s)
                </button>
            </div>

            <div class="an-auth__divider">OR</div>

            <button class="an-auth__google" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="48" height="48">
                    <path fill="#4285F4" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#34A853" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.96 16.46 0 20.12 0 24c0 3.88.96 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#EA4335" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>

                <span>Sign up with Google</span>
            </button>

            <p class="an-auth__footer">Already have an account? <a href="/login">Sign In</a></p>
        </section>
    </main>
</body>
</html>