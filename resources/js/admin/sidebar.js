// resources/js/admin/sidebar.js
// Admin shell behaviour: full-screen mobile sidebar (same swipe model as the
// member sidebar), hideable desktop sidebar, recursive nav accordion, and the
// topbar dropdown menus.

// Tunable gesture constants (identical to the member sidebar)
const DIRECTION_THRESHOLD_PX = 12; // movement required before committing to a horizontal drag
const OPEN_COMPLETE_RATIO = 0.4;   // fraction of sidebar width that counts as "dragged far enough"
const FLICK_VELOCITY = 0.35;       // px/ms, a deliberate fast swipe completes regardless of distance

const DESKTOP_BP = 1024;
const HIDDEN_CLASS = 'akd-admin-sidebar-hidden';

function getTranslateX(el) {
    const transform = window.getComputedStyle(el).transform;
    if (!transform || transform === 'none') return 0;

    if (transform.startsWith('matrix3d')) {
        const parts = transform.slice(9, -1).split(', ');
        return parseFloat(parts[12]) || 0;
    }

    const parts = transform.slice(7, -1).split(', ');
    return parseFloat(parts[4]) || 0;
}

// The lightbox owns touch interaction entirely while it is open.
function isLightboxOpen() {
    return document.querySelector('.an-lightbox.is-open') !== null;
}

// Walks up from the touch target looking for an element that owns native
// horizontal scrolling (e.g. a wide admin table wrapper), so the sidebar
// swipe never steals that gesture.
function isInsideHorizontalScroller(target) {
    let el = target;

    while (el && el !== document.body && el.nodeType === 1) {
        if (el.hasAttribute('data-no-sidebar-swipe')) return true;

        if (el.scrollWidth > el.clientWidth + 1) {
            const overflowX = window.getComputedStyle(el).overflowX;
            if (overflowX === 'auto' || overflowX === 'scroll') return true;
        }

        el = el.parentElement;
    }

    return false;
}

// ---- Topbar dropdowns (notifications, account) ----
// Returns closeAll() so the sidebar code can dismiss menus when it opens.
function initTopbarMenus() {
    const menus = Array.from(document.querySelectorAll('[data-admin-menu]'));
    if (!menus.length) return () => {};

    function closeMenu(menu) {
        menu.querySelector('[data-admin-menu-panel]')?.classList.remove('is-open');
        menu.querySelector('[data-admin-menu-trigger]')?.setAttribute('aria-expanded', 'false');
    }

    function closeAll(except = null) {
        menus.forEach((menu) => {
            if (menu !== except) closeMenu(menu);
        });
    }

    menus.forEach((menu) => {
        const trigger = menu.querySelector('[data-admin-menu-trigger]');
        const panel = menu.querySelector('[data-admin-menu-panel]');
        if (!trigger || !panel) return;

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const willOpen = !panel.classList.contains('is-open');
            closeAll(menu);
            panel.classList.toggle('is-open', willOpen);
            trigger.setAttribute('aria-expanded', String(willOpen));
        });
    });

    document.addEventListener('click', (e) => {
        menus.forEach((menu) => {
            if (!menu.contains(e.target)) closeMenu(menu);
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAll();
    });

    return closeAll;
}

export function initAdminSidebar({ layoutId, sidebarId, toggleBtnId, closeBtnId, overlayId }) {
    const layout = document.getElementById(layoutId);
    const sidebar = document.getElementById(sidebarId);
    const toggleBtn = document.getElementById(toggleBtnId);
    const closeBtn = document.getElementById(closeBtnId);
    const overlay = document.getElementById(overlayId);
    if (!layout || !sidebar) return;

    const sidebarScroll = sidebar.querySelector('.akd-admin-sidebar__scroll');
    const userId = sidebar.dataset.userId || 'guest';
    const root = document.documentElement;
    const HIDDEN_KEY = `akd-admin-sidebar-hidden:${userId}`;

    const closeMenus = initTopbarMenus();

    const isDesktop = () => window.innerWidth >= DESKTOP_BP;
    const isHidden = () => root.classList.contains(HIDDEN_CLASS);

    // The single toggle button means "open/close menu" on mobile and
    // "show/hide sidebar" on desktop. Keep its ARIA state honest for both.
    function syncToggle() {
        if (!toggleBtn) return;

        if (isDesktop()) {
            const visible = !isHidden();
            toggleBtn.setAttribute('aria-expanded', String(visible));
            toggleBtn.setAttribute('aria-label', visible ? 'Hide sidebar' : 'Show sidebar');
        } else {
            const open = sidebar.classList.contains('is-open');
            toggleBtn.setAttribute('aria-expanded', String(open));
            toggleBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        }
    }

    // ---- Sidebar scroll: persist per user, and make sure the active item is reachable ----
    if (sidebarScroll) {
        const SCROLL_KEY = `akd-admin-sidebar-scroll:${userId}`;
        const savedScroll = sessionStorage.getItem(SCROLL_KEY);

        requestAnimationFrame(() => {
            if (savedScroll !== null) {
                sidebarScroll.scrollTop = parseInt(savedScroll, 10) || 0;
            }

            const active = sidebar.querySelector('.akd-admin-nav__item--active');
            const target = active?.querySelector(':scope > .akd-admin-nav__row') ?? active;
            if (!target) return;

            const box = sidebarScroll.getBoundingClientRect();
            const rect = target.getBoundingClientRect();
            const margin = 24;

            if (rect.top < box.top + margin || rect.bottom > box.bottom - margin) {
                sidebarScroll.scrollTop += rect.top - box.top - (box.height - rect.height) / 2;
            }
        });

        sidebarScroll.addEventListener('scroll', () => {
            sessionStorage.setItem(SCROLL_KEY, String(sidebarScroll.scrollTop));
        }, { passive: true });
    }

    // ---- Global swipe-to-open / drag-to-close (mobile only), same model as the member side ----
    const gesture = {
        pending: false, active: false, mode: null,
        startX: 0, startY: 0, lastX: 0, lastT: 0,
        velocity: 0, baseTranslate: 0, width: 0, progress: null,
    };

    function decideOpen(mode, progress, velocity) {
        if (mode === 'open') {
            return progress >= OPEN_COMPLETE_RATIO || velocity > FLICK_VELOCITY;
        }
        const closedEnough = progress <= (1 - OPEN_COMPLETE_RATIO) || velocity < -FLICK_VELOCITY;
        return !closedEnough;
    }

    function resetGestureState() {
        gesture.pending = false;
        gesture.active = false;
        gesture.mode = null;
        gesture.velocity = 0;
        gesture.progress = null;
    }

    function updateFromTouch(touch, timeStamp) {
        const dt = timeStamp - gesture.lastT;
        if (dt > 0) {
            const instVelocity = (touch.clientX - gesture.lastX) / dt;
            gesture.velocity = gesture.velocity * 0.7 + instVelocity * 0.3;
        }
        gesture.lastX = touch.clientX;
        gesture.lastT = timeStamp;
        const dx = touch.clientX - gesture.startX;
        const width = gesture.width || sidebar.offsetWidth;
        const translatePx = Math.min(0, Math.max(-width, gesture.baseTranslate + dx));
        sidebar.style.transform = `translateX(${translatePx}px)`;
        const progress = 1 + translatePx / width;
        gesture.progress = progress;
        if (overlay) overlay.style.opacity = String(progress);
    }

    function settleDrag(shouldOpen) {
        sidebar.classList.remove('is-dragging');
        overlay?.classList.remove('is-dragging');
        void sidebar.offsetHeight;
        if (shouldOpen) { openSidebar(); } else { closeSidebar(); }
        requestAnimationFrame(() => {
            sidebar.style.transform = '';
            if (overlay) overlay.style.opacity = '';
        });
    }

    function onTouchStart(e) {
        if (window.innerWidth >= DESKTOP_BP) return;
        if (isLightboxOpen()) return;
        if (e.touches.length !== 1) return;
        if (gesture.pending || gesture.active) return;
        if (isInsideHorizontalScroller(e.target)) return;
        const touch = e.touches[0];
        const isOpen = sidebar.classList.contains('is-open');

        if (isOpen) {
            if (!sidebar.contains(e.target)) return;
            gesture.mode = 'close';
        } else {
            gesture.mode = 'open';
        }

        gesture.pending = true;
        gesture.active = false;
        gesture.startX = touch.clientX;
        gesture.startY = touch.clientY;
        gesture.lastX = touch.clientX;
        gesture.lastT = e.timeStamp;
        gesture.velocity = 0;
        gesture.progress = null;
    }

    function onTouchMove(e) {
        if (isLightboxOpen()) {
            if (gesture.pending || gesture.active) {
                sidebar.classList.remove('is-dragging');
                overlay?.classList.remove('is-dragging');
                sidebar.style.transform = '';
                if (overlay) overlay.style.opacity = '';
                resetGestureState();
            }

            return;
        }

        if (!gesture.pending && !gesture.active) return;
        const touch = e.touches[0];
        if (!touch) return;

        if (gesture.active) {
            if (e.cancelable) {
                e.preventDefault();
            }

            updateFromTouch(touch, e.timeStamp);
            return;
        }

        const dx = touch.clientX - gesture.startX;
        const dy = touch.clientY - gesture.startY;

        if (Math.abs(dx) < DIRECTION_THRESHOLD_PX && Math.abs(dy) < DIRECTION_THRESHOLD_PX) return;
        if (Math.abs(dy) >= Math.abs(dx)) { resetGestureState(); return; }
        if (gesture.mode === 'open' && dx <= 0) { resetGestureState(); return; }
        if (gesture.mode === 'close' && dx >= 0) { resetGestureState(); return; }

        gesture.active = true;
        gesture.pending = false;
        gesture.width = sidebar.offsetWidth;
        gesture.baseTranslate = getTranslateX(sidebar);
        sidebar.classList.add('is-dragging');
        overlay?.classList.add('is-dragging');
        document.body.style.overflow = 'hidden';

        if (e.cancelable) {
            e.preventDefault();
        }

        updateFromTouch(touch, e.timeStamp);
    }

    function onTouchEnd() {
        if (gesture.active) {
            const progress = gesture.progress ?? (gesture.mode === 'open' ? 0 : 1);
            settleDrag(decideOpen(gesture.mode, progress, gesture.velocity));
        }
        resetGestureState();
    }

    document.addEventListener('touchstart', onTouchStart, { passive: true });
    document.addEventListener('touchmove', onTouchMove, { passive: false });
    document.addEventListener('touchend', onTouchEnd, { passive: true });
    document.addEventListener('touchcancel', onTouchEnd, { passive: true });

    // ---- Mobile open / close ----
    function openSidebar() {
        closeMenus();
        sidebar.classList.add('is-open');
        overlay?.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        syncToggle();
    }

    function closeSidebar() {
        sidebar.classList.remove('is-open');
        overlay?.classList.remove('is-open');
        document.body.style.overflow = '';
        syncToggle();
    }

    // ---- Desktop hide / show ----
    function setHidden(next) {
        if (!isDesktop()) return;
        closeMenus();
        root.classList.toggle(HIDDEN_CLASS, next);
        syncToggle();

        try {
            localStorage.setItem(HIDDEN_KEY, next ? '1' : '0');
        } catch (e) {}
    }

    toggleBtn?.addEventListener('click', (e) => {
        e.stopPropagation();

        if (isDesktop()) {
            setHidden(!isHidden());
            return;
        }

        sidebar.classList.contains('is-open') ? closeSidebar() : openSidebar();
    });

    closeBtn?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // ---- Recursive accordion: one handler for every depth ----
    sidebar.querySelectorAll('.akd-admin-nav__expand-btn').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const panel = document.getElementById(btn.getAttribute('aria-controls'));
            if (!panel) return;

            const isOpen = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', String(!isOpen));
            panel.classList.toggle('is-open', !isOpen);
        });
    });

    window.addEventListener('resize', () => {
        if (isDesktop()) {
            if (sidebar.classList.contains('is-open')) closeSidebar();
            resetGestureState();
            sidebar.classList.remove('is-dragging');
            overlay?.classList.remove('is-dragging');
            sidebar.style.transform = '';
            if (overlay) overlay.style.opacity = '';
            document.body.style.overflow = '';
        } else {
            closeMenus();
        }

        syncToggle();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        closeSidebar();
    });

    syncToggle();
}