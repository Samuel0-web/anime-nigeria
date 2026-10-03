// resources/js/admin/form-utils.js
// Small form helpers shared by admin pages.

const CONTROL_SELECTOR = 'input:not([type="radio"]):not([type="checkbox"]), textarea';

export function collapseSpaces(value) {
    return String(value ?? '').trim().replace(/\s+/g, ' ');
}

export function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    })[character]);
}

export function setFieldError(field, message) {
    if (!field) return;
    field.classList.add('akd-admin-field--error');
    field.querySelector(CONTROL_SELECTOR)?.setAttribute('aria-invalid', 'true');
    const msg = field.querySelector('[data-field-error]');
    if (msg) msg.textContent = message;
}

export function clearFieldError(field) {
    if (!field) return;
    field.classList.remove('akd-admin-field--error');
    field.querySelector(CONTROL_SELECTOR)?.removeAttribute('aria-invalid');
    const msg = field.querySelector('[data-field-error]');
    if (msg) msg.textContent = '';
}

export function hasFieldError(field) {
    return field?.classList.contains('akd-admin-field--error') ?? false;
}

export function focusFirstError(scope) {
    const field = scope.querySelector('.akd-admin-field--error');

    field?.querySelector(
        `${CONTROL_SELECTOR}, input[type="radio"]:checked, input[type="radio"]:not(:disabled)`
    )?.focus();
}