// Tunable gesture constants
const DIRECTION_THRESHOLD_PX = 12; // movement required before committing to a horizontal drag
const OPEN_COMPLETE_RATIO = 0.4;   // fraction of sidebar width that counts as "dragged far enough"
const FLICK_VELOCITY = 0.35;       // px/ms — a deliberate fast swipe completes regardless of distance

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

// Walks up from the initial touch target looking for an element that owns
// native horizontal scrolling — e.g. .akd-dash-rail, which is a plain
// overflow-x: auto strip below the desktop breakpoint. Deliberately stops
// at `boundary` (the layout root) rather than climbing into <body>/<html>:
// we're identifying a specific scroll owner, not asking "does the page
// have horizontal overflow" (which the spec explicitly rules out).
// This generalizes to any future section built the same way, without
// needing to hardcode carousel-specific class names.
function isInsideHorizontalScroller(target) {
    let el = target;

    while (el && el !== document.body && el.nodeType === 1) {
        if (el.scrollWidth > el.clientWidth + 1) {
            const overflowX = window.getComputedStyle(el).overflowX;
            if (overflowX === 'auto' || overflowX === 'scroll') return true;
        }

        el = el.parentElement;
    }

    return false;
}

export function initSidebar({layoutId, sidebarId, toggleBtnId, closeBtnId, overlayId,
    profileBtnId, dropdownId,
}) {
    const layout = document.getElementById(layoutId);
    const sidebar = document.getElementById(sidebarId);
    const toggleBtn = document.getElementById(toggleBtnId);
    const closeBtn = document.getElementById(closeBtnId);
    const overlay = document.getElementById(overlayId);
    const profileBtn = document.getElementById(profileBtnId);
    const dropdown = document.getElementById(dropdownId);
    if (!layout || !sidebar) return;
    const sidebarScroll = sidebar.querySelector('.akd-sidebar__scroll');

    if (sidebarScroll) {
        const userId = sidebar.dataset.userId;

        if (userId) {
            const SIDEBAR_SCROLL_KEY = `akd-sidebar-scroll:${userId}`;

            // Restore this user's previous sidebar position.
            const savedScroll = sessionStorage.getItem(SIDEBAR_SCROLL_KEY);

            if (savedScroll !== null) {
                requestAnimationFrame(() => {
                    sidebarScroll.scrollTop = parseInt(savedScroll, 10) || 0;
                });
            }

            // Save this user's sidebar position.
            sidebarScroll.addEventListener('scroll', () => {
                sessionStorage.setItem(SIDEBAR_SCROLL_KEY, String(sidebarScroll.scrollTop));
            }, { passive: true });
        }
    }

        // ---- Global swipe-to-open / drag-to-close ----
    const gesture = {
        pending: false,   // touch started validly, waiting for direction commit
        active: false,    // committed — sidebar is now following the finger
        mode: null,       // 'open' | 'close'
        startX: 0, startY: 0, lastX: 0, lastT: 0,
        velocity: 0,      // px/ms, smoothed, positive = rightward
        baseTranslate: 0, // sidebar's actual translateX at the moment of commit
        width: 0,
        progress: null,   // 0 = closed .. 1 = open
    };

    function decideOpen(mode, progress, velocity) {
        if (mode === 'open') {
            return progress >= OPEN_COMPLETE_RATIO || velocity > FLICK_VELOCITY;
        }

        const closedEnough = progress <= (1 - OPEN_COMPLETE_RATIO) || 
            velocity < -FLICK_VELOCITY;

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
            gesture.velocity = gesture.velocity * 0.7 + instVelocity * 0.3; // light smoothing
        }

        gesture.lastX = touch.clientX;
        gesture.lastT = timeStamp;
        const dx = touch.clientX - gesture.startX;
        const width = gesture.width || sidebar.offsetWidth;
        const translatePx = Math.min(0, Math.max(-width, gesture.baseTranslate + dx));
        sidebar.style.transform = `translateX(${translatePx}px)`;

        // Overlay opacity is derived from this exact same progress value —
        // sidebar and overlay can never drift apart.
        const progress = 1 + translatePx / width;
        gesture.progress = progress;
        if (overlay) overlay.style.opacity = String(progress);
    }

    function settleDrag(shouldOpen) {
        sidebar.classList.remove('is-dragging');
        overlay?.classList.remove('is-dragging');

        // Commit the "no transition" state before handing off, so the browser
        // treats the next change as a fresh transition start.
        void sidebar.offsetHeight;

        if (shouldOpen) {
            openSidebar();
        } else {
            closeSidebar();
        }

        // openSidebar/closeSidebar only toggle classes — the inline transform
        // set during the drag is still winning the cascade, so nothing has
        // moved yet. Clearing it next frame hands control to the class-driven
        // target, which is what actually animates from "where the finger left
        // it" to the resolved open/closed state.
        requestAnimationFrame(() => {
            sidebar.style.transform = '';
            if (overlay) overlay.style.opacity = '';
        });
    }

    function onTouchStart(e) {
        if (window.innerWidth >= 1024) return;
        if (e.touches.length !== 1) return;
        if (gesture.pending || gesture.active) return;

        // Horizontal-scroll/carousel content always wins. Ownership is decided
        // once, from the initial touch target, and never re-evaluated mid-gesture —
        // if the carousel owns it, the sidebar does nothing for this touch sequence.
        if (isInsideHorizontalScroller(e.target)) return;
        const touch = e.touches[0];
        const isOpen = sidebar.classList.contains('is-open');

        if (isOpen) {
            if (!sidebar.contains(e.target)) return;
            gesture.mode = 'close';
        } else {
            // No edge requirement — a swipe-to-open gesture may begin anywhere
            // on normal page content.
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
        if (!gesture.pending && !gesture.active) return;
        const touch = e.touches[0];
        if (!touch) return;

        if (gesture.active) {
            e.preventDefault();
            updateFromTouch(touch, e.timeStamp);
            return;
        }

        const dx = touch.clientX - gesture.startX;
        const dy = touch.clientY - gesture.startY;

        if (Math.abs(dx) < DIRECTION_THRESHOLD_PX && Math.abs(dy) < DIRECTION_THRESHOLD_PX) {
            return; // not enough movement yet — don't preventDefault, let native handling stay live
        }

        if (Math.abs(dy) >= Math.abs(dx)) {
            resetGestureState(); // vertical wins — hand off to normal scrolling
            return;
        }

        if (gesture.mode === 'open' && dx <= 0) { resetGestureState(); return; }
        if (gesture.mode === 'close' && dx >= 0) { resetGestureState(); return; }

        // Direction confirmed — commit and take over.
        gesture.active = true;
        gesture.pending = false;
        gesture.width = sidebar.offsetWidth;
        gesture.baseTranslate = getTranslateX(sidebar);
        sidebar.classList.add('is-dragging');
        overlay?.classList.add('is-dragging');
        document.body.style.overflow = 'hidden';
        e.preventDefault();
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

    const openSidebar = () => {
        sidebar.classList.add('is-open');
        overlay?.classList.add('is-open');
        toggleBtn?.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    };

    const closeSidebar = () => {
        sidebar.classList.remove('is-open');
        overlay?.classList.remove('is-open');
        toggleBtn?.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    };

    toggleBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        sidebar.classList.contains('is-open') ? closeSidebar() : openSidebar();
    });

    closeBtn?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024 && sidebar.classList.contains('is-open')) {
            closeSidebar();
        }

        if (window.innerWidth >= 1024) {
            // Abort any in-flight gesture and strip inline styles — otherwise a
            // stale transform/opacity would displace the permanent desktop sidebar.
            resetGestureState();
            sidebar.classList.remove('is-dragging');
            overlay?.classList.remove('is-dragging');
            sidebar.style.transform = '';
            if (overlay) overlay.style.opacity = '';
        }
    });

    // Profile dropdown
    if (profileBtn && dropdown) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = dropdown.classList.toggle('is-open');
            profileBtn.setAttribute('aria-expanded', String(isOpen));
        });

        document.addEventListener('click', (e) => {
            if (!profileBtn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('is-open');
                profileBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Nested navigation groups — each branch's expand/collapse is independent.
    const expandButtons = sidebar.querySelectorAll('.akd-sidebar__expand-btn');
    expandButtons.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const submenu = document.getElementById(btn.getAttribute('aria-controls'));
            if (!submenu) return;

            const isOpen = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', String(!isOpen));
            submenu.classList.toggle('is-open', !isOpen);
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        closeSidebar();
        dropdown?.classList.remove('is-open');
        profileBtn?.setAttribute('aria-expanded', 'false');
    });
}