import { useConfirmDialog } from '../modules/confirm-dialog';

const USERNAME_PATTERN = /^[A-Za-z0-9_]+$/;
const AVATAR_ALLOWED_TYPES = ['image/png', 'image/jpeg'];
const AVATAR_MAX_BYTES = 2 * 1024 * 1024;

const PASSWORD_RULES = {
    length: (v) => v.length >= 8,
    uppercase: (v) => /[A-Z]/.test(v),
    number: (v) => /[0-9]/.test(v),
    symbol: (v) => /[!@#$%&*?,]/.test(v),
};

function collapseSpaces(value) {
    return value.trim().replace(/\s+/g, ' ');
}

export function initProfileModal({overlayId = 'editProfileOverlay', modalId = 'editProfileModal',
    lightboxId = 'akdAvatarLightbox',
} = {}) {
    
    const overlay = document.getElementById(overlayId);
    const modal = document.getElementById(modalId);
    if (!overlay || !modal) return;
    const confirmDialog = useConfirmDialog();
    const lightbox = createLightbox(lightboxId);
    const closeBtn = modal.querySelector('[data-modal-close]');
    const cancelBtn = modal.querySelector('[data-modal-cancel]');
    const form = modal.querySelector('form');
    const saveBtn = modal.querySelector('[data-modal-save]');
    const banner = modal.querySelector('[data-modal-banner]');
    const isGoogleAccount = form?.dataset.isGoogle === '1';

    // ---- Fields ----
    const fullNameField = modal.querySelector('[data-field="fullname"]');
    const usernameField = modal.querySelector('[data-field="username"]');
    const currentPasswordField = modal.querySelector('[data-field="currentPassword"]');
    const newPasswordField = modal.querySelector('[data-field="newPassword"]');
    const confirmPasswordField = modal.querySelector('[data-field="confirmPassword"]');
    const fullNameInput = fullNameField.querySelector('input');
    const usernameInput = usernameField.querySelector('input');
    const currentPasswordInput = currentPasswordField?.querySelector('input') ?? null;
    const newPasswordInput = newPasswordField?.querySelector('input') ?? null;
    const confirmPasswordInput = confirmPasswordField?.querySelector('input') ?? null;
    const originalFullName = fullNameInput.value;
    const originalUsername = usernameInput.value;

    // ---- Avatar ----
    const avatarWrap = modal.querySelector('.akd-modal__avatar-wrap');
    const avatarPreviewBtn = modal.querySelector('[data-avatar-preview]');
    const avatarInput = modal.querySelector('[data-avatar-input]');
    const avatarError = modal.querySelector('[data-avatar-error]');
    const avatarRemoveBtn = modal.querySelector('[data-avatar-remove]');
    const userInitials = avatarWrap?.dataset.userInitials || '';
    const avatarColor = avatarWrap?.dataset.avatarColor || '#3457D5';
    const hadOriginalAvatar = !!avatarPreviewBtn?.querySelector('img[data-avatar-img]');
    const originalAvatarSrc = hadOriginalAvatar ? avatarPreviewBtn.querySelector('img[data-avatar-img]').src : null;

    // ---- Password section collapse (new) ----
    const passwordToggleBtn = modal.querySelector('[data-password-section-toggle]');
    const passwordPanel = modal.querySelector('[data-password-panel]');

    function setPasswordSectionOpen(open) {
        if (!passwordToggleBtn || !passwordPanel) return;
        passwordToggleBtn.setAttribute('aria-expanded', String(open));
        passwordPanel.classList.toggle('is-open', open);
        passwordPanel.toggleAttribute('inert', !open);
    }

    passwordToggleBtn?.addEventListener('click', () => {
        const isOpen = passwordToggleBtn.getAttribute('aria-expanded') === 'true';
        setPasswordSectionOpen(!isOpen);
    });

    let avatarState = 'original'; // 'original' | 'newFile' | 'removed'
    let isDirty = false;
    let state = 'idle'; // idle | loading | success
    let lastFocusedEl = null;
    const focusableSelector = 'button:not([disabled]), [href], input:not([disabled]), textarea, [tabindex]:not([tabindex="-1"])';

    function getFocusable() {
        return Array.from(modal.querySelectorAll(focusableSelector)).filter((el) => !el.closest('[inert]'));
    }

    // ---- Avatar rendering ----
    function renderInitialsAvatar() {
        avatarPreviewBtn.innerHTML = `<div class="akd-modal__avatar-img akd-modal__avatar-img--initials" data-avatar-img style="background-color:${avatarColor}">${userInitials}</div>`;
    }

    function renderImageAvatar(src) {
        avatarPreviewBtn.innerHTML = `<img src="${src}" alt="" class="akd-modal__avatar-img" data-avatar-img>`;
    }

    function hasAvatarImage() {
        return !!avatarPreviewBtn.querySelector('img[data-avatar-img]');
    }

    function setAvatarError(message) {
        if (!avatarError) return;
        avatarError.textContent = message || '';
        avatarError.classList.toggle('is-visible', !!message);
    }

    function restoreOriginalAvatar() {
        if (hadOriginalAvatar) {
            renderImageAvatar(originalAvatarSrc);
        } else {
            renderInitialsAvatar();
        }

        avatarInput.value = '';
        avatarState = 'original';
        avatarRemoveBtn.hidden = !hadOriginalAvatar;
        setAvatarError(null);
    }

    avatarInput?.addEventListener('change', () => {
        const file = avatarInput.files?.[0];
        if (!file) return;
        setAvatarError(null);

        if (!AVATAR_ALLOWED_TYPES.includes(file.type)) {
            setAvatarError('Please choose a PNG or JPG image.');
            avatarInput.value = '';
            return;
        }

        if (file.size > AVATAR_MAX_BYTES) {
            setAvatarError('Image must be under 2MB.');
            avatarInput.value = '';
            return;
        }

        const reader = new FileReader();

        reader.onload = () => {
            renderImageAvatar(reader.result);
            avatarState = 'newFile';
            avatarRemoveBtn.hidden = false;
            updateDirtyState();
        };

        reader.onerror = () => {
            setAvatarError('Could not load that image. Please try another.');
            avatarInput.value = '';
        };

        reader.readAsDataURL(file);
    });

    avatarRemoveBtn?.addEventListener('click', async () => {
        if (!confirmDialog) return;

        const confirmed = await confirmDialog.ask({
            title: 'Remove profile picture?',
            message: 'This can\u2019t be undone. You can always upload a new one.',
            confirmLabel: 'Remove',
            cancelLabel: 'Cancel',
            destructive: true,
        });

        if (!confirmed) return;
        renderInitialsAvatar();
        avatarInput.value = '';
        avatarState = hadOriginalAvatar ? 'removed' : 'original';
        avatarRemoveBtn.hidden = true;
        updateDirtyState();
    });

    avatarPreviewBtn?.addEventListener('click', () => {
        if (!hasAvatarImage()) return;
        const img = avatarPreviewBtn.querySelector('img[data-avatar-img]');
        lightbox.open(img.src);
    });

    // ---- Password toggles (show/hide) ----
    modal.querySelectorAll('[data-password-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = btn.previousElementSibling;
            if (!input) return;
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.querySelector('i').className = showing ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
            btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        });
    });

    // ---- Password rules (live) ----
    const ruleItems = modal.querySelectorAll('[data-password-rules] li');

    function updatePasswordRules() {
        if (!newPasswordInput) return;
        const value = newPasswordInput.value;
        ruleItems.forEach((li) => {
            const rule = li.dataset.rule;
            const met = PASSWORD_RULES[rule]?.(value);
            li.classList.toggle('is-met', !!met);
            const icon = li.querySelector('i');
            if (icon) icon.className = met ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle';
        });
    }

    function passwordMeetsAllRules(value) {
        return Object.values(PASSWORD_RULES).every((fn) => fn(value));
    }

    function isChangingPassword() {
        return !isGoogleAccount && !!newPasswordInput && newPasswordInput.value.length > 0;
    }

    // ---- Validation ----
    function setFieldError(fieldWrap, message) {
        if (!fieldWrap) return;
        fieldWrap.classList.add('akd-field--error');
        fieldWrap.querySelector('input')?.setAttribute('aria-invalid', 'true');
        const msg = fieldWrap.querySelector('[data-field-error]');
        if (msg) msg.textContent = message;
    }

    function clearFieldError(fieldWrap) {
        if (!fieldWrap) return;
        fieldWrap.classList.remove('akd-field--error');
        fieldWrap.querySelector('input')?.removeAttribute('aria-invalid');
    }

    function validateFullName(showError = true) {
        const value = collapseSpaces(fullNameInput.value);
        clearFieldError(fullNameField);

        if (!value) {
            if (showError) setFieldError(fullNameField, 'Full name is required.');
            return false;
        }

        if (value.length > 100) {
            if (showError) setFieldError(fullNameField, 'Full name is too long.');
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

    function validateCurrentPassword(showError = true) {
        if (!isChangingPassword()) { clearFieldError(currentPasswordField); return true; }
        clearFieldError(currentPasswordField);

        if (!currentPasswordInput.value) {
            if (showError) setFieldError(currentPasswordField, 'Enter your current password.');
            return false;
        }

        return true;
    }

    function validateNewPassword(showError = true) {
        if (!isChangingPassword()) { clearFieldError(newPasswordField); return true; }
        clearFieldError(newPasswordField);

        if (!passwordMeetsAllRules(newPasswordInput.value)) {
            if (showError) setFieldError(newPasswordField, 'Password does not meet all requirements.');
            return false;
        }

        return true;
    }

    function validateConfirmPassword(showError = true) {
        if (!isChangingPassword()) { clearFieldError(confirmPasswordField); return true; }
        clearFieldError(confirmPasswordField);

        if (confirmPasswordInput.value !== newPasswordInput.value) {
            if (showError) setFieldError(confirmPasswordField, 'Passwords do not match.');
            return false;
        }

        return true;
    }

    fullNameInput.addEventListener('blur', () => validateFullName());
    fullNameInput.addEventListener('input', updateDirtyState);

    usernameInput.addEventListener('input', () => {
        validateUsername();
        updateDirtyState();
    });

    newPasswordInput?.addEventListener('input', () => {
        updatePasswordRules();
        validateNewPassword(false);
        updateDirtyState();
    });

    confirmPasswordInput?.addEventListener('input', () => {
        validateConfirmPassword(false);
        updateDirtyState();
    });

    currentPasswordInput?.addEventListener('input', updateDirtyState);

    // ---- Dirty state ----
    function updateDirtyState() {
        const nameDirty = collapseSpaces(fullNameInput.value) !== originalFullName.trim();
        const usernameDirty = usernameInput.value.trim() !== originalUsername.trim();
        const avatarDirty = avatarState !== 'original';

        const passwordDirty = !isGoogleAccount && ((newPasswordInput?.value.length ?? 0) > 0 ||
            (currentPasswordInput?.value.length ?? 0) > 0 ||
            (confirmPasswordInput?.value.length ?? 0) > 0
        );

        isDirty = nameDirty || usernameDirty || avatarDirty || passwordDirty;
        saveBtn.disabled = !isDirty || state === 'loading';
    }

    // ---- Banner ----
    function setBanner(type, message) {
        if (!banner) return;
        banner.classList.remove('akd-modal__banner--error', 'akd-modal__banner--success', 'is-visible');
        if (!type) return;
        banner.textContent = message;
        banner.classList.add(`akd-modal__banner--${type}`, 'is-visible');
    }

    // ---- Open / close ----
    function openModal(trigger) {
        lastFocusedEl = trigger || document.activeElement;
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', handleKeydown);
        (getFocusable()[0] || modal).focus();
    }

    function resetState() {
        state = 'idle';
        setBanner(null);
        [fullNameField, usernameField, currentPasswordField, newPasswordField, confirmPasswordField]
            .forEach((f) => clearFieldError(f));

        saveBtn.disabled = true;
        saveBtn.classList.remove('akd-btn--success');
        saveBtn.textContent = 'Save Changes';
        fullNameInput.value = originalFullName;
        usernameInput.value = originalUsername;
        if (currentPasswordInput) currentPasswordInput.value = '';
        if (newPasswordInput) newPasswordInput.value = '';
        if (confirmPasswordInput) confirmPasswordInput.value = '';
        updatePasswordRules();
        setPasswordSectionOpen(false); // always collapsed on fresh open
        restoreOriginalAvatar();
        isDirty = false;
    }

    function reallyClose() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        document.removeEventListener('keydown', handleKeydown);
        resetState();
        lastFocusedEl?.focus();
    }

    async function requestClose() {
        if (state === 'loading') return;
        if (!isDirty) { reallyClose(); return; }
        if (!confirmDialog) { reallyClose(); return; }

        const discard = await confirmDialog.ask({
            title: 'Discard changes?',
            message: 'You have unsaved changes.',
            confirmLabel: 'Discard',
            cancelLabel: 'Continue Editing',
            destructive: true,
        });

        if (discard) reallyClose();
    }

    function handleKeydown(e) {
        if (confirmDialog?.isOpen() || lightbox.isOpen()) return;

        if (e.key === 'Escape') {
            e.preventDefault();
            requestClose();
            return;
        }

        if (e.key === 'Tab') {
            const focusables = getFocusable();
            if (!focusables.length) return;
            const first = focusables[0];
            const last = focusables[focusables.length - 1];

            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    }

    document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => openModal(trigger));
    });

    closeBtn?.addEventListener('click', requestClose);
    cancelBtn?.addEventListener('click', requestClose);

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) requestClose();
    });

    // ---- Submit ----
    form?.addEventListener('submit', (e) => {
        e.preventDefault();
        if (state === 'loading' || !isDirty) return;
        fullNameInput.value = collapseSpaces(fullNameInput.value);

        const checks = [validateFullName(), validateUsername()];
        if (!isGoogleAccount) {
            checks.push(validateCurrentPassword(), validateNewPassword(), validateConfirmPassword());
        }

        if (checks.includes(false)) {
            const erroredField = modal.querySelector('.akd-field--error');

            // If the error is inside the collapsed password section, open it
            // first — an error the user can't see is worse than no validation.
            if (erroredField && passwordPanel?.contains(erroredField)) {
                setPasswordSectionOpen(true);
            }

            setBanner('error', 'Please fix the errors below.');
            erroredField?.querySelector('input')?.focus();
            return;
        }

        setBanner(null);
        state = 'loading';
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="akd-spinner" aria-hidden="true"></span> Saving\u2026';

        // Placeholder for the real request — swap this timeout for a fetch() call
        // to your update-profile endpoint once it exists.
        window.setTimeout(() => {
            state = 'success';
            setBanner('success', 'Profile updated successfully.');
            saveBtn.classList.add('akd-btn--success');
            saveBtn.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i> Saved';
            window.setTimeout(reallyClose, 1100);
        }, 900);
    });

    resetState();
}

// ---- Lightbox (reuses the existing .an-lightbox component) ----
function createLightbox(overlayId) {
    const overlay = document.getElementById(overlayId);
    const img = overlay?.querySelector('[data-lightbox-image]');
    const closeBtn = overlay?.querySelector('[data-lightbox-close]');

    if (!overlay || !img) {
        return { open: () => {}, close: () => {}, isOpen: () => false };
    }

    let lastFocusedEl = null;

    function handleKeydown(e) {
        if (e.key === 'Escape') {
            e.preventDefault();
            close();
        }
    }

    function open(src) {
        lastFocusedEl = document.activeElement;
        img.src = src;
        overlay.classList.add('is-open');
        document.addEventListener('keydown', handleKeydown);
        closeBtn?.focus();
    }

    function close() {
        overlay.classList.remove('is-open');
        document.removeEventListener('keydown', handleKeydown);
        lastFocusedEl?.focus();
    }

    closeBtn?.addEventListener('click', close);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) close();
    });

    return { open, close, isOpen: () => overlay.classList.contains('is-open') };
}