import { useModal } from '../modules/modal.js';
import { useConfirmDialog } from '../modules/confirm-dialog.js';
import { api, handleApiError } from '../modules/api.js';
import { error as errorToast } from '../modules/toast.js';
import { startCooldown } from '../modules/cooldown.js';
import { setLoading, clearLoading } from '../modules/loading-state.js';

const SETTINGS_ENDPOINT = '/member/api/settings';

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
// Shared: password confirmation modal.
// Used by both "delete account" and "disable 2FA": both are
// security-sensitive actions that require re-entering the password,
// so they share one implementation inside the existing modal singleton.
// ------------------------------------------------------------
function openPasswordConfirmModal(modal, {
    className = 'akd-modal--delete',
    modalTitle = 'Confirm your password',
    modalSubtitle = 'For your security, re-enter your password to continue.',
    description,
    confirmLabel = 'Confirm',
    successTitle = 'Done',
    successText,
    onConfirmed,
}) {
    modal.open({
        title: modalTitle,
        subtitle: modalSubtitle,
        content: buildPasswordView(),
        footer: buildPasswordFooter(),
        className,
    });

    wirePasswordView();

    function buildPasswordView() {
        const wrap = document.createElement('div');
        wrap.className = 'akd-delete-confirm';

        wrap.innerHTML = `
            <p class="akd-delete-confirm__text">${description}</p>

            <div class="akd-delete-confirm__field">
                <label class="akd-2fa__label" for="akdPasswordConfirmInput">
                    Current password
                </label>

                <div class="akd-password-field">
                    <input type="password" id="akdPasswordConfirmInput"
                        class="akd-password-field__input" autocomplete="current-password"
                        data-password-input
                    >

                    <button type="button" class="akd-password-field__toggle" data-password-toggle
                        aria-label="Show password" aria-pressed="false"
                    >
                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                    </button>
                </div>

                <p class="akd-delete-confirm__error" role="alert" data-password-error hidden>
                    Enter your password to continue.
                </p>

                <div class="akd-delete-confirm__cooldown" data-password-cooldown role="status"
                    aria-live="polite" hidden
                >
                    <i class="fa-solid fa-clock" aria-hidden="true"></i>

                    <span>
                        Too many attempts. Try again in
                        <strong data-password-cooldown-time>00:00</strong>
                    </span>
                </div>
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
        confirmBtn.textContent = confirmLabel;
        confirmBtn.disabled = true; // stays disabled until the field has a value
        confirmBtn.addEventListener('click', handleConfirm);
        fragment.append(cancel, confirmBtn);
        return fragment;
    }

    function wirePasswordView() {
        const body = modal.getBody();
        const toggle = body.querySelector('[data-password-toggle]');
        const input = body.querySelector('[data-password-input]');
        const confirmBtn = modal.getFooter().querySelector('.akd-btn--danger');

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
            body.querySelector('[data-password-error]').hidden = true;

            if (input.dataset.cooldown === 'true') {
                return;
            }

            confirmBtn.disabled = !input.value.trim();
        });
    }

    async function handleConfirm() {
        const body = modal.getBody();
        const input = body.querySelector('[data-password-input]');
        const error = body.querySelector('[data-password-error]');
        const cooldown = body.querySelector('[data-password-cooldown]');
        const cooldownTime = body.querySelector('[data-password-cooldown-time]');
        const confirmBtn = modal.getFooter().querySelector('.akd-btn--danger');

        if (!input.value.trim()) {
            input.setAttribute('aria-invalid', 'true');
            error.textContent = 'Enter your password to continue.';
            error.hidden = false;
            input.focus();
            return;
        }

        // Delete-account doesn't pass onConfirmed.
        if (!onConfirmed) {
            showSuccessView();
            return;
        }

        setLoading(confirmBtn, 'Verifying…');

        try {
            await onConfirmed(input.value);
            showSuccessView();
        } catch (err) {

            // --------------------------------------------------------
            // Rate limited
            // --------------------------------------------------------
            if (err.rateLimited && err.retryAfter > 0) {
                error.hidden = true;
                input.setAttribute('aria-invalid', 'false');

                startCooldown(body, err.retryAfter,
                    (time) => {
                        cooldown.hidden = false;
                        cooldownTime.textContent = time;
                    },
                    () => {
                        cooldown.hidden = true;
                        cooldownTime.textContent = '00:00';
                        input.value = '';
                        input.setAttribute('aria-invalid', 'false');
                        error.hidden = true;

                        // Input is enabled again by startCooldown().
                        // Confirm must remain disabled because the input is empty.
                        confirmBtn.disabled = true;
                        input.focus();
                    },
                    [confirmBtn]
                );

                return;
            }

            error.textContent = err.message || 'Something went wrong. Please try again.';
            error.hidden = false;
            input.setAttribute('aria-invalid', 'true');
            input.focus();
        } finally {
            clearLoading(confirmBtn);
        }
    }

    function showSuccessView() {
        const body = modal.getBody();
        const footer = modal.getFooter();
        const modalEl = modal.getModal();
        const titleEl = modalEl.querySelector('.akd-modal__title');
        const subtitleEl = modalEl.querySelector('.akd-modal__subtitle');

        body.classList.add('is-leaving');

        window.setTimeout(() => {
            if (titleEl) titleEl.textContent = successTitle;

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
            focusFirst(footer);
        }, 160);
    }

    function buildSuccessView() {
        const wrap = document.createElement('div');
        wrap.className = 'akd-delete-confirm akd-delete-confirm--success';

        wrap.innerHTML = `
            <div class="akd-delete-confirm__icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></div>
            <p class="akd-delete-confirm__text">${successText}</p>
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

// ------------------------------------------------------------
// 6-digit OTP boxes (2FA verification step)
// ------------------------------------------------------------
function wireOtpBoxes(boxList) {
    const boxes = Array.from(boxList);

    boxes.forEach((box, i) => {
        box.addEventListener('input', () => {
            box.value = box.value.replace(/[^0-9]/g, '').slice(-1);
            box.setAttribute('aria-invalid', 'false');

            if (box.value && boxes[i + 1]) {
                boxes[i + 1].focus();
            }
        });

        box.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !box.value && boxes[i - 1]) {
                boxes[i - 1].focus();
            }
        });

        box.addEventListener('paste', (e) => {
            const text = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
            if (!text) return;
            e.preventDefault();

            text.slice(0, boxes.length).split('').forEach((digit, idx) => {
                if (boxes[idx]) boxes[idx].value = digit;
            });

            const next = boxes[Math.min(text.length, boxes.length - 1)];
            next?.focus();
        });
    });
}

function getOtpValue(boxList) {
    return Array.from(boxList).map((box) => box.value).join('');
}

function downloadBackupCodesFile(codes) {
    const content = [
        'Anime Nigeria — two-factor authentication recovery codes',
        'Generated ' + new Date().toLocaleString(), '',
        ...codes,
    ].join('\n');

    const blob = new Blob([content], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'anime-nigeria-backup-codes.txt';
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
}

async function copyToClipboard(text) {
    if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(text);
        return;
    }

    // Fallback for insecure contexts (e.g. plain http, not localhost) or
    // browsers without the Clipboard API.
    const helper = document.createElement('textarea');
    helper.value = text;
    helper.setAttribute('readonly', '');
    helper.style.position = 'fixed';
    helper.style.opacity = '0';
    document.body.appendChild(helper);
    helper.select();
    const ok = document.execCommand('copy');
    helper.remove();

    if (!ok) {
        throw new Error('Copy command was unsuccessful.');
    }
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
    const descEl = row.querySelector('[data-2fa-desc]');

    // Server-authoritative state. Never set independently of a response.
    let twoFactorState = { enabled: false, managed_externally: false };

    // Purely transient, current-interaction-only state:
    let setupData = null;     // { secret, provisioning_uri, qr_code, expires_at }
    let recoveryCodes = null; // plaintext codes — only ever held during the active setup modal
    trigger.disabled = true;
    statusText.textContent = 'Loading…';
    loadSettings();

    trigger.addEventListener('click', () => {
        if (twoFactorState.managed_externally) return;
        twoFactorState.enabled ? openManageView() : openSetupFlow();
    });

    // ---------------- Server sync ----------------
    async function loadSettings() {
        try {
            const response = await api(SETTINGS_ENDPOINT, { method: 'GET' });

            if (!response.success) {
                statusText.textContent = 'Unable to load';
                errorToast(response.message || 'Unable to load your security settings.');
                return;
            }

            applyTwoFactorState(response.settings['2fa']);
        } catch (err) {
            statusText.textContent = 'Unable to load';
            handleApiError(err, 'Unable to load your security settings.');
        }
    }

    async function postAction(action, fields = {}) {
        const formData = new FormData();
        formData.append('action', action);

        Object.entries(fields).forEach(([key, value]) => {
            formData.append(key, value);
        });

        return api(SETTINGS_ENDPOINT, { method: 'POST', body: formData });
    }

    // Renders whatever PHP says. Makes no security decisions of its own.
    function applyTwoFactorState(state) {
        twoFactorState = state;

        if (state.managed_externally) {
            statusEl.dataset.state = 'managed';
            statusEl.innerHTML = '<i class="fa-brands fa-google" aria-hidden="true"></i> <span>Managed by Google</span>';
            descEl.textContent = 'Two-factor authentication for Google-linked accounts is managed in your Google Account settings.';
            trigger.textContent = 'Managed by Google';
            trigger.disabled = true;
            trigger.setAttribute('aria-disabled', 'true');
            return;
        }

        statusEl.dataset.state = state.enabled ? 'on' : 'off';
        statusText.textContent = state.enabled ? 'Enabled' : 'Not enabled';
        actionLabel.textContent = state.enabled ? 'Manage' : 'Set up';

        descEl.textContent = state.enabled
            ? 'Two-factor authentication is enabled for your account.'
            : 'Add an extra layer of security when signing in.';

        trigger.disabled = false;
        trigger.removeAttribute('aria-disabled');
    }

    function formatSecret(secret) {
        return secret.replace(/(.{4})/g, '$1 ').trim().toUpperCase();
    }

    // ---------------- Setup flow (3 steps, unchanged shape) ----------------
    function openSetupFlow() {
        const totalSteps = 3;
        setupData = null;
        recoveryCodes = null;

        // Tracks which security state we're in — this is what beforeClose
        // branches on. Step 2 = unfinished, cancellable setup. Step 3 =
        // already-enabled 2FA; only the one-time code display is at stake.
        let currentStep = 1;

        // Blocks every close path while a request that changes step or
        // server state is in flight (prevents the "Escape mid-request"
        // race, and duplicate cancel-setup submissions).
        let isBusy = false;

        // Lets the deliberate "Finish setup" action skip the Step 3
        // leave-warning — it's not an abandonment, it's completion.
        let isFinishing = false;

        modal.open({
            title: 'Two-factor authentication',
            subtitle: 'Step 1 of 3',
            content: renderPanel(1),
            footer: renderFooter(1),
            className: 'akd-modal--2fa',
            beforeClose: handleBeforeClose,
        });
        wireStepEvents(1);

        // ---------------- Close interception (single choke point) ----------------
        async function handleBeforeClose() {
            if (isBusy) return false;
            if (isFinishing) return true;
            if (currentStep === 2) return requestCancelPendingSetup();
            if (currentStep === 3) return requestLeaveWithoutSavingCodes();
            return true; // Step 1: nothing exists server-side yet.
        }

        async function requestCancelPendingSetup() {
            const confirmed = await confirmDialog.ask({
                title: 'Cancel 2FA setup?',
                message: "Your current 2FA setup hasn't been completed. If you leave now, this setup will be discarded and you'll need to start again.",
                confirmLabel: 'Cancel setup',
                cancelLabel: 'Keep setting up',
                destructive: true,
            });

            if (!confirmed) return false; // stay on Step 2, do nothing
            isBusy = true;
            setStepInteractive(false);

            try {
                const response = await postAction('2fa.cancel-setup');

                if (!response.success) {
                    errorToast(response.message || 'Unable to cancel setup. Please try again.');
                    return false; // keep the modal open so they can retry
                }

                setupData = null;
                recoveryCodes = null;
                applyTwoFactorState(response.settings['2fa']);
                return true;
            } catch (err) {
                handleApiError(err, 'Unable to cancel setup. Please try again.');
                return false;
            } finally {
                isBusy = false;
                setStepInteractive(true);
            }
        }

        async function requestLeaveWithoutSavingCodes() {
            const confirmed = await confirmDialog.ask({
                title: 'Leave without saving your recovery codes?',
                message: 'These recovery codes are shown only once. If you leave now, you may not be able to access these codes again.',
                confirmLabel: 'Leave',
                cancelLabel: 'Stay',
                destructive: true,
            });

            if (!confirmed) return false; // stay on Step 3

            // Nothing to send to the server — 2FA is already enabled and
            // stays that way. Only the plaintext codes are discarded.
            recoveryCodes = null;
            setupData = null;
            return true;
        }

        function setStepInteractive(enabled) {
            const body = modal.getBody();
            const footer = modal.getFooter();
            const closeBtn = modal.getModal().querySelector('.akd-modal__close');
            body.querySelectorAll('input, button').forEach((el) => { el.disabled = !enabled; });
            footer.querySelectorAll('button').forEach((el) => { el.disabled = !enabled; });
            if (closeBtn) closeBtn.disabled = !enabled;
        }

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
                    <h3 class="akd-2fa__heading">Link your authenticator</h3>

                    <p class="akd-2fa__text" data-2fa-qr-instruction>Scan the QR code with your authenticator app.</p>
                    <div class="akd-2fa__qr" aria-hidden="true" data-2fa-qr>${setupData?.qr_code ?? ''}</div>

                    <p class="akd-2fa__text" data-2fa-key-instruction hidden>Enter this setup key manually in your authenticator app.</p>
                    <div class="akd-2fa__key" data-2fa-key hidden>
                        <code class="akd-2fa__key-value">${formatSecret(setupData?.secret ?? '')}</code>
                        <button type="button" class="akd-2fa__key-copy" data-2fa-copy-key aria-label="Copy setup key">
                            <i class="fa-solid fa-copy" aria-hidden="true"></i>
                        </button>
                    </div>

                    <button type="button" class="akd-2fa__link-toggle" data-2fa-key-toggle>Trouble scanning?</button>

                    <h3 class="akd-2fa__heading akd-2fa__heading--verify" id="akd2faVerifyHeading">Verify your authenticator</h3>
                    <p class="akd-2fa__text">Enter the 6-digit verification code generated by your authenticator app.</p>

                    <div class="akd-2fa__field">
                        <div class="akd-otp" role="group" aria-labelledby="akd2faVerifyHeading" data-otp>
                            ${Array.from({ length: 6 }, (_, i) => `
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                                    class="akd-otp__box" data-otp-box aria-label="Digit ${i + 1} of 6">
                            `).join('')}
                        </div>
                        <p class="akd-2fa__error" role="alert" data-2fa-code-error hidden></p>
                    </div>
                `;
            }

            if (n === 3) {
                panel.innerHTML = `
                    <h3 class="akd-2fa__heading">Save your backup codes</h3>
                    <p class="akd-2fa__text">Keep these somewhere safe. Each code can be used once if you ever lose access to your authenticator app.</p>
                    <div class="akd-2fa__codes">
                        ${(recoveryCodes ?? []).map((c) => `<code class="akd-2fa__code">${c}</code>`).join('')}
                    </div>
                    <div class="akd-2fa__download">
                        <button type="button" class="akd-btn akd-btn--secondary" data-2fa-download-codes>
                            <i class="fa-solid fa-download" aria-hidden="true"></i> Download backup codes
                        </button>
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

        // Secondary button no longer navigates backward at any step —
        // per the lifecycle rules, there's no path back to a prior step,
        // only "cancel the whole thing" (Step 2) or "leave" (Step 3).
        // Both route through modal.close() so beforeClose is the one
        // and only place that logic lives.
        function renderFooter(n) {
            const fragment = document.createDocumentFragment();
            const secondary = document.createElement('button');
            secondary.type = 'button';
            secondary.className = 'akd-btn akd-btn--secondary';
            secondary.textContent = n === 3 ? 'Leave' : 'Cancel';
            secondary.addEventListener('click', () => modal.close());
            const primary = document.createElement('button');
            primary.type = 'button';
            primary.className = 'akd-btn akd-btn--primary';
            primary.textContent = n === 3 ? 'Finish setup' : n === 2 ? 'Verify and continue' : 'Continue';
            if (n === 3) primary.disabled = true;
            primary.addEventListener('click', () => handleNext(n));
            fragment.append(secondary, primary);
            return fragment;
        }

        function goToStep(n) {
            currentStep = n;
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
                wireOtpBoxes(body.querySelectorAll('[data-otp-box]'));

                // ---- QR ↔ setup-key toggle: pure visibility swap, no re-render ----
                const qrToggle = body.querySelector('[data-2fa-key-toggle]');
                const qrBlock = body.querySelector('[data-2fa-qr]');
                const qrInstruction = body.querySelector('[data-2fa-qr-instruction]');
                const keyBlock = body.querySelector('[data-2fa-key]');
                const keyInstruction = body.querySelector('[data-2fa-key-instruction]');

                qrToggle?.addEventListener('click', () => {
                    const showingKey = !keyBlock.hidden;

                    qrBlock.hidden = !showingKey;
                    qrInstruction.hidden = !showingKey;
                    keyBlock.hidden = showingKey;
                    keyInstruction.hidden = showingKey;

                    qrToggle.textContent = showingKey ? 'Trouble scanning?' : 'Use QR code instead';
                });

                // ---- Copy button: always copies the raw, unformatted secret ----
                const copyBtn = body.querySelector('[data-2fa-copy-key]');
                const keyValueEl = body.querySelector('.akd-2fa__key-value');
                let copyResetTimer = null;

                copyBtn?.addEventListener('click', async () => {
                    try {
                        await copyToClipboard(setupData?.secret ?? '');
                        copyBtn.classList.add('is-copied');
                        copyBtn.setAttribute('aria-label', 'Copied');
                        copyBtn.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i>';

                        clearTimeout(copyResetTimer);
                        copyResetTimer = window.setTimeout(() => {
                            copyBtn.classList.remove('is-copied');
                            copyBtn.setAttribute('aria-label', 'Copy setup key');
                            copyBtn.innerHTML = '<i class="fa-solid fa-copy" aria-hidden="true"></i>';
                        }, 1500);
                    } catch {
                        errorToast('Unable to copy the setup key. Please select and copy it manually.');
                    }
                });

                // ---- Manual select + native Copy on the key element also grabs the raw secret ----
                keyValueEl?.addEventListener('copy', (e) => {
                    e.preventDefault();
                    e.clipboardData.setData('text/plain', setupData?.secret ?? '');
                });
            }

            if (n === 3) {
                const ack = body.querySelector('[data-2fa-ack]');
                const finishBtn = modal.getFooter().querySelector('.akd-btn--primary');
                if (finishBtn) finishBtn.disabled = true;

                ack?.addEventListener('change', () => {
                    if (finishBtn) finishBtn.disabled = !ack.checked;
                });

                body.querySelector('[data-2fa-download-codes]')?.addEventListener('click', () => {
                    downloadBackupCodesFile(recoveryCodes ?? []);
                });
            }
        }

        async function handleNext(n) {
            const footer = modal.getFooter();
            const primary = footer.querySelector('.akd-btn--primary');

            // Step 1 -> 2: actually start the server-side setup.
            if (n === 1) {
                isBusy = true;
                setLoading(primary, 'Preparing…');

                try {
                    const response = await postAction('2fa.setup');

                    if (!response.success) {
                        isBusy = false;
                        clearLoading(primary);
                        modal.close();
                        errorToast(response.message || 'Unable to start setup.');
                        return;
                    }

                    setupData = response.setup;
                    isBusy = false;
                    clearLoading(primary);
                    goToStep(2);
                } catch (err) {
                    isBusy = false;
                    clearLoading(primary);
                    modal.close();
                    handleApiError(err, 'Unable to start two-factor authentication setup.');
                }

                return;
            }

            // Step 2 -> 3: verify the OTP through PHP. Client only checks format.
            if (n === 2) {
                const body = modal.getBody();
                const boxes = body.querySelectorAll('[data-otp-box]');
                const errorEl = body.querySelector('[data-2fa-code-error]');
                const code = getOtpValue(boxes);
                const validFormat = /^\d{6}$/.test(code);
                boxes.forEach((box) => box.setAttribute('aria-invalid', String(!validFormat)));

                if (!validFormat) {
                    errorEl.textContent = 'Enter the 6-digit code from your app.';
                    errorEl.hidden = false;
                    boxes[0]?.focus();
                    return;
                }

                errorEl.hidden = true;
                isBusy = true;
                setLoading(primary, 'Verifying…');

                try {
                    const response = await postAction('2fa.verify', { code });

                    if (!response.success) {
                        boxes.forEach((box) => box.setAttribute('aria-invalid', 'true'));
                        errorEl.textContent = response.errors?.code || response.message || 'Invalid code. Please try again.';
                        errorEl.hidden = false;
                        boxes[0]?.focus();
                        return;
                    }

                    // The server already enabled 2FA inside completeSetup() —
                    // reflect that now, not only if the user clicks Finish.
                    // If they Leave from here instead, the badge must still
                    // be correct behind the modal.
                    recoveryCodes = response.recovery_codes;
                    applyTwoFactorState(response.settings['2fa']);
                    goToStep(3);
                } catch (err) {
                    handleApiError(err, 'Unable to verify your code.');
                } finally {
                    isBusy = false;
                    clearLoading(primary);
                }

                return;
            }

            // Step 3: deliberate completion. State is already applied (see
            // above) — this just clears the plaintext codes and closes,
            // bypassing the "leave without saving" warning since the user
            // explicitly confirmed they saved them (ack checkbox).
            if (n === 3) {
                isFinishing = true;
                recoveryCodes = null;
                setupData = null;
                modal.close();
            }
        }
    }

    function openDisable2faPasswordStep() {
        openPasswordConfirmModal(modal, {
            className: 'akd-modal--2fa',
            modalTitle: 'Confirm your password',
            modalSubtitle: 'For your security, re-enter your password to continue.',
            description: 'Enter your current password to confirm you want to disable two-factor authentication.',
            confirmLabel: 'Disable 2FA',
            successTitle: 'Two-factor authentication disabled',
            successText: 'Two-factor authentication has been turned off for your account.',

            onConfirmed: async (password) => {
                let response;

                try {
                    response = await postAction('2fa.disable', { password });
                } catch (err) {
                    if (err.data?.retry_after > 0) {
                        const rateLimitError = new Error(err.data.message ||
                            'Too many incorrect attempts.'
                        );

                        rateLimitError.rateLimited = true;
                        rateLimitError.retryAfter = Number(err.data.retry_after);
                        throw rateLimitError;
                    }

                    throw new Error(err.data?.message || 'Something went wrong. Please try again.');
                }

                if (!response.success) {
                    throw new Error(
                        response.errors?.password ||
                        response.message ||
                        'Incorrect password.'
                    );
                }

                applyTwoFactorState(response.settings['2fa']);
            },
        });
    }

    // ---------------- Manage view (shown once enabled) ----------------
    function openManageView() {
        modal.open({
            title: 'Two-factor authentication',
            subtitle: 'Manage your two-factor authentication settings.',
            content: buildManageContent(),
            footer: buildManageFooter(),
            className: 'akd-modal--2fa',
        });
    }

    function buildManageContent() {
        const wrap = document.createElement('div');
        wrap.className = 'akd-2fa-manage';

        wrap.innerHTML = `
            <div class="akd-2fa-manage__row">
                <div class="akd-2fa-manage__icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></div>
                <div class="akd-2fa-manage__content">
                    <span class="akd-2fa-manage__label">Security status</span>
                    <p class="akd-2fa-manage__desc">Two-factor authentication is protecting your account.</p>
                </div>
            </div>

            <div class="akd-2fa-manage__row">
                <div class="akd-2fa-manage__icon" aria-hidden="true"><i class="fa-solid fa-key"></i></div>
                <div class="akd-2fa-manage__content">
                    <span class="akd-2fa-manage__label">Backup codes</span>
                    <p class="akd-2fa-manage__desc">Your backup codes were shown once, right after setup.</p>
                </div>
                <button type="button" class="akd-btn akd-btn--secondary akd-2fa-manage__action" data-manage-view-codes>About</button>
            </div>

            <div class="akd-2fa-manage__row akd-2fa-manage__row--danger">
                <div class="akd-2fa-manage__icon akd-2fa-manage__icon--danger" aria-hidden="true"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="akd-2fa-manage__content">
                    <span class="akd-2fa-manage__label">Disable two-factor authentication</span>
                    <p class="akd-2fa-manage__desc">Turn off the extra verification step at sign-in.</p>
                </div>
                <button type="button" class="akd-btn akd-btn--danger akd-2fa-manage__action" data-manage-disable>Disable</button>
            </div>
        `;

        wrap.querySelector('[data-manage-view-codes]')?.addEventListener('click', () => {
            openBackupCodesInfo();
        });

        wrap.querySelector('[data-manage-disable]')?.addEventListener('click', () => {
            confirmDialog.ask({
                title: 'Disable two-factor authentication?',
                message: 'This will remove the extra verification step from your sign-ins. You can set up two-factor authentication again later.',
                confirmLabel: 'Continue',
                cancelLabel: 'Cancel',
                destructive: true,
            }).then((confirmed) => {
                if (confirmed) {
                    openDisable2faPasswordStep();
                }
            });
        });

        return wrap;
    }

    function buildManageFooter() {
        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'akd-btn akd-btn--secondary';
        close.textContent = 'Close';
        close.addEventListener('click', () => modal.close());
        return close;
    }

    // Informational only — the server cannot retrieve hashed codes,
    // so this must never imply the original codes can be shown again.
    function openBackupCodesInfo() {
        const body = modal.getBody();
        const footer = modal.getFooter();
        const modalEl = modal.getModal();
        const subtitleEl = modalEl.querySelector('.akd-modal__subtitle');
        body.classList.add('is-leaving');

        window.setTimeout(() => {
            if (subtitleEl) subtitleEl.textContent = 'About your backup codes.';
            const wrap = document.createElement('div');
            wrap.className = 'akd-2fa';

            wrap.innerHTML = `
                <h3 class="akd-2fa__heading">Backup codes</h3>

                <p class="akd-2fa__text">
                    Your backup codes were generated and shown once, right after you
                    finished setting up two-factor authentication. For your security,
                    we don't store the codes themselves, so they can't be displayed again.
                </p>

                <p class="akd-2fa__text">
                    If you've lost your backup codes and no longer have access to your
                    authenticator app, disable two-factor authentication and set it up
                    again to generate a new set of backup codes.
                </p>
            `;

            body.replaceChildren(wrap);
            const back = document.createElement('button');
            back.type = 'button';
            back.className = 'akd-btn akd-btn--secondary';
            back.textContent = 'Back';
            back.addEventListener('click', () => openManageView());
            footer.replaceChildren(back);
            body.classList.remove('is-leaving');
            body.classList.add('is-entering');
            requestAnimationFrame(() => body.classList.remove('is-entering'));
            focusFirst(footer);
        }, 160);
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

    const prefs = {
        achievements: true,
        quiz: true,
        challenges: true,
        communityAwards: true,
        animeAwards: true,
    };

    const categories = [
        { key: 'achievements', label: 'Achievement emails', desc: 'When you unlock an achievement or move up the leaderboard.' },
        { key: 'quiz', label: 'Quiz emails', desc: 'Reminders and results for quizzes you have joined.' },
        { key: 'challenges', label: 'Community challenge emails', desc: 'Updates on community challenges you can take part in.' },
        { key: 'communityAwards', label: 'Community award emails', desc: 'Nominations, voting, and results for community awards.' },
        { key: 'animeAwards', label: 'Anime award emails', desc: 'Nominations, voting, and results for the anime awards.' },
    ];

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

        const rows = categories.map(({ key, label, desc }) => `
            <div class="akd-email-prefs__row">
                <div>
                    <span class="akd-email-prefs__label" id="email-${key}-label">${label}</span>
                    <p class="akd-email-prefs__desc">${desc}</p>
                </div>
                <button type="button" class="akd-switch" role="switch" aria-checked="${prefs[key]}"
                    aria-labelledby="email-${key}-label" data-email-switch="${key}">
                    <span class="akd-switch__track"><span class="akd-switch__thumb"></span></span>
                </button>
            </div>
        `).join('');

        wrap.innerHTML = `
            <p class="akd-email-prefs__intro">Account and security emails can't be turned off.</p>
            ${rows}
            <div class="akd-email-prefs__row akd-email-prefs__row--locked">
                <div>
                    <span class="akd-email-prefs__label">Account and security emails</span>
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
        const total = categories.length;
        const onCount = Object.values(prefs).filter(Boolean).length;

        if (onCount === total) {
            summary.textContent = 'All optional emails on. Account emails always on.';
        } else if (onCount === 0) {
            summary.textContent = 'Optional emails off. Account emails always on.';
        } else {
            summary.textContent = `${onCount} of ${total} optional email types on. Account emails always on.`;
        }
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
        label.textContent = 'Preparing your data...';

        window.setTimeout(() => {
            downloadMockDataFile();
            label.textContent = 'Download again';
            desc.textContent = 'Your data file was downloaded to this device. (Preview data, for demonstration only.)';
            trigger.disabled = false;
            busy = false;
        }, 900);
    });

    function downloadMockDataFile() {
        const payload = {
            note: 'Preview export. Real account data will be included once this is connected.',
            generatedAt: new Date().toISOString(),
            profile: { username: 'preview_user' },
            achievements: [],
            quizHistory: [],
        };

        const blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'anime-nigeria-data-preview.json';
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    }
}

// ------------------------------------------------------------
// Delete account: confirm dialog, then password re-entry modal,
// then mock success. Password step now uses the shared helper above.
// ------------------------------------------------------------
function initDeleteAccount(root, modal, confirmDialog) {
    const trigger = root.querySelector('[data-delete-trigger]');
    if (!trigger) return;

    trigger.addEventListener('click', () => {
        confirmDialog.ask({
            title: 'Delete your account?',
            message: "This will permanently remove your account, achievements, and activity. This can't be undone.",
            confirmLabel: 'Continue',
            cancelLabel: 'Cancel',
            destructive: true,
        }).then((confirmed) => {
            if (confirmed) openPasswordStep();
        });
    });

    function openPasswordStep() {
        openPasswordConfirmModal(modal, {
            className: 'akd-modal--delete',
            modalTitle: 'Confirm your password',
            modalSubtitle: 'For your security, re-enter your password to continue.',
            description: "Enter your current password to confirm you'd like to permanently delete your account.",
            confirmLabel: 'Confirm deletion',
            successTitle: 'Deletion scheduled',
            successText: 'This is a preview. Nothing has been deleted. Once this is connected, your account and data would be permanently removed.',
        });
    }
}