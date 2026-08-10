// resources/js/member/settings.js
// Settings page — UI-only mockup. No persistence, no network calls.
// Reuses the existing modal singleton and confirm dialog singleton.

import { useModal } from '../modules/modal.js';
import { useConfirmDialog } from '../modules/confirm-dialog.js';

export function initSettingsPage() {
    const root = document.querySelector('.akd-settings');
    if (!root) return;

    const modal = useModal();
    const confirmDialog = useConfirmDialog();

    initToggleSwitches(root);
    init2fa(root, modal, confirmDialog);
    initEmailPreferences(root, modal);
    initDownloadData(root);
    initDeleteAccount(root, modal, confirmDialog);
}

// ------------------------------------------------------------
// Standalone preference switches (achievement motif, quiz reminders)
// ------------------------------------------------------------
function initToggleSwitches(root) {
    root.querySelectorAll('[data-switch]').forEach(bindSwitch);
}

function bindSwitch(el) {
    el.addEventListener('click', () => {
        const checked = el.getAttribute('aria-checked') === 'true';
        el.setAttribute('aria-checked', String(!checked));
    });
}

function focusFirst(container) {
    const target = container.querySelector('input, button, [tabindex]');
    if (target) target.focus();
}

// ------------------------------------------------------------
// Two-factor authentication
// ------------------------------------------------------------
function init2fa(root, modal, confirmDialog) {
    const row = root.querySelector('[data-settings-row="2fa"]');
    if (!row) return;

    const trigger = row.querySelector('[data-2fa-trigger]');
    const statusEl = row.querySelector('[data-2fa-status]');
    const statusText = row.querySelector('[data-2fa-status-text]');
    const actionLabel = row.querySelector('[data-2fa-action-label]');

    let enabled = false;

    trigger.addEventListener('click', () => {
        if (enabled) {
            confirmDialog
                .ask({
                    title: 'Disable two-factor authentication?',
                    message: "Your account will no longer ask for a verification code at sign-in.",
                    confirmLabel: 'Disable',
                    cancelLabel: 'Cancel',
                    destructive: true,
                })
                .then((confirmed) => {
                    if (confirmed) setEnabled(false);
                });
            return;
        }

        openSetupFlow();
    });

    function setEnabled(value) {
        enabled = value;
        statusEl.dataset.state = value ? 'on' : 'off';
        statusText.textContent = value ? 'Enabled' : 'Not enabled';
        actionLabel.textContent = value ? 'Disable' : 'Set up';
    }

    function openSetupFlow() {
        const totalSteps = 3;
        let backupCodes = [];

        modal.open({
            title: 'Two-factor authentication',
            subtitle: 'Step 1 of 3',
            content: renderPanel(1),
            footer: renderFooter(1),
            className: 'akd-modal--2fa',
        });
        wireStepEvents(1);

        function renderPanel(n) {
            const wrap = document.createElement('div');
            wrap.className = 'akd-2fa';

            const meta = document.createElement('div');
            meta.className = 'akd-2fa__meta';
            meta.innerHTML = `
                <div class="akd-2fa__progress" aria-hidden="true">
                    <span class="akd-2fa__progress-bar" style="width:${(n / totalSteps) * 100}%"></span>
                </div>
            `;
            wrap.appendChild(meta);

            const panel = document.createElement('div');

            if (n === 1) {
                panel.innerHTML = `
                    <div class="akd-2fa__intro-icon" aria-hidden="true"><i class="fa-solid fa-shield-halved"></i></div>
                    <h3 class="akd-2fa__heading">Protect your account</h3>
                    <p class="akd-2fa__text">Two-factor authentication adds a second step at sign-in, so your account stays protected even if your password is ever compromised.</p>
                    <ul class="akd-2fa__checklist">
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> An authenticator app on your phone (Google Authenticator, Authy, etc.)</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> About two minutes to finish setup</li>
                    </ul>
                `;
            }

            if (n === 2) {
                panel.innerHTML = `
                    <h3 class="akd-2fa__heading">Scan the code</h3>
                    <p class="akd-2fa__text">Open your authenticator app and scan this code, or enter the setup key manually.</p>
                    <div class="akd-2fa__qr" aria-hidden="true"><i class="fa-solid fa-qrcode"></i></div>
                    <div class="akd-2fa__key">
                        <span class="akd-2fa__key-label">Setup key</span>
                        <code class="akd-2fa__key-value">AKD3-F8K2-9XQ1-MZ4T</code>
                    </div>
                    <div class="akd-2fa__field">
                        <label class="akd-2fa__label" for="akd2faCode">Enter the 6-digit code</label>
                        <input type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                            id="akd2faCode" class="akd-code-input" placeholder="000000" data-2fa-code-input>
                        <p class="akd-2fa__error" role="alert" data-2fa-code-error hidden>Enter the 6-digit code from your app.</p>
                    </div>
                `;
            }

            if (n === 3) {
                backupCodes = backupCodes.length ? backupCodes : generateBackupCodes();
                panel.innerHTML = `
                    <h3 class="akd-2fa__heading">Save your backup codes</h3>
                    <p class="akd-2fa__text">Keep these somewhere safe. Each code can be used once if you ever lose access to your authenticator app.</p>
                    <div class="akd-2fa__codes">
                        ${backupCodes.map((c) => `<code class="akd-2fa__code">${c}</code>`).join('')}
                    </div>
                    <label class="akd-2fa__ack">
                        <input type="checkbox" data-2fa-ack>
                        <span>I've saved my backup codes in a safe place</span>
                    </label>
                `;
            }

            wrap.appendChild(panel);
            return wrap;
        }

        function renderFooter(n) {
            const fragment = document.createDocumentFragment();

            const secondary = document.createElement('button');
            secondary.type = 'button';
            secondary.className = 'akd-btn akd-btn--secondary';
            secondary.textContent = n > 1 ? 'Back' : 'Cancel';
            secondary.addEventListener('click', () => {
                if (n > 1) goToStep(n - 1);
                else modal.close();
            });

            const primary = document.createElement('button');
            primary.type = 'button';
            primary.className = 'akd-btn akd-btn--primary';
            primary.textContent = n === 3 ? 'Finish setup' : n === 2 ? 'Verify & continue' : 'Continue';
            if (n === 3) primary.disabled = true;
            primary.addEventListener('click', () => handleNext(n));

            fragment.append(secondary, primary);
            return fragment;
        }

        function goToStep(n) {
            const body = modal.getBody();
            const footer = modal.getFooter();
            const subtitleEl = modal.getModal().querySelector('.akd-modal__subtitle');

            body.classList.add('is-leaving');

            window.setTimeout(() => {
                body.replaceChildren(renderPanel(n));
                footer.replaceChildren(renderFooter(n));
                if (subtitleEl) subtitleEl.textContent = `Step ${n} of ${totalSteps}`;

                body.classList.remove('is-leaving');
                body.classList.add('is-entering');
                requestAnimationFrame(() => body.classList.remove('is-entering'));

                wireStepEvents(n);
                focusFirst(body);
            }, 160);
        }

        function wireStepEvents(n) {
            const body = modal.getBody();

            if (n === 2) {
                const input = body.querySelector('[data-2fa-code-input]');
                input?.addEventListener('input', () => {
                    input.setAttribute('aria-invalid', 'false');
                    body.querySelector('[data-2fa-code-error]').hidden = true;
                });
            }

            if (n === 3) {
                const ack = body.querySelector('[data-2fa-ack]');
                const finishBtn = modal.getFooter().querySelector('.akd-btn--primary');
                if (finishBtn) finishBtn.disabled = true;
                ack?.addEventListener('change', () => {
                    if (finishBtn) finishBtn.disabled = !ack.checked;
                });
            }
        }

        function handleNext(n) {
            if (n === 1) {
                goToStep(2);
                return;
            }

            if (n === 2) {
                const body = modal.getBody();
                const input = body.querySelector('[data-2fa-code-input]');
                const error = body.querySelector('[data-2fa-code-error]');
                const valid = /^\d{6}$/.test(input.value.trim());

                input.setAttribute('aria-invalid', String(!valid));
                error.hidden = valid;
                if (!valid) { input.focus(); return; }

                goToStep(3);
                return;
            }

            if (n === 3) {
                modal.close();
                setEnabled(true);
            }
        }
    }

    function generateBackupCodes(count = 8) {
        const part = () => Math.random().toString(36).slice(2, 6).toUpperCase();
        return Array.from({ length: count }, () => `${part()}-${part()}`);
    }
}

// ------------------------------------------------------------
// Email preferences
// ------------------------------------------------------------
function initEmailPreferences(root, modal) {
    const row = root.querySelector('[data-settings-row="email"]');
    if (!row) return;

    const trigger = row.querySelector('[data-email-trigger]');
    const summary = row.querySelector('[data-email-summary]');
    const prefs = { achievements: true, quiz: true };

    trigger.addEventListener('click', () => {
        modal.open({
            title: 'Email preferences',
            subtitle: "Choose which emails you'd like to receive",
            content: buildContent(),
            footer: buildFooter(),
            className: 'akd-modal--email',
        });
    });

    function buildContent() {
        const wrap = document.createElement('div');
        wrap.className = 'akd-email-prefs';
        wrap.innerHTML = `
            <p class="akd-email-prefs__intro">Account and security emails can't be turned off.</p>

            <div class="akd-email-prefs__row">
                <div>
                    <span class="akd-email-prefs__label" id="emailAchievements-label">Achievement emails</span>
                    <p class="akd-email-prefs__desc">When you unlock an achievement or move up the leaderboard.</p>
                </div>
                <button type="button" class="akd-switch" role="switch" aria-checked="${prefs.achievements}"
                    aria-labelledby="emailAchievements-label" data-email-switch="achievements">
                    <span class="akd-switch__track"><span class="akd-switch__thumb"></span></span>
                </button>
            </div>

            <div class="akd-email-prefs__row">
                <div>
                    <span class="akd-email-prefs__label" id="emailQuiz-label">Quiz emails</span>
                    <p class="akd-email-prefs__desc">Reminders and results for quizzes you've joined.</p>
                </div>
                <button type="button" class="akd-switch" role="switch" aria-checked="${prefs.quiz}"
                    aria-labelledby="emailQuiz-label" data-email-switch="quiz">
                    <span class="akd-switch__track"><span class="akd-switch__thumb"></span></span>
                </button>
            </div>

            <div class="akd-email-prefs__row akd-email-prefs__row--locked">
                <div>
                    <span class="akd-email-prefs__label">Account &amp; security emails</span>
                    <p class="akd-email-prefs__desc">Password resets, sign-in alerts, and important account notices.</p>
                </div>
                <span class="akd-email-prefs__locked-badge"><i class="fa-solid fa-lock" aria-hidden="true"></i> Always on</span>
            </div>
        `;

        wrap.querySelectorAll('[data-email-switch]').forEach((el) => {
            el.addEventListener('click', () => {
                const key = el.dataset.emailSwitch;
                prefs[key] = !prefs[key];
                el.setAttribute('aria-checked', String(prefs[key]));
            });
        });

        return wrap;
    }

    function buildFooter() {
        const done = document.createElement('button');
        done.type = 'button';
        done.className = 'akd-btn akd-btn--primary';
        done.textContent = 'Done';
        done.addEventListener('click', () => {
            modal.close();
            updateSummary();
        });
        return done;
    }

    function updateSummary() {
        const on = [];
        if (prefs.achievements) on.push('Achievement');
        if (prefs.quiz) on.push('Quiz');
        summary.textContent = on.length
            ? `${on.join(' & ')} emails on · Account emails always on`
            : 'Achievement and quiz emails off · Account emails always on';
    }
}

// ------------------------------------------------------------
// Download my data
// ------------------------------------------------------------
function initDownloadData(root) {
    const row = root.querySelector('[data-settings-row="download"]');
    if (!row) return;

    const trigger = row.querySelector('[data-download-trigger]');
    const label = row.querySelector('[data-download-label]');
    const desc = row.querySelector('[data-download-desc]');
    let busy = false;

    trigger.addEventListener('click', () => {
        if (busy) return;
        busy = true;
        trigger.disabled = true;
        label.textContent = 'Preparing…';

        window.setTimeout(() => {
            label.textContent = 'Request received';
            desc.textContent = "We'll prepare your data and notify you when it's ready. (Preview only — nothing was generated.)";
        }, 900);
    });
}

// ------------------------------------------------------------
// Delete account — confirm dialog -> password re-entry modal -> mock success
// ------------------------------------------------------------
function initDeleteAccount(root, modal, confirmDialog) {
    const trigger = root.querySelector('[data-delete-trigger]');
    if (!trigger) return;

    trigger.addEventListener('click', () => {
        confirmDialog
            .ask({
                title: 'Delete your account?',
                message: "This will permanently remove your account, achievements, and activity. This can't be undone.",
                confirmLabel: 'Continue',
                cancelLabel: 'Cancel',
                destructive: true,
            })
            .then((confirmed) => {
                if (confirmed) openPasswordStep();
            });
    });

    function openPasswordStep() {
        modal.open({
            title: 'Confirm your password',
            subtitle: 'For your security, re-enter your password to continue.',
            content: buildPasswordView(),
            footer: buildPasswordFooter(),
            className: 'akd-modal--delete',
        });
        wirePasswordView();
    }

    function buildPasswordView() {
        const wrap = document.createElement('div');
        wrap.className = 'akd-delete-confirm';
        wrap.innerHTML = `
            <p class="akd-delete-confirm__text">Enter your current password to confirm you'd like to permanently delete your account.</p>
            <div class="akd-delete-confirm__field">
                <label class="akd-2fa__label" for="akdDeletePassword">Current password</label>
                <div class="akd-password-field">
                    <input type="password" id="akdDeletePassword" class="akd-password-field__input"
                        autocomplete="current-password" data-delete-password-input>
                    <button type="button" class="akd-password-field__toggle" data-delete-password-toggle
                        aria-label="Show password" aria-pressed="false">
                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
                <p class="akd-delete-confirm__error" role="alert" data-delete-password-error hidden>
                    Enter your password to continue.
                </p>
            </div>
        `;
        return wrap;
    }

    function buildPasswordFooter() {
        const fragment = document.createDocumentFragment();

        const cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'akd-btn akd-btn--secondary';
        cancel.textContent = 'Cancel';
        cancel.addEventListener('click', () => modal.close());

        const confirmBtn = document.createElement('button');
        confirmBtn.type = 'button';
        confirmBtn.className = 'akd-btn akd-btn--danger';
        confirmBtn.textContent = 'Confirm deletion';
        confirmBtn.addEventListener('click', handleConfirmDeletion);

        fragment.append(cancel, confirmBtn);
        return fragment;
    }

    function wirePasswordView() {
        const body = modal.getBody();
        const toggle = body.querySelector('[data-delete-password-toggle]');
        const input = body.querySelector('[data-delete-password-input]');

        toggle.addEventListener('click', () => {
            const shown = input.type === 'text';
            input.type = shown ? 'password' : 'text';
            toggle.setAttribute('aria-pressed', String(!shown));
            toggle.setAttribute('aria-label', shown ? 'Show password' : 'Hide password');
            toggle.innerHTML = shown
                ? '<i class="fa-solid fa-eye" aria-hidden="true"></i>'
                : '<i class="fa-solid fa-eye-slash" aria-hidden="true"></i>';
        });

        input.addEventListener('input', () => {
            input.setAttribute('aria-invalid', 'false');
            body.querySelector('[data-delete-password-error]').hidden = true;
        });
    }

    function handleConfirmDeletion() {
        const body = modal.getBody();
        const input = body.querySelector('[data-delete-password-input]');
        const error = body.querySelector('[data-delete-password-error]');

        if (!input.value.trim()) {
            input.setAttribute('aria-invalid', 'true');
            error.hidden = false;
            input.focus();
            return;
        }

        showSuccessView();
    }

    function showSuccessView() {
        const body = modal.getBody();
        const footer = modal.getFooter();
        const modalEl = modal.getModal();
        const titleEl = modalEl.querySelector('.akd-modal__title');
        const subtitleEl = modalEl.querySelector('.akd-modal__subtitle');

        body.classList.add('is-leaving');

        window.setTimeout(() => {
            if (titleEl) titleEl.textContent = 'Deletion scheduled';
            if (subtitleEl) {
                subtitleEl.hidden = true;
                subtitleEl.textContent = '';
                modalEl.removeAttribute('aria-describedby');
            }

            body.replaceChildren(buildSuccessView());
            footer.replaceChildren(buildSuccessFooter());

            body.classList.remove('is-leaving');
            body.classList.add('is-entering');
            requestAnimationFrame(() => body.classList.remove('is-entering'));

            focusFirst(body.parentElement.querySelector('.akd-modal__footer') || body);
        }, 160);
    }

    function buildSuccessView() {
        const wrap = document.createElement('div');
        wrap.className = 'akd-delete-confirm akd-delete-confirm--success';
        wrap.innerHTML = `
            <div class="akd-delete-confirm__icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></div>
            <p class="akd-delete-confirm__text">This is a preview — nothing has been deleted. Once this is connected, your account and data would be permanently removed.</p>
        `;
        return wrap;
    }

    function buildSuccessFooter() {
        const done = document.createElement('button');
        done.type = 'button';
        done.className = 'akd-btn akd-btn--primary';
        done.textContent = 'Done';
        done.addEventListener('click', () => modal.close());
        return done;
    }
}