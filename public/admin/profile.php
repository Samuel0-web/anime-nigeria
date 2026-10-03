<?php
$page_title       = 'Profile';
$page_description = 'Manage your administrator profile, photo and account security.';

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/home'],
    ['label' => 'Profile', 'url' => null],
];

require_once __DIR__ . '/partials/header.php';

// Real data from $user, loaded in partials/header.php.
// $userInitials and $avatarColor are also defined there.
$fullname   = (string) ($user['fullname'] ?? '');
$username   = (string) ($user['username'] ?? '');
$email      = (string) ($user['email'] ?? '');
$role       = (string) ($user['role'] ?? 'admin');
$roleClass  = preg_replace('/[^a-z]/', '', strtolower($role));
$roleLabel  = [
    'admin' => 'Student Council President',
    'moderator' => 'Student Council Member',
][$role] ?? ucfirst($role);
$avatarUrl  = !empty($user['avatar']) ? (string) $user['avatar'] : null;
$isGoogle   = ($user['auth_provider'] ?? 'local') === 'google';
$isVerified = !empty($user['email_verified_at']);

$utc = new DateTimeZone('UTC');
$memberSince = !empty($user['created_at'])
    ? (new DateTimeImmutable($user['created_at'], $utc))->format('M Y')
    : null;
$lastLogin = !empty($user['last_login_at'])
    ? new DateTimeImmutable($user['last_login_at'], $utc)
    : null;

if (!function_exists('akd_admin_render_password_field')) {
    /**
     * One password field with a visibility toggle. $after is trusted static
     * markup rendered between the input and its error message.
     */
    function akd_admin_render_password_field(
        string $name,
        string $label,
        string $id,
        string $autocomplete,
        string $hint = '',
        string $after = '',
        string $extraDescribedBy = ''
    ): void {
        $describedBy = trim($extraDescribedBy . ' ' . $id . '-error');
        ?>
        <div class="akd-admin-field" data-field="<?= htmlspecialchars($name) ?>">
            <label class="akd-admin-field__label" for="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($label) ?></label>
            <div class="akd-admin-field__control">
                <input type="password"
                    id="<?= htmlspecialchars($id) ?>"
                    name="<?= htmlspecialchars($name) ?>"
                    class="akd-admin-field__input akd-admin-field__input--toggle"
                    autocomplete="<?= htmlspecialchars($autocomplete) ?>"
                    aria-describedby="<?= htmlspecialchars($describedBy) ?>"
                >
                <button type="button" class="akd-admin-field__toggle" data-password-toggle aria-label="Show password">
                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            <?php if ($hint !== ''): ?>
                <p class="akd-admin-field__hint"><?= htmlspecialchars($hint) ?></p>
            <?php endif; ?>
            <?= $after ?>
            <p class="akd-admin-field__error" id="<?= htmlspecialchars($id) ?>-error" data-field-error aria-live="polite"></p>
        </div>
        <?php
    }
}

$passwordRulesHtml = '';

if (!$isGoogle) {
    ob_start();
    ?>
    <div class="akd-admin-rules" data-password-rules-wrap>
        <ul class="akd-admin-rules__list" id="adminPasswordRules" data-password-rules>
            <li data-rule="length"><i class="fa-solid fa-circle" aria-hidden="true"></i> At least 8 characters</li>
            <li data-rule="uppercase"><i class="fa-solid fa-circle" aria-hidden="true"></i> One uppercase letter</li>
            <li data-rule="number"><i class="fa-solid fa-circle" aria-hidden="true"></i> One number</li>
            <li data-rule="symbol"><i class="fa-solid fa-circle" aria-hidden="true"></i> One symbol (! @ # $ % &amp; * ? ,)</li>
        </ul>
    </div>
    <?php
    $passwordRulesHtml = (string) ob_get_clean();
}
?>

<main class="akd-content">
    <?php require __DIR__ . '/partials/page-header.php'; ?>

    <div class="akd-admin-profile" id="adminProfile"
        data-profile-config='<?= htmlspecialchars(json_encode([
            'isGoogle' => $isGoogle,
            'fullname' => $fullname,
            'username' => $username,
            'avatarUrl' => $avatarUrl ?? '',
        ], JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>'
    >

        <!-- Identity -->
        <section class="akd-admin-identity" aria-labelledby="adminIdentityName">
            <div class="akd-admin-identity__avatar-wrap" data-avatar-wrap>
                <span class="akd-admin-avatar akd-admin-avatar--xl" data-user-avatar-container>
                    <a class="akd-admin-avatar__link" data-user-avatar-link
                        href="<?= htmlspecialchars($avatarUrl ?? '') ?>" data-fancybox
                        aria-label="View profile photo"<?= $avatarUrl ? '' : ' style="display:none"' ?>
                    >
                        <img data-user-avatar class="akd-admin-avatar__img" alt=""
                            <?= $avatarUrl ? 'src="' . htmlspecialchars($avatarUrl) . '"' : 'style="display:none"' ?>
                        >
                    </a>
                    <span data-user-avatar-initials class="akd-admin-avatar__initials"
                        style="<?= $avatarUrl ? 'display:none;' : 'display:flex;' ?> background-color: <?= htmlspecialchars($avatarColor) ?>;"
                    ><?= htmlspecialchars($userInitials) ?></span>
                </span>

                <span class="akd-admin-identity__avatar-busy" aria-hidden="true">
                    <span class="spinner"></span>
                </span>
            </div>

            <div class="akd-admin-identity__body">
                <div class="akd-admin-identity__title-row">
                    <h2 class="akd-admin-identity__name" id="adminIdentityName" data-user-fullname><?= htmlspecialchars($fullname) ?></h2>
                    <span class="akd-admin-badge akd-admin-badge--<?= htmlspecialchars($roleClass) ?>"><?= htmlspecialchars($roleLabel) ?></span>
                </div>

                <p class="akd-admin-identity__handle" data-user-username>@<?= htmlspecialchars($username) ?></p>

                <p class="akd-admin-identity__email">
                    <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($email) ?></span>
                    <?php if ($isVerified): ?>
                        <span class="akd-admin-identity__verified">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified
                        </span>
                    <?php endif; ?>
                </p>

                <ul class="akd-admin-identity__meta">
                    <li>
                        <?php if ($isGoogle): ?>
                            <i class="fa-brands fa-google" aria-hidden="true"></i>
                            <span>Signed in with Google</span>
                        <?php else: ?>
                            <i class="fa-solid fa-key" aria-hidden="true"></i>
                            <span>Signed in with password</span>
                        <?php endif; ?>
                    </li>
                    <?php if ($memberSince !== null): ?>
                        <li>
                            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                            <span>Member since <?= htmlspecialchars($memberSince) ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($lastLogin !== null): ?>
                        <li>
                            <i class="fa-regular fa-clock" aria-hidden="true"></i>
                            <span>Last sign-in <time datetime="<?= $lastLogin->format('Y-m-d\TH:i:s\Z') ?>" data-local-time><?= htmlspecialchars($lastLogin->format('j M Y, H:i')) ?> UTC</time></span>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="akd-admin-identity__actions">
                    <input type="file" id="adminAvatarInput" accept="image/png,image/jpeg" hidden data-avatar-input aria-label="Choose a profile photo">
                    <button type="button" class="akd-admin-btn" data-avatar-change>
                        <i class="fa-solid fa-camera" aria-hidden="true"></i> Change photo
                    </button>
                    <button type="button" class="akd-admin-btn akd-admin-btn--ghost akd-admin-btn--danger" data-avatar-remove<?= $avatarUrl ? '' : ' hidden' ?>>
                        <i class="fa-regular fa-trash-can" aria-hidden="true"></i> Remove
                    </button>
                    <p class="akd-admin-identity__hint" id="adminAvatarHint">PNG or JPG, up to 2MB.</p>
                    <p class="akd-admin-identity__error" data-avatar-error role="alert"></p>
                </div>
            </div>
        </section>

        <!-- Personal information -->
        <section class="akd-admin-section" aria-labelledby="adminInfoTitle">
            <div class="akd-admin-section__intro">
                <h2 class="akd-admin-section__title" id="adminInfoTitle">Personal information</h2>
                <p class="akd-admin-section__desc">Your name and username are shown wherever your account appears.</p>
            </div>

            <form class="akd-admin-form" id="adminProfileForm" novalidate>
                <div class="akd-admin-form__grid">
                    <div class="akd-admin-field" data-field="fullname">
                        <label class="akd-admin-field__label" for="adminFullname">Full name</label>
                        <div class="akd-admin-field__control">
                            <input type="text" id="adminFullname" name="fullname"
                                class="akd-admin-field__input"
                                value="<?= htmlspecialchars($fullname) ?>"
                                maxlength="100" autocomplete="name"
                                aria-describedby="adminFullname-error"
                            >
                        </div>
                        <p class="akd-admin-field__error" id="adminFullname-error" data-field-error aria-live="polite"></p>
                    </div>

                    <div class="akd-admin-field" data-field="username">
                        <label class="akd-admin-field__label" for="adminUsername">Username</label>
                        <div class="akd-admin-field__control">
                            <span class="akd-admin-field__affix" aria-hidden="true">@</span>
                            <input type="text" id="adminUsername" name="username"
                                class="akd-admin-field__input akd-admin-field__input--affix"
                                value="<?= htmlspecialchars($username) ?>"
                                maxlength="20" autocomplete="off" autocapitalize="none" spellcheck="false"
                                aria-describedby="adminUsername-hint adminUsername-error"
                            >
                        </div>
                        <p class="akd-admin-field__hint" id="adminUsername-hint">3 to 20 characters: letters, numbers and underscores.</p>
                        <p class="akd-admin-field__error" id="adminUsername-error" data-field-error aria-live="polite"></p>
                    </div>

                    <div class="akd-admin-field akd-admin-field--wide">
                        <label class="akd-admin-field__label" for="adminEmail">Email address</label>
                        <div class="akd-admin-field__control">
                            <input type="email" id="adminEmail" class="akd-admin-field__input"
                                value="<?= htmlspecialchars($email) ?>" readonly
                                aria-describedby="adminEmail-hint"
                            >
                            <i class="fa-solid fa-lock akd-admin-field__lock" aria-hidden="true"></i>
                        </div>
                        <p class="akd-admin-field__hint" id="adminEmail-hint">Feature coming soon.</p>
                    </div>
                </div>

                <div class="akd-admin-form__actions">
                    <button type="submit" class="akd-admin-btn akd-admin-btn--primary" data-profile-save disabled>Save changes</button>
                    <button type="button" class="akd-admin-btn akd-admin-btn--ghost" data-profile-reset hidden>Discard</button>
                    <p class="akd-admin-form__status" data-profile-status aria-live="polite"></p>
                </div>
            </form>
        </section>

        <!-- Security -->
        <section class="akd-admin-section" aria-labelledby="adminSecurityTitle">
            <div class="akd-admin-section__intro">
                <h2 class="akd-admin-section__title" id="adminSecurityTitle">Security</h2>
                <p class="akd-admin-section__desc">
                    <?php if ($isGoogle): ?>
                        How your account signs in.
                    <?php else: ?>
                        Choose a strong password you don't use anywhere else. Changing it signs you out of your other devices.
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($isGoogle): ?>
                <div class="akd-admin-note">
                    <i class="fa-brands fa-google" aria-hidden="true"></i>
                    <p>You sign in with Google, so there is no password to manage here.</p>
                </div>
            <?php else: ?>
                <form class="akd-admin-form" id="adminPasswordForm" novalidate>
                    <?php /* Lets password managers associate the new password with this account. */ ?>
                    <input type="text" value="<?= htmlspecialchars($username) ?>" autocomplete="username"
                        class="visually-hidden" tabindex="-1" aria-hidden="true" readonly
                    >

                    <?php akd_admin_render_password_field('currentPassword', 'Current password', 'adminCurrentPassword', 'current-password', 'Needed to confirm it\'s you.'); ?>
                    <?php akd_admin_render_password_field('newPassword', 'New password', 'adminNewPassword', 'new-password', '', $passwordRulesHtml, 'adminPasswordRules'); ?>
                    <?php akd_admin_render_password_field('confirmPassword', 'Confirm new password', 'adminConfirmPassword', 'new-password'); ?>

                    <div class="akd-admin-form__actions">
                        <button type="submit" class="akd-admin-btn akd-admin-btn--primary" data-password-save disabled>Update password</button>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>