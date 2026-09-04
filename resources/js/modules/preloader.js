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
    const preloaderImage = preloader.querySelector(".preloader__wheel");

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

        // link.href is always the browser-resolved absolute URL, so this
        // check works the same for a bare "#voting" href and for a fully
        // qualified same-page URL with a hash. Comparing pathname and
        // search (and requiring an actual hash) against the current
        // location identifies same-document navigation: the browser
        // performs an in-page scroll for these, never a new document
        // load, so there is no navigation for the preloader to wait out.
        // A hash link that points at a different pathname is still a
        // real route and still shows the preloader normally.
        const targetUrl = new URL(link.href, window.location.href);

        const isSameDocumentNavigation = targetUrl.hash !== "" &&
            targetUrl.pathname === window.location.pathname &&
            targetUrl.search === window.location.search;

        if (isSameDocumentNavigation) {
            return;
        }

        showPreloader();
    });

    document.addEventListener("submit", (event) => {
        // A form handled entirely in JavaScript (validation, a
        // confirmation dialog, a mock submission, and so on) calls
        // event.preventDefault() in its own submit handler before this
        // listener runs, since handlers on the form itself fire first
        // during the bubble phase. In that case there is no page load
        // coming, so showing the preloader here would lock scroll with
        // nothing left to ever clear it. Forms that genuinely navigate
        // never call preventDefault(), so this keeps the preloader for
        // them unchanged.
        if (event.defaultPrevented) {
            return;
        }

        showPreloader();
    });
}