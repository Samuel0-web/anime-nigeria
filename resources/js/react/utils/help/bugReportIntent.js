const MAX_PAGE_LENGTH = 500;

/**
 * Accepts only internal paths ("/x", "/x?y=z"). Anything else returns "".
 */
export function sanitizeFromParam(raw) {
    if (typeof raw !== "string") return "";

    const value = raw.trim();

    if (!value || value.length > MAX_PAGE_LENGTH) return "";
    if (!value.startsWith("/") || value.startsWith("//")) return "";
    if (value.includes("\\")) return "";
    if (/[\u0000-\u001F\u007F\uFFFD]/.test(value)) return ""; // \uFFFD = failed decode

    return value;
}

/**
 * Reads the requested initial state from the URL.
 * URLSearchParams decodes exactly once. Never throws.
 */
export function readBugReportIntent(search = window.location.search) {
    try {
        const params = new URLSearchParams(search);

        if (params.get("report") !== "bug") {
            return { open: false, page: "" };
        }

        const rawFrom = params.get("from");
        const page = sanitizeFromParam(rawFrom);

        if (rawFrom && !page) {
            console.warn("Ignored invalid bug report origin:", rawFrom);
        }

        return { open: true, page };
    } catch (error) {
        return { open: false, page: "" };
    }
}

/**
 * Removes ?report=bug&from=... once handled, so a refresh doesn't reopen
 * the panel. Uses replaceState, so no extra history entry is created.
 */
export function clearBugReportParams() {
    try {
        const url = new URL(window.location.href);

        if (url.searchParams.get("report") !== "bug") return;

        url.searchParams.delete("report");
        url.searchParams.delete("from");

        window.history.replaceState(
            window.history.state,
            "",
            url.pathname + url.search + url.hash
        );
    } catch (error) {
        // Non-critical; leave the URL as is.
    }
}