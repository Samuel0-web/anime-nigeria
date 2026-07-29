export function initPreloader() {
    const preloader = document.getElementById("preloader");

    if (!preloader) {
        return;
    }

    const body = document.body;
    let isVisible = true;

    const lockScroll = () => {
        body.style.overflow = "hidden";
    };

    const unlockScroll = () => {
        body.style.overflow = "";
    };

    const showPreloader = () => {
        if (isVisible) return;
        isVisible = true;
        lockScroll();
        preloader.classList.remove("is-hidden");
        preloader.setAttribute("aria-hidden", "false");
    };

    const hidePreloader = () => {
        if (!isVisible) return;
        isVisible = false;
        preloader.classList.add("is-hidden");
        preloader.setAttribute("aria-hidden", "true");

        const remove = () => {
            unlockScroll();
            preloader.remove();
        };

        preloader.addEventListener("transitionend", remove, { once: true });

        // Fallback in case transitionend never fires.
        setTimeout(remove, 500);
    };

    lockScroll();

    // setTimeout(() => {
    //     hidePreloader()
    // }, 5000)

    // ------------------------------------------------------------------
    // Real behaviour
    // ------------------------------------------------------------------
    window.addEventListener("load", () => {
        hidePreloader();
    });

    window.addEventListener("pageshow", (event) => {
        if (event.persisted) {
            hidePreloader();
        }
    });

    document.addEventListener("click", (event) => {
        const link = event.target.closest("a");

        if (!link) return;

        if (link.target === "_blank" || link.hasAttribute("download") ||
            link.href.startsWith("javascript:") || link.origin !== window.location.origin ||
            event.ctrlKey || event.metaKey || event.shiftKey || event.altKey
        ) {
            return;
        }

        showPreloader();
    });

    document.addEventListener("submit", () => {
        showPreloader();
    });
}