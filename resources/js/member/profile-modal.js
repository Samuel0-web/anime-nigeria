// resources/js/member/profile-modal.js
// Edit Profile business logic: form state, avatar, password, validation,
// dirty-state tracking, and API submission. Modal chrome (open/close, focus
// trap, Escape, backdrop, scroll lock) is owned entirely by modal.js.

import { useConfirmDialog } from '../modules/confirm-dialog';
import { api, handleApiError } from '../modules/api';
import { success } from '../modules/toast';
import { useModal } from '../modules/modal';

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

function getInitials(name) {
    return name.trim().split(/\s+/).slice(0, 2).map((part) => part[0].toUpperCase()).join('');
}

// Explicit display control instead of the `hidden` attribute — avoids the
// case where a leftover inline style from a previous state out-specificities
// the [hidden] stylesheet rule and an element stays visually stuck.
function setDisplay(element, visible, displayValue = '') {
    if (!element) return;
    element.style.display = visible ? displayValue : 'none';
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    })[character]);
}

function createProfileTemplate({ isGoogle, userInitials, avatarColor, avatarUrl, fullname, username }) {
    userInitials = escapeHtml(userInitials);
    avatarColor = escapeHtml(avatarColor);
    avatarUrl = escapeHtml(avatarUrl);
    fullname = escapeHtml(fullname);
    username = escapeHtml(username);

    const template = document.createElement('template');
    template.innerHTML = `
        <form id="editProfileForm" class="akd-profile-form" enctype="multipart/form-data" novalidate data-is-google="${isGoogle ? '1' : '0'}">
            <div class="akd-modal__banner" data-modal-banner role="status" aria-live="polite"></div>

            <div class="akd-modal__avatar-field">
                <div class="akd-modal__avatar-wrap" data-user-initials="${userInitials}" data-avatar-color="${avatarColor}">
                    <button type="button" class="akd-modal__avatar-preview" data-avatar-preview aria-label="View profile picture">
                        <img src="${avatarUrl}" alt="" class="akd-modal__avatar-img" data-avatar-img${avatarUrl ? '' : ' style="display:none;"'}>
                        <div class="akd-modal__avatar-img akd-modal__avatar-img--initials" data-avatar-initials style="background-color: ${avatarColor};${avatarUrl ? ' display:none;' : ''}">${userInitials}</div>
                    </button>
                    <label class="akd-modal__avatar-upload" for="avatarUploadInput" aria-label="Upload new photo">
                        <i class="fa-solid fa-pen"></i>
                    </label>
                </div>

                <input type="file" name="avatar" id="avatarUploadInput" accept="image/png,image/jpeg" class="akd-modal__avatar-input" data-avatar-input>
                <span class="akd-modal__avatar-hint" data-avatar-hint>PNG or JPG, up to 2MB</span>
                <span class="akd-modal__avatar-error" data-avatar-error></span>
                <button type="button" class="akd-modal__avatar-remove" data-avatar-remove${avatarUrl ? '' : ' style="display:none;"'}>
                    <i class="fa-solid fa-trash-can"></i>
                    Remove
                </button>
            </div>

            <div class="akd-field" data-field="fullname">
                <label class="akd-field__label" for="editFullname">Full Name</label>
                <div class="akd-field__control-wrap">
                    <input type="text" name="fullname" id="editFullname" class="akd-field__input" value="${fullname}" maxlength="100" autocomplete="off">
                </div>
                <span class="akd-field__error-msg" data-field-error></span>
            </div>

            <div class="akd-field" data-field="username">
                <label class="akd-field__label" for="editUsername">Username</label>
                <div class="akd-field__control-wrap">
                    <span class="akd-field__affix">@</span>
                    <input type="text" name="username" id="editUsername" class="akd-field__input akd-field__input--with-affix" value="${username}" maxlength="20" autocomplete="off">
                </div>
                <span class="akd-field__error-msg" data-field-error></span>
            </div>

            ${isGoogle ? '' : `
                <div class="akd-modal__divider"></div>
                <div class="akd-password-collapse" data-password-collapse>
                    <button type="button" class="akd-password-collapse__trigger" data-password-section-toggle aria-expanded="false" aria-controls="passwordSectionPanel">
                        <span class="akd-password-collapse__trigger-text"><i class="fa-solid fa-lock"></i> Change Password</span>
                        <i class="fa-solid fa-chevron-down akd-password-collapse__chevron" aria-hidden="true"></i>
                    </button>
                    <div class="akd-password-collapse__panel" id="passwordSectionPanel" data-password-panel>
                        <div class="akd-password-collapse__inner">
                            <div class="akd-password-section">
                                ${createPasswordField('currentPassword', 'Current Password', 'editCurrentPassword', 'current-password', 'Needed to confirm it\'s you before changing your password.')}
                                ${createPasswordField('newPassword', 'New Password', 'editNewPassword', 'new-password', '', `
                                    <ul class="akd-password-rules" data-password-rules>
                                        <li data-rule="length"><i class="fa-solid fa-circle"></i> At least 8 characters</li>
                                        <li data-rule="uppercase"><i class="fa-solid fa-circle"></i> One uppercase letter</li>
                                        <li data-rule="number"><i class="fa-solid fa-circle"></i> One number</li>
                                        <li data-rule="symbol"><i class="fa-solid fa-circle"></i> One symbol (! @ # $ % &amp; * ? ,)</li>
                                    </ul>
                                `)}
                                ${createPasswordField('confirmPassword', 'Confirm New Password', 'editConfirmPassword', 'new-password')}
                            </div>
                        </div>
                    </div>
                </div>
            `}
        </form>
    `;
    return template;
}

function createPasswordField(name, label, id, autocomplete, hint = '', extra = '') {
    return `
        <div class="akd-field" data-field="${name}">
            <label class="akd-field__label" for="${id}">${label}</label>
            <div class="akd-field__control-wrap">
                <input type="password" name="${name}" id="${id}" class="akd-field__input akd-field__input--with-toggle" autocomplete="${autocomplete}">
                <button type="button" class="akd-field__toggle" data-password-toggle aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
            </div>
            ${hint ? `<span class="akd-field__hint">${hint}</span>` : ''}
            ${extra}
            <span class="akd-field__error-msg" data-field-error></span>
        </div>
    `;
}

export function initProfileModal({
    triggerSelector = '[data-modal-open="edit-profile"]',
    lightboxId = 'akdAvatarLightbox',
} = {}) {
    const triggers = document.querySelectorAll(triggerSelector);
    if (!triggers.length) return;

    const profile = document.querySelector('.akd-profile');
    if (!profile?.dataset.profileConfig) return;

    const profileConfig = JSON.parse(profile.dataset.profileConfig);

    const modalApi = useModal();
    const confirmDialog = useConfirmDialog();
    const lightbox = createLightbox(lightboxId);

    // Build the form once; modal.js moves this same node in and out of its body.
    const form = createProfileTemplate(profileConfig).content.firstElementChild;

    const isGoogleAccount = form.dataset.isGoogle === '1';
    const banner = form.querySelector('[data-modal-banner]');

    const fullNameField = form.querySelector('[data-field="fullname"]');
    const usernameField = form.querySelector('[data-field="username"]');
    const currentPasswordField = form.querySelector('[data-field="currentPassword"]');
    const newPasswordField = form.querySelector('[data-field="newPassword"]');
    const confirmPasswordField = form.querySelector('[data-field="confirmPassword"]');

    const fullNameInput = fullNameField.querySelector('input');
    const usernameInput = usernameField.querySelector('input');
    const currentPasswordInput = currentPasswordField?.querySelector('input') ?? null;
    const newPasswordInput = newPasswordField?.querySelector('input') ?? null;
    const confirmPasswordInput = confirmPasswordField?.querySelector('input') ?? null;

    let originalFullName = fullNameInput.value;
    let originalUsername = usernameInput.value;

    // ---- Avatar ----
    const avatarWrap = form.querySelector('.akd-modal__avatar-wrap');
    const avatarPreviewBtn = form.querySelector('[data-avatar-preview]');
    const avatarImg = form.querySelector('[data-avatar-img]');
    const avatarInitialsEl = form.querySelector('[data-avatar-initials]');
    const avatarInput = form.querySelector('[data-avatar-input]');
    const avatarError = form.querySelector('[data-avatar-error]');
    const avatarRemoveBtn = form.querySelector('[data-avatar-remove]');

    let userInitials = avatarWrap.dataset.userInitials || '';
    let hadOriginalAvatar = avatarImg.style.display !== 'none' && !!avatarImg.getAttribute('src');
    let originalAvatarSrc = avatarImg.getAttribute('src') || null;
    let avatarState = 'original'; // 'original' | 'newFile' | 'removed'

    // ---- Password collapse ----
    const passwordToggleBtn = form.querySelector('[data-password-section-toggle]');
    const passwordPanel = form.querySelector('[data-password-panel]');

    function setPasswordSectionOpen(open) {
        if (!passwordToggleBtn || !passwordPanel) return;
        passwordToggleBtn.setAttribute('aria-expanded', String(open));
        passwordPanel.classList.toggle('is-open', open);
        passwordPanel.toggleAttribute('inert', !open);
    }

    passwordToggleBtn?.addEventListener('click', () => {
        const isOpenNow = passwordToggleBtn.getAttribute('aria-expanded') === 'true';
        setPasswordSectionOpen(!isOpenNow);
    });

    // ---- Password show/hide toggles ----
    form.querySelectorAll('[data-password-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = btn.closest('.akd-field__control-wrap')?.querySelector('input');
            if (!input) return;
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            const icon = btn.querySelector('i');
            if (icon) icon.className = showing ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
            btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        });
    });

    function resetPasswordVisibility() {
        form.querySelectorAll('[data-password-toggle]').forEach((btn) => {
            const input = btn.closest('.akd-field__control-wrap')?.querySelector('input');
            if (input) input.type = 'password';
            const icon = btn.querySelector('i');
            if (icon) icon.className = 'fa-solid fa-eye';
            btn.setAttribute('aria-label', 'Show password');
        });
    }

    // ---- Password rules (live) ----
    const ruleItems = form.querySelectorAll('[data-password-rules] li');

    function updatePasswordRules() {
        if (!newPasswordInput) return;
        const value = newPasswordInput.value;
        ruleItems.forEach((li) => {
            const met = PASSWORD_RULES[li.dataset.rule]?.(value);
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

    // ---- Footer (Save / Cancel), built once. The DOM nodes are reused for
    // the lifetime of the page — only the wrapping fragment is rebuilt on
    // each open(). A DocumentFragment's children are moved out, not copied,
    // when it's appended, so reusing the same fragment instance across opens
    // silently empties the footer from the second open onward. ----
    const cancelBtn = document.createElement('button');
    cancelBtn.type = 'button';
    cancelBtn.className = 'akd-btn akd-btn--secondary';
    cancelBtn.textContent = 'Cancel';
    cancelBtn.setAttribute('data-modal-cancel', '');
    cancelBtn.addEventListener('click', () => modalApi.close());

    const saveBtn = document.createElement('button');
    saveBtn.type = 'submit';
    saveBtn.setAttribute('form', form.id);
    saveBtn.className = 'akd-btn akd-btn--primary';
    saveBtn.textContent = 'Save Changes';
    saveBtn.setAttribute('data-modal-save', '');

    function createFooterContent() {
        const fragment = document.createDocumentFragment();
        fragment.append(cancelBtn, saveBtn);
        return fragment;
    }

    // ---- Avatar rendering ----
    function updateModalAvatar(src = null) {
        if (src) {
            avatarImg.src = src;
            setDisplay(avatarImg, true);
            setDisplay(avatarInitialsEl, false);
        } else {
            avatarImg.removeAttribute('src');
            setDisplay(avatarImg, false);
            setDisplay(avatarInitialsEl, true, 'flex');
            avatarInitialsEl.textContent = userInitials;
        }
    }

    function hasAvatarImage() {
        return avatarImg.style.display !== 'none' && !!avatarImg.getAttribute('src');
    }

    function setAvatarError(message) {
        if (!avatarError) return;
        avatarError.textContent = message || '';
        avatarError.classList.toggle('is-visible', !!message);
    }

    function restoreOriginalAvatar() {
        updateModalAvatar(hadOriginalAvatar ? originalAvatarSrc : null);
        avatarInput.value = '';
        avatarState = 'original';
        setDisplay(avatarRemoveBtn, hadOriginalAvatar);
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
            updateModalAvatar(reader.result);
            avatarState = 'newFile';
            setDisplay(avatarRemoveBtn, true);
            updateDirtyState();
        };

        reader.onerror = () => {
            setAvatarError('Could not load that image. Please try another.');
            avatarInput.value = '';
        };

        reader.readAsDataURL(file);
    });

    avatarRemoveBtn?.addEventListener('click', async () => {
        const confirmed = await confirmDialog.ask({
            title: 'Remove profile picture?',
            message: 'This can\u2019t be undone. You can always upload a new one.',
            confirmLabel: 'Remove',
            cancelLabel: 'Cancel',
            destructive: true,
        });

        if (!confirmed) return;

        updateModalAvatar();
        avatarInput.value = '';
        avatarState = hadOriginalAvatar ? 'removed' : 'original';
        setDisplay(avatarRemoveBtn, false);
        updateDirtyState();
    });

    avatarPreviewBtn?.addEventListener('click', () => {
        if (!hasAvatarImage()) return;
        lightbox.open(avatarImg.src);
    });

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
    let isDirty = false;
    let state = 'idle'; // idle | loading | success

    function updateDirtyState() {
        const nameDirty = collapseSpaces(fullNameInput.value) !== originalFullName.trim();
        const usernameDirty = usernameInput.value.trim() !== originalUsername.trim();
        const avatarDirty = avatarState !== 'original';

        const passwordDirty = !isGoogleAccount && (
            (newPasswordInput?.value.length ?? 0) > 0 ||
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

    function resetState() {
        state = 'idle';
        setBanner(null);

        [fullNameField, usernameField, currentPasswordField, newPasswordField, confirmPasswordField]
            .forEach(clearFieldError);

        saveBtn.disabled = true;
        saveBtn.classList.remove('akd-btn--success');
        saveBtn.textContent = 'Save Changes';

        fullNameInput.value = originalFullName;
        usernameInput.value = originalUsername;

        if (currentPasswordInput) currentPasswordInput.value = '';
        if (newPasswordInput) newPasswordInput.value = '';
        if (confirmPasswordInput) confirmPasswordInput.value = '';

        resetPasswordVisibility();
        updatePasswordRules();
        setPasswordSectionOpen(false); // always collapsed on fresh open
        restoreOriginalAvatar();
        isDirty = false;
    }

    function updateUserUI(user) {
        document.querySelectorAll('[data-user-fullname]').forEach((el) => {
            el.textContent = user.fullname;
        });

        document.querySelectorAll('[data-user-username]').forEach((el) => {
            el.textContent = '@' + user.username;
        });

        const initials = getInitials(user.fullname);
        const cacheBust = Date.now();

        document.querySelectorAll('[data-user-avatar-container]').forEach((container) => {
            const img = container.querySelector('[data-user-avatar]');
            const fallback = container.querySelector('[data-user-avatar-initials]');

            if (user.avatar) {
                if (img) {
                    img.src = `${user.avatar}?v=${cacheBust}`;
                    setDisplay(img, true);
                }
                setDisplay(fallback, false);
            } else {
                if (img) {
                    img.removeAttribute('src');
                    setDisplay(img, false);
                }
                if (fallback) {
                    setDisplay(fallback, true, 'flex');
                    fallback.textContent = initials;
                    fallback.style.backgroundColor = user.avatarColor;
                }
            }
        });
    }

    // ---- Submit ----
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (state === 'loading' || !isDirty) return;
        fullNameInput.value = collapseSpaces(fullNameInput.value);
        const checks = [validateFullName(), validateUsername()];

        if (!isGoogleAccount) {
            checks.push(validateCurrentPassword(), validateNewPassword(), validateConfirmPassword());
        }

        if (checks.includes(false)) {
            const erroredField = form.querySelector('.akd-field--error');

            if (erroredField && passwordPanel?.contains(erroredField)) {
                setPasswordSectionOpen(true);
            }

            setBanner('error', 'Please fix the errors below.');
            erroredField?.querySelector('input')?.focus();
            return;
        }

        state = 'loading';
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="akd-spinner"></span> Saving...';
        setBanner(null);

        const formData = new FormData(form);
        formData.set('fullname', fullNameInput.value);
        formData.set('username', usernameInput.value.trim());
        formData.set('removeAvatar', avatarState === 'removed' ? '1' : '0');

        try {
            const result = await api('/member/api/update-profile', {
                method: 'POST',
                body: formData,
            });

            if (result.success === false) {
                state = 'idle';
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Changes';
                setBanner('error', 'Please fix the errors below.');

                Object.entries(result.errors || {}).forEach(([field, message]) => {
                    switch (field) {
                        case 'fullname': setFieldError(fullNameField, message); break;
                        case 'username': setFieldError(usernameField, message); break;
                        case 'currentPassword':
                            setPasswordSectionOpen(true);
                            setFieldError(currentPasswordField, message);
                            break;
                        case 'newPassword':
                            setPasswordSectionOpen(true);
                            setFieldError(newPasswordField, message);
                            break;
                        case 'confirmPassword':
                            setPasswordSectionOpen(true);
                            setFieldError(confirmPasswordField, message);
                            break;
                        case 'avatar': setAvatarError(message); break;
                    }
                });

                return;
            }

            updateUserUI(result.user);

            userInitials = getInitials(result.user.fullname);
            avatarWrap.dataset.userInitials = userInitials;
            avatarWrap.dataset.avatarColor = result.user.avatarColor;
            avatarInitialsEl.textContent = userInitials;
            avatarInitialsEl.style.backgroundColor = result.user.avatarColor;

            originalFullName = result.user.fullname;
            originalUsername = result.user.username;
            fullNameInput.value = result.user.fullname;
            usernameInput.value = result.user.username;

            if (result.user.avatar) {
                originalAvatarSrc = result.user.avatar;
                hadOriginalAvatar = true;
            } else {
                originalAvatarSrc = null;
                hadOriginalAvatar = false;
            }

            updateModalAvatar(result.user.avatar ? `${result.user.avatar}?v=${Date.now()}` : null);
            setDisplay(avatarRemoveBtn, hadOriginalAvatar);

            isDirty = false;
            state = 'success';
            saveBtn.classList.add('akd-btn--success');
            saveBtn.innerHTML = '<i class="fa-solid fa-check"></i> Saved';
            success(result.message);

            setTimeout(() => modalApi.close(), 700);
        } catch (err) {
            state = 'idle';
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Changes';
            handleApiError(err);
        }
    });

    // ---- beforeClose: single source of truth for "can this modal close?" ----
    async function handleBeforeClose() {
        if (state === 'loading') return false;
        if (!isDirty) return true;

        return confirmDialog.ask({
            title: 'Discard changes?',
            message: 'You have unsaved changes. Are you sure you want to discard them?',
            confirmLabel: 'Discard',
            cancelLabel: 'Continue Editing',
            destructive: true,
        });
    }

    // ---- Trigger wiring ----
    function openProfileModal() {
        resetState();
        modalApi.open({
            title: 'Edit Profile',
            subtitle: isGoogleAccount
                ? 'Update your name and username.'
                : 'Update your name, username, avatar and password.',
            content: form,
            footer: createFooterContent(),
            beforeClose: handleBeforeClose,
            initialFocus: fullNameInput,
        });
    }

    triggers.forEach((trigger) => trigger.addEventListener('click', openProfileModal));
}

// ---- Lightbox (reuses the existing .an-lightbox component; not owned by modal.js) ----
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
            e.stopPropagation();
            close();
        }
    }

    function open(src) {
        lastFocusedEl = document.activeElement;
        img.src = src;
        overlay.classList.add('is-open');
        document.addEventListener('keydown', handleKeydown, true);
        closeBtn?.focus();
    }

    function close() {
        overlay.classList.remove('is-open');
        document.removeEventListener('keydown', handleKeydown, true);
        lastFocusedEl?.focus();
        lastFocusedEl = null;
    }

    closeBtn?.addEventListener('click', close);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) close();
    });

    return { open, close, isOpen: () => overlay.classList.contains('is-open') };
}