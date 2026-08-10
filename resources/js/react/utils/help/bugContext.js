function detectBrowser(userAgent) {
    if (/edg\//i.test(userAgent)) return "Edge";
    if (/chrome\//i.test(userAgent) && !/edg\//i.test(userAgent)) return "Chrome";
    if (/firefox\//i.test(userAgent)) return "Firefox";
    if (/safari\//i.test(userAgent) && !/chrome\//i.test(userAgent)) return "Safari";
    return "Unknown";
}

function detectOS(userAgent) {
    if (/windows/i.test(userAgent)) return "Windows";
    if (/mac os/i.test(userAgent)) return "macOS";
    if (/android/i.test(userAgent)) return "Android";
    if (/iphone|ipad|ios/i.test(userAgent)) return "iOS";
    if (/linux/i.test(userAgent)) return "Linux";
    return "Unknown";
}

/**
 * Assembles context the application already knows, so the user never has
 * to type it manually. `accountId` reads from `window.AKD_USER` if the
 * layout exposes the authenticated user there — adjust once a real
 * bug-report endpoint exists and the session shape is confirmed.
 */
export function getAutoBugContext() {
    const userAgent = window.navigator.userAgent;

    return {
        accountId: window.AKD_USER?.id ?? null,
        url: window.location.href,
        browser: detectBrowser(userAgent),
        os: detectOS(userAgent),
        device: /mobile/i.test(userAgent) ? "Mobile" : "Desktop",
        screen: {
            width: window.screen.width,
            height: window.screen.height,
        },
        userAgent,
        timestamp: new Date().toISOString(),
    };
}