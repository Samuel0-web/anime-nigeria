let instance = null;

export function useConfirmDialog() {
    if (instance) {
        return instance;
    }

    let overlay = document.getElementById("akdConfirmOverlay");

    if (!overlay) {
        overlay = document.createElement("div");
        overlay.id = "akdConfirmOverlay";
        overlay.className = "akd-confirm-overlay";

        overlay.innerHTML = `
            <div class="akd-confirm" id="akdConfirmDialog" role="alertdialog" aria-modal="true"
                aria-labelledby="akdConfirmTitle" aria-describedby="akdConfirmMessage"
            >
                <h3 class="akd-confirm__title" id="akdConfirmTitle"></h3>
                <p class="akd-confirm__message" id="akdConfirmMessage"></p>

                <div class="akd-confirm__actions">
                    <button type="button" class="akd-btn akd-btn--secondary"
                        data-confirm-cancel
                    ></button>

                    <button type="button" class="akd-btn akd-btn--danger"
                        data-confirm-accept
                    ></button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
    }

    const dialog = overlay.querySelector(".akd-confirm");
    const titleEl = dialog.querySelector("#akdConfirmTitle");
    const messageEl = dialog.querySelector("#akdConfirmMessage");
    const cancelBtn = dialog.querySelector("[data-confirm-cancel]");
    const acceptBtn = dialog.querySelector("[data-confirm-accept]");

    const focusableSelector =
        'button:not([disabled]), [href], input:not([disabled]), [tabindex]:not([tabindex="-1"])';

    let resolvePromise = null;
    let lastFocusedEl = null;

    function getFocusable() {
        return Array.from(dialog.querySelectorAll(focusableSelector));
    }

    // Registered with capture:true so this fires before any modal's own
    // (bubble-phase) keydown listener underneath it — stopPropagation keeps
    // that listener from also reacting, so whichever layer is on top always
    // owns Escape/Tab, regardless of what's stacked below it.
    function handleKeydown(e) {
        if (e.key === "Escape") {
            e.preventDefault();
            e.stopPropagation();
            settle(false);
            return;
        }

        if (e.key === "Tab") {
            e.stopPropagation();
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
        overlay.classList.add("is-open");
        document.addEventListener("keydown", handleKeydown, true);
        (acceptBtn || dialog).focus();
    }

    function close() {
        overlay.classList.remove("is-open");
        document.removeEventListener("keydown", handleKeydown, true);
        lastFocusedEl?.focus();
    }

    function settle(result) {
        close();
        const resolve = resolvePromise;
        resolvePromise = null;
        resolve?.(result);
    }

    cancelBtn.addEventListener("click", () => settle(false));
    acceptBtn.addEventListener("click", () => settle(true));

    overlay.addEventListener("click", (e) => {
        if (e.target === overlay) {
            settle(false);
        }
    });

    function ask({title, message, confirmLabel = "Confirm", cancelLabel = "Cancel",
        destructive = false,
    }) {
        lastFocusedEl = document.activeElement;
        titleEl.textContent = title;
        messageEl.textContent = message;
        acceptBtn.textContent = confirmLabel;
        cancelBtn.textContent = cancelLabel;
        acceptBtn.classList.toggle("akd-btn--danger", destructive);
        acceptBtn.classList.toggle("akd-btn--primary", !destructive);
        open();

        return new Promise((resolve) => {
            resolvePromise = resolve;
        });
    }

    function isOpen() {
        return overlay.classList.contains("is-open");
    }

    instance = { ask, isOpen, };
    return instance;
}