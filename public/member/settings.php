<?php
$page_title       = "Settings";
$page_description = "Manage your account settings and preferences.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Settings', 'url' => null],
];

require_once __DIR__ . '/includes/header.php';
?>

<main class="akd-content">
    <div class="akd-settings">
        <!-- Security -->
        <section class="akd-settings__section" aria-labelledby="settings-security-heading">
            <h2 class="akd-settings__section-title" id="settings-security-heading">Security</h2>
            <div class="akd-settings-group">
                <div class="akd-settings-row" data-settings-row="2fa">
                    <div class="akd-settings-row__icon akd-settings-row__icon--security" aria-hidden="true">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="akd-settings-row__content">
                        <div class="akd-settings-row__top">
                            <span class="akd-settings-row__label">Two-factor authentication</span>
                            <span class="akd-settings-row__status" data-2fa-status data-state="off">
                                <span class="akd-settings-row__status-dot" aria-hidden="true"></span>
                                <span data-2fa-status-text>Not enabled</span>
                            </span>
                        </div>
                        <p class="akd-settings-row__desc" data-2fa-desc>Add an extra layer of security when signing in.</p>
                    </div>
                    <button type="button" class="akd-btn akd-btn--secondary akd-settings-row__action" data-2fa-trigger>
                        <span data-2fa-action-label>Set up</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- Preferences -->
        <section class="akd-settings__section" aria-labelledby="settings-preferences-heading">
            <h2 class="akd-settings__section-title" id="settings-preferences-heading">Preferences</h2>
            <div class="akd-settings-group">
                <div class="akd-settings-row" data-settings-row="language">
                    <div class="akd-settings-row__icon" aria-hidden="true"><i class="fa-solid fa-language"></i></div>
                    <div class="akd-settings-row__content">
                        <div class="akd-settings-row__top">
                            <span class="akd-settings-row__label">Language</span>
                            <span class="akd-settings-row__value" data-language-status-text>English</span>
                        </div>
                        <p class="akd-settings-row__desc">Choose the language used throughout Anime Nigeria.</p>
                    </div>
                    <button type="button" class="akd-btn akd-btn--secondary akd-settings-row__action" data-language-trigger>Change</button>
                </div>

                <div class="akd-settings-row" data-settings-row="timezone">
                    <div class="akd-settings-row__icon" aria-hidden="true"><i class="fa-solid fa-globe"></i></div>
                    <div class="akd-settings-row__content">
                        <div class="akd-settings-row__top">
                            <span class="akd-settings-row__label">Time zone</span>
                            <span class="akd-settings-row__value" data-timezone-status-text>
                                Africa/Lagos (UTC+01:00)
                            </span>
                        </div>
                        <p class="akd-settings-row__desc">Used for event times, reminders, and other scheduled activities.</p>
                    </div>
                    <button type="button" class="akd-btn akd-btn--secondary akd-settings-row__action" data-timezone-trigger>Change</button>
                </div>

                <div class="akd-settings-row" data-settings-row="notifications">
                    <div class="akd-settings-row__icon" aria-hidden="true"><i class="fa-solid fa-bell"></i></div>
                    <div class="akd-settings-row__content">
                        <span class="akd-settings-row__label">Notification preferences</span>
                        <p class="akd-settings-row__desc" data-notifications-summary>All notifications on.</p>
                    </div>
                    <button type="button" class="akd-btn akd-btn--secondary akd-settings-row__action" data-notifications-trigger>Manage</button>
                </div>

                <div class="akd-settings-row" data-settings-row="email">
                    <div class="akd-settings-row__icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></div>
                    <div class="akd-settings-row__content">
                        <span class="akd-settings-row__label">Email preferences</span>
                        <p class="akd-settings-row__desc" data-email-summary>All optional emails on. Account emails always on.</p>
                    </div>
                    <button type="button" class="akd-btn akd-btn--secondary akd-settings-row__action" data-email-trigger>Manage</button>
                </div>
            </div>
        </section>

        <!-- Account & Data -->
        <section class="akd-settings__section" aria-labelledby="settings-data-heading">
            <h2 class="akd-settings__section-title" id="settings-data-heading">Account &amp; Data</h2>
            <div class="akd-settings-group">
                <div class="akd-settings-row" data-settings-row="download">
                    <div class="akd-settings-row__icon" aria-hidden="true"><i class="fa-solid fa-cloud-arrow-down"></i></div>
                    <div class="akd-settings-row__content">
                        <span class="akd-settings-row__label">Download my data</span>
                        <p class="akd-settings-row__desc" data-download-desc>Get a copy of your profile, achievements, and activity.</p>
                    </div>
                    
                    <button type="button" class="akd-btn akd-btn--secondary akd-settings-row__action" data-download-trigger>
                        <span data-download-label>Download</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- Danger Zone -->
        <section class="akd-settings__section akd-settings__section--danger" aria-labelledby="settings-danger-heading">
            <h2 class="akd-settings__section-title akd-settings__section-title--danger" id="settings-danger-heading">Danger Zone</h2>
            <div class="akd-settings-group akd-settings-group--danger">
                <div class="akd-settings-row" data-settings-row="delete">
                    <div class="akd-settings-row__icon akd-settings-row__icon--danger" aria-hidden="true">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="akd-settings-row__content">
                        <span class="akd-settings-row__label">Delete account</span>
                        <p class="akd-settings-row__desc">Permanently remove your account and all associated data. This can't be undone.</p>
                    </div>
                    <button type="button" class="akd-btn akd-btn--danger akd-settings-row__action" data-delete-trigger>Delete account</button>
                </div>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>