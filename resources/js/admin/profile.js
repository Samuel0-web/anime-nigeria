// resources/js/admin/profile.js
// Admin Profile page behaviour: personal-information form, avatar change and
// removal, and password change.
//
// All three actions post to the existing /admin/api/update-profile endpoint
// (ProfileService::update). That service always validates fullname + username,
// so every request carries the last server-confirmed values for whatever the
// action is NOT editing. That keeps the three actions independent: uploading a
// photo never submits half-typed edits from the form, and vice versa.

import { api, handleApiError } from '../modules/api';
import { success, error as notifyError } from '../modules/toast';
import { setLoading, clearLoading } from '../modules/loading-state';
import { useConfirmDialog } from '../modules/confirm-dialog';
import { initLightbox } from '../modules/lightbox';

const ENDPOINT = '/admin/api/update-profile';
const USERNAME_PATTERN = /^[A-Za-z0-9_]+$/;
const AVATAR_ALLOWED_TYPES = ['image/png', 'image/jpeg'];
const AVATAR_MAX_BYTES = 2 * 1024 * 1024;

// Mirrors ProfileService::validatePassword(). The server stays authoritative;
// this only drives the live hints and saves a pointless round trip.
const PASSWORD_RULES = {
    length: (v) => v.length >= 8,
    uppercase: (v) => /[A-Z]/.test(v),
    number: (v) => /[0-9]/.test(v),
    symbol: (v) => /[!@#$%&*?,]/.test(v),
};

function collapseSpaces(value) {
    return value.trim().replace(/\s+/g, ' ');
}

function getInitials(name) {
    return name.trim().split(/\s+/).slice(0, 2)
        .map((part) => part[0].toUpperCase()).join('');
}

// Explicit display control, same reason as the member profile: a leftover
// inline style must never leave an element visually stuck.
function setDisplay(element, visible, displayValue = '') {
    if (!element) return;
    element.style.display = visible ? displayValue : 'none';
}

// Server timestamps are UTC; show them in the viewer's own locale and zone.
function formatLocalTimes(scope) {
    const formatter = new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });

    scope.querySelectorAll('time[data-local-time]').forEach((el) => {
        const date = new Date(el.getAttribute('datetime'));
        if (!Number.isNaN(date.getTime())) el.textContent = formatter.format(date);
    });
}

export function initAdminProfile() {
    const root = document.getElementById('adminProfile');
    if (!root?.dataset.profileConfig) return;
    const config = JSON.parse(root.dataset.profileConfig);
    const confirmDialog = useConfirmDialog();
    initLightbox();
    formatLocalTimes(root);

    // Last server-confirmed values. Everything is compared against these.
    const saved = {
        fullname: collapseSpaces(config.fullname || ''),
        username: (config.username || '').trim(),
        avatar: config.avatarUrl || null,
    };

    // Only one request at a time. It also stops the three actions from racing
    // each other over `saved`.
    let busy = null; // 'profile' | 'avatar' | 'password' | null

    // ---- Elements: identity / avatar ----
    const avatarWrap = root.querySelector('[data-avatar-wrap]');
    const avatarContainer = root.querySelector('[data-user-avatar-container]');
    const avatarInput = root.querySelector('[data-avatar-input]');
    const avatarChangeBtn = root.querySelector('[data-avatar-change]');
    const avatarRemoveBtn = root.querySelector('[data-avatar-remove]');
    const avatarError = root.querySelector('[data-avatar-error]');

    // ---- Elements: profile form ----
    const profileForm = root.querySelector('#adminProfileForm');
    const fullnameField = profileForm.querySelector('[data-field="fullname"]');
    const usernameField = profileForm.querySelector('[data-field="username"]');
    const fullnameInput = fullnameField.querySelector('input');
    const usernameInput = usernameField.querySelector('input');
    const profileSaveBtn = profileForm.querySelector('[data-profile-save]');
    const profileResetBtn = profileForm.querySelector('[data-profile-reset]');
    const profileStatus = profileForm.querySelector('[data-profile-status]');

    // ---- Elements: password form (absent for Google accounts) ----
    const passwordForm = root.querySelector('#adminPasswordForm');
    const currentPasswordField = passwordForm?.querySelector('[data-field="currentPassword"]') ?? null;
    const newPasswordField = passwordForm?.querySelector('[data-field="newPassword"]') ?? null;
    const confirmPasswordField = passwordForm?.querySelector('[data-field="confirmPassword"]') ?? null;
    const currentPasswordInput = currentPasswordField?.querySelector('input') ?? null;
    const newPasswordInput = newPasswordField?.querySelector('input') ?? null;
    const confirmPasswordInput = confirmPasswordField?.querySelector('input') ?? null;
    const passwordSaveBtn = passwordForm?.querySelector('[data-password-save]') ?? null;
    const rulesWrap = passwordForm?.querySelector('[data-password-rules-wrap]') ?? null;
    const ruleItems = passwordForm ? passwordForm.querySelectorAll('[data-password-rules] li') : [];

    // The server-rendered value may contain stray spaces. Normalise once so the
    // form never starts out looking "changed".
    fullnameInput.value = saved.fullname;

    // =====================================================================
    // Shared helpers
    // =====================================================================
    function setFieldError(field, message) {
        if (!field) return;
        field.classList.add('akd-admin-field--error');
        field.querySelector('input')?.setAttribute('aria-invalid', 'true');
        const msg = field.querySelector('[data-field-error]');
        if (msg) msg.textContent = message;
    }

    function clearFieldError(field) {
        if (!field) return;
        field.classList.remove('akd-admin-field--error');
        field.querySelector('input')?.removeAttribute('aria-invalid');
        const msg = field.querySelector('[data-field-error]');
        if (msg) msg.textContent = '';
    }

    function hasError(field) {
        return field?.classList.contains('akd-admin-field--error') ?? false;
    }

    function focusFirstError(scope) {
        scope.querySelector('.akd-admin-field--error input')?.focus();
    }

    function setAvatarError(message) {
        if (avatarError) avatarError.textContent = message || '';
    }

    function buildPayload(overrides = {}) {
        const formData = new FormData();
        formData.set('fullname', saved.fullname);
        formData.set('username', saved.username);
        Object.entries(overrides).forEach(([key, value]) => formData.set(key, value));
        return formData;
    }

    function send(formData) {
        return api(ENDPOINT, { method: 'POST', body: formData });
    }

    // Routes a 422 payload. Keyed validation errors go to their field. Anything
    // else (a message-only failure, or an error for a field this action does
    // not own) becomes a toast, so a failure is never silent.
    function routeFailure(result, fieldHandlers) {
        const errors = result.errors || {};
        const keys = Object.keys(errors);
        const unmatched = [];

        keys.forEach((key) => {
            if (fieldHandlers[key]) {
                fieldHandlers[key](errors[key]);
            } else {
                unmatched.push(errors[key]);
            }
        });

        if (!keys.length) {
            unmatched.push(result.message || 'Something went wrong. Please try again.');
        }

        if (unmatched.length) notifyError(unmatched[0]);
    }

    // Updates every place the current user appears: this page, the top bar
    // account menu, and the mobile sidebar account row. The response from the
    // server is the source of truth, never the submitted values.
    function syncUserUI(user) {
        document.querySelectorAll('[data-user-fullname]').forEach((el) => {
            el.textContent = user.fullname;
        });

        document.querySelectorAll('[data-user-username]').forEach((el) => {
            el.textContent = `@${user.username}`;
        });

        const initials = getInitials(user.fullname);

        document.querySelectorAll('[data-user-avatar-container]').forEach((container) => {
            const link = container.querySelector('[data-user-avatar-link]');
            const img = container.querySelector('[data-user-avatar]');
            const fallback = container.querySelector('[data-user-avatar-initials]');

            if (fallback) {
                fallback.textContent = initials;
                fallback.style.backgroundColor = user.avatarColor;
            }

            if (user.avatar) {
                if (link) {
                    link.href = user.avatar;
                    setDisplay(link, true, 'block');
                }
                if (img) {
                    img.src = user.avatar;
                    setDisplay(img, true);
                }
                setDisplay(fallback, false);
            } else {
                if (link) {
                    link.removeAttribute('href');
                    setDisplay(link, false);
                }
                if (img) {
                    img.removeAttribute('src');
                    setDisplay(img, false);
                }

                setDisplay(fallback, true, 'flex');
            }
        });
    }

    function applyServerUser(user) {
        saved.fullname = user.fullname;
        saved.username = user.username;
        saved.avatar = user.avatar || null;
        syncUserUI(user);
        syncAvatarControls();
    }

    // =====================================================================
    // Busy state and control syncing
    // =====================================================================
    function setBusy(next) {
        busy = next;
        root.setAttribute('aria-busy', next !== null ? 'true' : 'false');
        avatarWrap?.classList.toggle('is-busy', next === 'avatar');
        syncAvatarControls();
        syncProfileControls();
        syncPasswordControls();
    }

    function isProfileDirty() {
        return collapseSpaces(fullnameInput.value) !== saved.fullname
            || usernameInput.value.trim() !== saved.username;
    }

    function syncProfileControls() {
        const dirty = isProfileDirty();
        profileSaveBtn.disabled = !dirty || busy !== null;
        profileResetBtn.hidden = !dirty;
        profileResetBtn.disabled = busy !== null;
        profileStatus.textContent = dirty ? 'Unsaved changes' : '';
    }

    function isPasswordDirty() {
        return [currentPasswordInput, newPasswordInput, confirmPasswordInput]
            .some((input) => input && input.value.length > 0);
    }

    function syncPasswordControls() {
        if (!passwordSaveBtn) return;
        passwordSaveBtn.disabled = !isPasswordDirty() || busy !== null;
    }

    function syncAvatarControls() {
        avatarChangeBtn.disabled = busy !== null;
        avatarRemoveBtn.disabled = busy !== null;
        avatarRemoveBtn.hidden = !saved.avatar;
    }

    // =====================================================================
    // Personal information
    // =====================================================================
    function validateFullname(showError = true) {
        const value = collapseSpaces(fullnameInput.value);
        clearFieldError(fullnameField);

        if (!value) {
            if (showError) setFieldError(fullnameField, 'Full name is required.');
            return false;
        }

        if (value.length > 100) {
            if (showError) setFieldError(fullnameField, 'Full name is too long.');
            return false;
        }

        return true;
    }

    function validateUsername(showError = true) {
        const value = usernameInput.value.trim();
        clearFieldError(usernameField);

        if (!value) {
            if (showError) setFieldError(usernameField, 'Username is required.');
            return false;
        }

        if (value.length < 3 || value.length > 20) {
            if (showError) setFieldError(usernameField, 'Must be between 3 and 20 characters.');
            return false;
        }

        if (!USERNAME_PATTERN.test(value)) {
            if (showError) setFieldError(usernameField, 'Only letters, numbers and underscores.');
            return false;
        }

        return true;
    }

    fullnameInput.addEventListener('blur', () => validateFullname());
    fullnameInput.addEventListener('input', () => {
        if (hasError(fullnameField)) validateFullname();
        syncProfileControls();
    });

    usernameInput.addEventListener('blur', () => {
        if (usernameInput.value.trim() !== '') validateUsername();
    });
    usernameInput.addEventListener('input', () => {
        if (hasError(usernameField)) validateUsername();
        syncProfileControls();
    });

    profileResetBtn.addEventListener('click', () => {
        fullnameInput.value = saved.fullname;
        usernameInput.value = saved.username;
        clearFieldError(fullnameField);
        clearFieldError(usernameField);
        syncProfileControls();
        fullnameInput.focus();
    });

    profileForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (busy || !isProfileDirty()) return;
        fullnameInput.value = collapseSpaces(fullnameInput.value);
        const checks = [validateFullname(), validateUsername()];

        if (checks.includes(false)) {
            focusFirstError(profileForm);
            return;
        }

        setBusy('profile');
        setLoading(profileSaveBtn, 'Saving...');

        try {
            const result = await send(buildPayload({
                fullname: fullnameInput.value,
                username: usernameInput.value.trim(),
            }));

            if (result.success === false) {
                routeFailure(result, {
                    fullname: (message) => setFieldError(fullnameField, message),
                    username: (message) => setFieldError(usernameField, message),
                });

                focusFirstError(profileForm);
                return;
            }

            applyServerUser(result.user);
            fullnameInput.value = saved.fullname;
            usernameInput.value = saved.username;
            clearFieldError(fullnameField);
            clearFieldError(usernameField);
            success(result.message || 'Profile updated successfully.');
        } catch (err) {
            handleApiError(err);
        } finally {
            clearLoading(profileSaveBtn);
            setBusy(null);
        }
    });

    // =====================================================================
    // Avatar
    // =====================================================================
    // Local preview on the profile page only. The top bar and sidebar change
    // once the server confirms the upload.
    function renderIdentityAvatar(src) {
        if (!avatarContainer) return;
        const link = avatarContainer.querySelector('[data-user-avatar-link]');
        const img = avatarContainer.querySelector('[data-user-avatar]');
        const fallback = avatarContainer.querySelector('[data-user-avatar-initials]');

        if (src) {
            img.src = src;
            setDisplay(img, true);
            setDisplay(link, true, 'block');
            setDisplay(fallback, false);
        } else {
            img.removeAttribute('src');
            setDisplay(img, false);
            setDisplay(link, false);
            setDisplay(fallback, true, 'flex');
        }
    }

    avatarChangeBtn.addEventListener('click', () => {
        if (!busy) avatarInput.click();
    });

    avatarInput.addEventListener('change', async () => {
        const file = avatarInput.files?.[0];
        avatarInput.value = ''; // lets the same file be chosen again later
        if (!file || busy) return;
        setAvatarError('');

        if (!AVATAR_ALLOWED_TYPES.includes(file.type)) {
            setAvatarError('Please choose a PNG or JPG image.');
            return;
        }

        if (file.size > AVATAR_MAX_BYTES) {
            setAvatarError('Image must be under 2MB.');
            return;
        }

        const previewUrl = URL.createObjectURL(file);
        renderIdentityAvatar(previewUrl);
        setBusy('avatar');

        try {
            const result = await send(buildPayload({ avatar: file }));

            if (result.success === false) {
                renderIdentityAvatar(saved.avatar);
                routeFailure(result, { avatar: setAvatarError });
                return;
            }

            applyServerUser(result.user);
            success('Profile photo updated successfully.');
        } catch (err) {
            renderIdentityAvatar(saved.avatar);
            handleApiError(err);
        } finally {
            URL.revokeObjectURL(previewUrl);
            setBusy(null);
        }
    });

    avatarRemoveBtn.addEventListener('click', async () => {
        if (busy) return;
        setAvatarError('');

        try {
            const confirmed = await confirmDialog.ask({
                title: 'Remove profile photo?',
                message: 'This can\u2019t be undone. You can always upload a new one.',
                confirmLabel: 'Remove',
                cancelLabel: 'Cancel',
                destructive: true,
            });

            if (!confirmed || busy) return;
            setBusy('avatar');
            const result = await send(buildPayload({ removeAvatar: '1' }));

            if (result.success === false) {
                routeFailure(result, { avatar: setAvatarError });
                return;
            }

            applyServerUser(result.user);
            success('Profile photo removed.');
        } catch (err) {
            handleApiError(err);
        } finally {
            if (busy === 'avatar') setBusy(null);
        }
    });

    // =====================================================================
    // Password
    // =====================================================================
    if (passwordForm) {
        let newPasswordFocused = false;

        function updatePasswordRules() {
            const value = newPasswordInput.value;

            ruleItems.forEach((li) => {
                const met = Boolean(PASSWORD_RULES[li.dataset.rule]?.(value));
                li.classList.toggle('is-met', met);
                const icon = li.querySelector('i');
                if (icon) icon.className = met ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle';
            });
        }

        function syncRulesVisibility() {
            rulesWrap?.classList.toggle('is-visible',
                newPasswordFocused || newPasswordInput.value.length > 0);
        }

        function passwordMeetsAllRules(value) {
            return Object.values(PASSWORD_RULES).every((rule) => rule(value));
        }

        function resetPasswordVisibility() {
            passwordForm.querySelectorAll('[data-password-toggle]').forEach((btn) => {
                const input = btn.closest('.akd-admin-field__control')?.querySelector('input');
                if (input) input.type = 'password';
                const icon = btn.querySelector('i');
                if (icon) icon.className = 'fa-solid fa-eye';
                btn.setAttribute('aria-label', 'Show password');
            });
        }

        passwordForm.querySelectorAll('[data-password-toggle]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const input = btn.closest('.akd-admin-field__control')?.querySelector('input');
                if (!input) return;
                const showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                const icon = btn.querySelector('i');
                if (icon) icon.className = showing ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
                btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            });
        });

        function validateCurrentPassword() {
            clearFieldError(currentPasswordField);

            if (!currentPasswordInput.value) {
                setFieldError(currentPasswordField, 'Enter your current password.');
                return false;
            }

            return true;
        }

        function validateNewPassword() {
            clearFieldError(newPasswordField);

            if (!newPasswordInput.value) {
                setFieldError(newPasswordField, 'Enter a new password.');
                return false;
            }

            if (!passwordMeetsAllRules(newPasswordInput.value)) {
                setFieldError(newPasswordField, 'Password does not meet all requirements.');
                return false;
            }

            return true;
        }

        function validateConfirmPassword() {
            clearFieldError(confirmPasswordField);

            if (!confirmPasswordInput.value) {
                setFieldError(confirmPasswordField, 'Confirm your new password.');
                return false;
            }

            if (confirmPasswordInput.value !== newPasswordInput.value) {
                setFieldError(confirmPasswordField, 'Passwords do not match.');
                return false;
            }

            return true;
        }

        currentPasswordInput.addEventListener('input', () => {
            clearFieldError(currentPasswordField);
            syncPasswordControls();
        });

        newPasswordInput.addEventListener('focus', () => {
            newPasswordFocused = true;
            syncRulesVisibility();
        });

        newPasswordInput.addEventListener('blur', () => {
            newPasswordFocused = false;
            syncRulesVisibility();
        });

        newPasswordInput.addEventListener('input', () => {
            clearFieldError(newPasswordField);
            updatePasswordRules();
            syncRulesVisibility();
            if (hasError(confirmPasswordField)) validateConfirmPassword();
            syncPasswordControls();
        });

        confirmPasswordInput.addEventListener('blur', () => {
            if (confirmPasswordInput.value) validateConfirmPassword();
        });

        confirmPasswordInput.addEventListener('input', () => {
            if (hasError(confirmPasswordField)) validateConfirmPassword();
            syncPasswordControls();
        });

        function resetPasswordForm() {
            [currentPasswordInput, newPasswordInput, confirmPasswordInput]
                .forEach((input) => { input.value = ''; });

            [currentPasswordField, newPasswordField, confirmPasswordField]
                .forEach(clearFieldError);

            newPasswordFocused = false;
            resetPasswordVisibility();
            updatePasswordRules();
            syncRulesVisibility();
            syncPasswordControls();
        }

        passwordForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (busy || !isPasswordDirty()) return;

            const checks = [
                validateCurrentPassword(),
                validateNewPassword(),
                validateConfirmPassword(),
            ];

            if (checks.includes(false)) {
                focusFirstError(passwordForm);
                return;
            }

            setBusy('password');
            setLoading(passwordSaveBtn, 'Updating...');

            try {
                const result = await send(buildPayload({
                    currentPassword: currentPasswordInput.value,
                    newPassword: newPasswordInput.value,
                    confirmPassword: confirmPasswordInput.value,
                }));

                if (result.success === false) {
                    routeFailure(result, {
                        currentPassword: (message) => setFieldError(currentPasswordField, message),
                        newPassword: (message) => setFieldError(newPasswordField, message),
                        confirmPassword: (message) => setFieldError(confirmPasswordField, message),
                    });

                    focusFirstError(passwordForm);
                    return;
                }

                applyServerUser(result.user);
                resetPasswordForm();
                success('Password updated successfully.');
            } catch (err) {
                handleApiError(err);
            } finally {
                clearLoading(passwordSaveBtn);
                setBusy(null);
            }
        });

        updatePasswordRules();
        syncRulesVisibility();
    }

    // ---- Initial state ----
    syncAvatarControls();
    syncProfileControls();
    syncPasswordControls();
}