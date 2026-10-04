// resources/js/member/announcement-unread.js
// Keeps the sidebar "Announcements" badge in sync with the member's server-side
// unread count, over the single /member/stream/events stream.

const LINK_SELECTOR = '.akd-sidebar__nav a.akd-sidebar__link[href="/member/announcements"]';
const STREAM_URL = '/member/stream/events';
const RETRY_BASE_MS = 5000;
const RETRY_MAX_MS = 60000;
const RETRY_LIMIT = 6; // consecutive refusals (401, 204, 5xx) before giving up for this page

const badgeText = (count) => (count > 99 ? '99+' : String(count));

// Same structure akd_render_nav_badge() produces: .akd-sidebar__badge followed
// by a visually-hidden label. Zero removes both.
function renderBadge(link, count) {
    let badge = link.querySelector(':scope > .akd-sidebar__badge');
    let label = link.querySelector(':scope > .visually-hidden');

    if (count <= 0) {
        badge?.remove();
        label?.remove();
        return;
    }

    if (!badge) {
        badge = document.createElement('span');
        badge.className = 'akd-sidebar__badge';
        badge.setAttribute('aria-hidden', 'true');
        link.append(badge);
    }

    if (!label) {
        label = document.createElement('span');
        label.className = 'visually-hidden';
        link.append(label);
    }

    badge.textContent = badgeText(count);
    label.textContent = `${count} new announcement${count === 1 ? '' : 's'}`;
}

// Opens the EventSource; returns a function that closes it. While readyState is
// CONNECTING the browser is already reconnecting by itself (the server ends each
// stream after ~50s). CLOSED means the server refused the connection, so retry
// with backoff, then stop.
function openStream(onUnread) {
    if (!('EventSource' in window)) {
        return () => {};
    }

    let source = null;
    let timer = null;
    let failures = 0;
    let closed = false;

    const connect = () => {
        if (closed) return;
        source = new EventSource(STREAM_URL);

        source.addEventListener('open', () => { failures = 0; });

        source.addEventListener('unread', (event) => {
            failures = 0;

            try {
                onUnread(JSON.parse(event.data));
            } catch {
                // malformed payload: ignore
            }
        });

        source.addEventListener('error', () => {
            if (source.readyState !== EventSource.CLOSED) return;

            source.close();
            if (closed || failures >= RETRY_LIMIT) return;

            failures += 1;
            timer = window.setTimeout(connect, Math.min(RETRY_BASE_MS * 2 ** (failures - 1), RETRY_MAX_MS));
        });
    };

    connect();

    return () => {
        closed = true;
        window.clearTimeout(timer);
        source?.close();
    };
}

export function initAnnouncementUnread() {
    const sidebar = document.getElementById('akdSidebar');
    const link = sidebar?.querySelector(LINK_SELECTOR);
    if (!sidebar || !link) return;

    const userId = sidebar.dataset.userId || 'guest';

    // The server stamped the page with the time its count was read. Anything the
    // stream computed before that moment is older than the page and is ignored,
    // so a late in-flight event can never bring back a badge the page just cleared.
    let appliedAt = Number(sidebar.dataset.unreadAt) || 0;

    function apply(state) {
        const count = Number(state?.count);
        const at = Number(state?.at);

        if (!Number.isFinite(count) || !Number.isFinite(at) || at < appliedAt) return;

        appliedAt = at;
        renderBadge(link, Math.max(0, Math.floor(count)));
    }

    const channel = 'BroadcastChannel' in window
        ? new BroadcastChannel(`akd-unread:${userId}`)
        : null;

    channel?.addEventListener('message', (event) => apply(event.data));

    // One tab owns the connection (Web Locks leader election) and relays to the
    // others. When it closes, the lock passes to the next waiting tab.
    function startShared() {
        let release = () => {};
        const held = new Promise((resolve) => { release = resolve; });
        let cancelled = false;

        navigator.locks.request(`akd-unread-sse:${userId}`, () => {
            if (cancelled) return undefined;

            const close = openStream((state) => {
                apply(state);
                channel.postMessage(state);
            });

            return held.then(close);
        }).catch(() => {});

        return () => {
            cancelled = true;
            release();
        };
    }

    let stop = null;

    function start() {
        if (stop) return;
        stop = channel && navigator.locks ? startShared() : openStream(apply);
    }

    start();

    // Close promptly on navigation; reconnect if the page returns from the bfcache.
    window.addEventListener('pagehide', () => {
        stop?.();
        stop = null;
    });

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) start();
    });
}