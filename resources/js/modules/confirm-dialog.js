import { loadPhpComponent } from './load-php-component.js';

let instance = null;

export function useConfirmDialog() {
    if (instance) {
        return instance;
    }

    let overlay = null;
    let dialog = null;
    let titleEl = null;
    let messageEl = null;
    let cancelBtn = null;
    let acceptBtn = null;
    let componentPromise = null;
    let isAsking = false;

    const focusableSelector = [
        "button:not([disabled])",
        "[href]",
        "input:not([disabled])",
        "select:not([disabled])",
        "textarea:not([disabled])",
        "[tabindex]:not([tabindex='-1'])",
    ].join(",");

    let resolvePromise = null;
    let lastFocusedEl = null;
    let previousBodyOverflow = "";
    let previousBodyPaddingRight = "";

    function ensureComponent() {
        if (overlay) return Promise.resolve(overlay);
        if (componentPromise) return componentPromise;

        componentPromise = loadPhpComponent('/components/confirm-dialog').then((component) => {
            overlay = component;
            dialog = overlay.querySelector(".akd-confirm");
            titleEl = overlay.querySelector("#akdConfirmTitle");
            messageEl = overlay.querySelector("#akdConfirmMessage");
            cancelBtn = overlay.querySelector("[data-confirm-cancel]");
            acceptBtn = overlay.querySelector("[data-confirm-accept]");
            document.body.appendChild(overlay);

            cancelBtn.addEventListener("click", () => settle(false));
            acceptBtn.addEventListener("click", () => settle(true));
            overlay.addEventListener("click", (event) => {
                if (event.target === overlay) settle(false);
            });

            return overlay;
        }).finally(() => {
            componentPromise = null;
        });

        return componentPromise;
    }

    function getFocusable() {
        return Array.from(dialog.querySelectorAll(focusableSelector)).filter((el) => {
            return el.offsetParent !== null;
        });
    }

    function handleKeydown(event) {
        if (!isOpen()) {
            return;
        }

        if (event.key === "Escape") {
            event.preventDefault();
            event.stopPropagation();
            settle(false);
            return;
        }

        if (event.key !== "Tab") {
            return;
        }

        const focusables = getFocusable();

        if (!focusables.length) {
            event.preventDefault();
            dialog.focus();
            return;
        }

        const first = focusables[0];
        const last = focusables[focusables.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
            return;
        }

        if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    function lockBodyScroll() {
        previousBodyOverflow = document.body.style.overflow;
        previousBodyPaddingRight = document.body.style.paddingRight;
        const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
        document.body.style.overflow = "hidden";

        // Prevent the page from shifting horizontally when the scrollbar disappears.
        if (scrollbarWidth > 0) {
            document.body.style.paddingRight = `${scrollbarWidth}px`;
        }
    }

    function unlockBodyScroll() {
        document.body.style.overflow = previousBodyOverflow;
        document.body.style.paddingRight = previousBodyPaddingRight;
        previousBodyOverflow = "";
        previousBodyPaddingRight = "";
    }

    function open() {
        lockBodyScroll();
        overlay.classList.add("is-open");
        document.addEventListener("keydown", handleKeydown, true);

        requestAnimationFrame(() => {
            if (!isOpen()) {
                return;
            }

            acceptBtn.focus();
        });
    }

    function close() {
        overlay.classList.remove("is-open");
        document.removeEventListener("keydown", handleKeydown, true);
        unlockBodyScroll();

        if (lastFocusedEl && typeof lastFocusedEl.focus === "function" &&
            document.contains(lastFocusedEl)
        ) {
            lastFocusedEl.focus();
        }

        lastFocusedEl = null;
    }

    function settle(result) {
        const resolve = resolvePromise;
        resolvePromise = null;
        close();
        resolve?.(result);
    }

    function ask({
        title,
        message,
        confirmLabel = "Confirm",
        cancelLabel = "Cancel",
        destructive = false,
    }) {
        // Prevent two unresolved dialogs from sharing the same resolver.
        if (isAsking || isOpen()) {
            return Promise.resolve(false);
        }

        isAsking = true;
        lastFocusedEl = document.activeElement;
        return ensureComponent().then(() => {
            titleEl.textContent = title ?? "";
            messageEl.textContent = message ?? "";
            acceptBtn.textContent = confirmLabel;
            cancelBtn.textContent = cancelLabel;
            acceptBtn.classList.toggle("akd-btn--danger", destructive);
            acceptBtn.classList.toggle("akd-btn--primary", !destructive);

            return new Promise((resolve) => {
                resolvePromise = resolve;
                isAsking = false;
                open();
            });
        }).catch((error) => {
            isAsking = false;
            lastFocusedEl = null;
            throw error;
        });
    }

    function isOpen() {
        return overlay?.classList.contains("is-open") ?? false;
    }

    instance = { ask, isOpen, };
    return instance;
}