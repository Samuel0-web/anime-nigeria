<?php
$pageTitle = "Sign In | Anime Nigeria";
$pageDescription = "Sign In to your Anime Nigeria account to access your profile and community features.";

require_once __DIR__ . "/partials/meta.php";

$auth->requireGuest();
?>
<body data-page="login">
    <main class="an-auth an-auth--login">
        <div class="an-auth__glow an-auth__glow--one" aria-hidden="true"></div>
        <div class="an-auth__glow an-auth__glow--two" aria-hidden="true"></div>

        <section class="an-auth__card">
            <a href="/" class="an-auth__logo" aria-label="Anime Nigeria Home">
                <img src="/uploads/Landscape-Anime-Nigeria-Logo.png" alt="Anime Nigeria">
            </a>

            <div class="an-auth__intro">
                <h1>Welcome Back</h1>
                <p>Sign in to continue your Anime Nigeria journey.</p>
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

                <!-- Password -->
                <div class="an-auth__field">
                    <input type="password" id="password" name="password" class="an-auth__input"
                        placeholder=" " autocomplete="current-password" required>

                    <label for="password">Password</label>

                    <button class="an-auth__toggle-password" type="button" aria-label="Show password">
                        <i class="fa-regular fa-eye"></i>
                    </button>

                    <small class="an-auth__error"></small>
                </div>
                
                <div class="an-auth__message"></div>

                <div class="an-auth__row">
                    <label class="an-auth__checkbox">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="an-auth__checkbox-box" aria-hidden="true"></span>
                        <span class="an-auth__checkbox-text">Remember me</span>

                    </label>

                    <a href="/auth/forgot-password">Forgot password?</a>
                </div>

                <button class="an-btn an-btn--primary an-auth__submit" type="submit">
                    Sign In
                </button>
            </form>

            <div class="an-auth__divider">OR</div>

            <button class="an-auth__google" type="button" data-url="/auth/google">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="48" height="48">
                    <path fill="#4285F4" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#34A853" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.96 16.46 0 20.12 0 24c0 3.88.96 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#EA4335" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>

                <span>Continue with Google</span>
            </button>

            <p class="an-auth__footer">Don't have an account? <a href="/join">Create one</a></p>
        </section>
    </main>
</body>
</html>