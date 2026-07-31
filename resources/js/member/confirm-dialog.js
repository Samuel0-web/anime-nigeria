export function createConfirmDialog({ overlayId, dialogId } = {}) {
    const overlay = document.getElementById(overlayId);
    const dialog = document.getElementById(dialogId);
    if (!overlay || !dialog) return null;

    const titleEl = dialog.querySelector('#akdConfirmTitle');
    const messageEl = dialog.querySelector('#akdConfirmMessage');
    const cancelBtn = dialog.querySelector('[data-confirm-cancel]');
    const acceptBtn = dialog.querySelector('[data-confirm-accept]');

    const focusableSelector = 'button:not([disabled]), [href], input:not([disabled]), [tabindex]:not([tabindex="-1"])';

    let resolvePromise = null;
    let lastFocusedEl = null;

    function getFocusable() {
        return Array.from(dialog.querySelectorAll(focusableSelector));
    }

    function handleKeydown(e) {
        if (e.key === 'Escape') {
            e.preventDefault();
            settle(false);
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

    function open() {
        overlay.classList.add('is-open');
        document.addEventListener('keydown', handleKeydown);
        (acceptBtn || dialog).focus();
    }

    function close() {
        overlay.classList.remove('is-open');
        document.removeEventListener('keydown', handleKeydown);
        lastFocusedEl?.focus();
    }

    function settle(result) {
        close();
        const resolve = resolvePromise;
        resolvePromise = null;
        resolve?.(result);
    }

    cancelBtn?.addEventListener('click', () => settle(false));
    acceptBtn?.addEventListener('click', () => settle(true));

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) settle(false);
    });

    function ask({ title, message, confirmLabel = 'Confirm', cancelLabel = 'Cancel', destructive = false }) {
        lastFocusedEl = document.activeElement;
        titleEl.textContent = title;
        messageEl.textContent = message;
        acceptBtn.textContent = confirmLabel;
        cancelBtn.textContent = cancelLabel;
        acceptBtn.classList.toggle('akd-btn--danger', destructive);
        acceptBtn.classList.toggle('akd-btn--primary', !destructive);

        open();

        return new Promise((resolve) => {
            resolvePromise = resolve;
        });
    }

    function isOpen() {
        return overlay.classList.contains('is-open');
    }

    return { ask, isOpen };
}