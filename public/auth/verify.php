<?php

require_once __DIR__ . '/partials/meta.php';
use App\Auth\Auth;
use App\Database\Database;
use App\Mail\Mail;
use App\Mail\SmtpMailer;
$db = Database::connection();

$mail = new Mail(
    new SmtpMailer(),
    $_ENV['APP_URL']
);

$auth = new Auth($db, $mail);

$result = $auth->verifyEmail(
    $_GET['token'] ?? ''
);

$status = 'invalid';

if ($result !== false) {
    $status = $result['status'];

    if ($status === 'verified') {
        $_SESSION['pending_username_user_id'] = $result['user']['id'];
    }
}

$pageTitle = 'Verify your account | Anime Nigeria';
$pageDescription = 'Verify your account';
?>

<main class="an-auth">
    <div class="an-auth__glow an-auth__glow--one"></div>
    <div class="an-auth__glow an-auth__glow--two"></div>

    <section class="an-auth__card">
        <a href="/" class="an-auth__logo">
            <img src="/uploads/Landscape-Anime-Nigeria-Logo.png" alt="Anime Nigeria">
        </a>
        <?php if ($status === 'verified'): ?>
            <div class="an-auth__verify">
                <div class="an-auth__verify-icon is-success">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <h1>Email Verified</h1>

                <p>
                    Your email has been verified successfully.
                    You're almost done setting up your Anime Nigeria account.
                </p>

                <a href="/auth/username" class="an-btn an-btn-primary an-auth__verify-button"
                    id="continue-button">
                    Continue
                </a>
            </div>
        <?php else: ?>
            <div class="an-auth__verify">
                <div class="an-auth__verify-icon is-error">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>

                <h1>Verification Failed</h1>

                <p>
                    We couldn't verify your email with this link.
                    It may be invalid, expired, or already used.
                </p>

                <a href="/auth/resend-verification" class="an-btn an-btn-primary an-auth__verify-button">
                    Get a New Verification Link
                </a>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php if ($status === 'verified'): ?>
    <script>
        setTimeout(() => {
            window.location.href = "/auth/username";
        }, 5000);
    </script>
<?php endif; ?>
</body>
</html>