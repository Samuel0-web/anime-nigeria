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
    const userId = sidebar.dataset.userId || 'guest';

    const DESKTOP_BP = 1024;
    const FLYOUT_GAP = 8;           // px between a trigger/panel and the next flyout level
    const FLYOUT_CLOSE_DELAY = 200; // ms grace period so diagonal mouse moves don't close menus
    const VIEWPORT_MARGIN = 8;      // px kept clear of the viewport edge when flipping/clamping

    function isDesktop() {
        return window.innerWidth >= DESKTOP_BP;
    }

    function isCollapsed() {
        return layout.classList.contains('is-collapsed');
    }

    if (sidebarScroll) {
        const SIDEBAR_SCROLL_KEY = `akd-sidebar-scroll:${userId}`;
        const savedScroll = sessionStorage.getItem(SIDEBAR_SCROLL_KEY);

        if (savedScroll !== null) {
            requestAnimationFrame(() => {
                sidebarScroll.scrollTop = parseInt(savedScroll, 10) || 0;
            });
        }

        sidebarScroll.addEventListener('scroll', () => {
            sessionStorage.setItem(SIDEBAR_SCROLL_KEY, String(sidebarScroll.scrollTop));
        }, { passive: true });
    }

    // ---- Global swipe-to-open / drag-to-close (mobile only) ----
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

    // ---- Portal helper ----
    // Moves an element to <body> as position:fixed so it escapes
    // .akd-sidebar__scroll's overflow clipping, then positions it beside an
    // anchor rect — either the trigger itself (level 0) or the previous
    // flyout panel (level 1+). Restores the element to its original DOM spot
    // on close so expanded-mode inline behavior keeps working unchanged.
    function portalElement(el, anchorRect, prevPanelRect) {
        el.__akdOriginalParent = el.parentElement;
        el.__akdOriginalNext = el.nextSibling;

        document.body.appendChild(el);
        el.classList.add('akd-flyout-portal');
        el.style.maxHeight = `${Math.round(window.innerHeight * 0.7)}px`;

        const left = prevPanelRect ? prevPanelRect.right + FLYOUT_GAP : anchorRect.right + FLYOUT_GAP;
        el.style.left = `${left}px`;
        el.style.top = `${anchorRect.top}px`;

        requestAnimationFrame(() => {
            const rect = el.getBoundingClientRect();

            if (rect.right > window.innerWidth - VIEWPORT_MARGIN) {
                const flippedLeft = prevPanelRect
                    ? prevPanelRect.left - FLYOUT_GAP - rect.width
                    : anchorRect.left - FLYOUT_GAP - rect.width;
                el.style.left = `${Math.max(VIEWPORT_MARGIN, flippedLeft)}px`;
            }

            if (rect.bottom > window.innerHeight - VIEWPORT_MARGIN) {
                el.style.top = `${Math.max(VIEWPORT_MARGIN, window.innerHeight - VIEWPORT_MARGIN - rect.height)}px`;
            }

            el.classList.add('is-open');
        });
    }

    function unportalElement(el) {
        el.classList.remove('akd-flyout-portal', 'is-open');
        el.style.left = '';
        el.style.top = '';
        el.style.maxHeight = '';

        const parent = el.__akdOriginalParent;
        const next = el.__akdOriginalNext;
        if (parent) parent.insertBefore(el, next || null);
    }

    // ---- Profile dropdown ----
    function closeProfileDropdown() {
        if (!dropdown) return;
        if (dropdown.classList.contains('akd-flyout-portal')) unportalElement(dropdown);
        dropdown.classList.remove('is-open');
        profileBtn?.setAttribute('aria-expanded', 'false');
    }

    function openProfileDropdown() {
        if (!dropdown || !profileBtn) return;
        closeAllFlyouts();

        if (isCollapsed() && isDesktop()) {
            portalElement(dropdown, profileBtn.getBoundingClientRect(), null);
        } else {
            dropdown.classList.add('is-open');
        }

        profileBtn.setAttribute('aria-expanded', 'true');
    }

    if (profileBtn && dropdown) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.contains('is-open') ? closeProfileDropdown() : openProfileDropdown();
        });
    }

    // ---- Recursive collapsed-sidebar flyouts ----
    // Every nav item's nesting depth is computed once, up front, from the
    // DOM as originally rendered — depth 0 is a row in the collapsed icon
    // rail, depth 1+ is normally nested inside a parent. That depth becomes
    // the item's flyout "level", so the same logic handles any nesting depth
    // the PHP navigation data happens to contain, with nothing hardcoded
    // per level.
    const allItems = Array.from(sidebar.querySelectorAll('.akd-sidebar__nav .akd-sidebar__item'));

    function computeDepth(item) {
        let depth = 0;
        let el = item.parentElement;
        while (el && el !== sidebar) {
            if (el.classList?.contains('akd-sidebar__sublist')) depth++;
            el = el.parentElement;
        }
        return depth;
    }

    allItems.forEach((item) => { item.dataset.flyoutLevel = String(computeDepth(item)); });

    const openLevels = []; // openLevels[level] = { item, panel } for whichever panel is open at that level
    let closeTimer = null;

    function getOwnSublist(item) {
        return item.querySelector(':scope > .akd-sidebar__sublist');
    }

    function getExpandBtn(item) {
        return item.querySelector(':scope > .akd-sidebar__row .akd-sidebar__expand-btn, :scope > .akd-sidebar__expand-btn');
    }

    function closeFromLevel(level) {
        for (let l = openLevels.length - 1; l >= level; l--) {
            const entry = openLevels[l];
            if (!entry) continue;
            unportalElement(entry.panel);
            entry.item.classList.remove('is-flyout-open');
            getExpandBtn(entry.item)?.setAttribute('aria-expanded', 'false');
            openLevels[l] = undefined;
        }
        openLevels.length = level;
    }

    function closeAllFlyouts() {
        clearTimeout(closeTimer);
        closeFromLevel(0);
    }

    function openFlyoutFor(item) {
        if (!isCollapsed() || !isDesktop()) return;
        const level = Number(item.dataset.flyoutLevel || 0);
        if (openLevels[level] && openLevels[level].item === item) return;

        closeFromLevel(level);

        const sublist = getOwnSublist(item);
        if (!sublist) return;

        closeProfileDropdown();

        const anchorRect = item.getBoundingClientRect();
        const prevPanelRect = level > 0 && openLevels[level - 1]
            ? openLevels[level - 1].panel.getBoundingClientRect()
            : null;

        portalElement(sublist, anchorRect, prevPanelRect);
        openLevels[level] = { item, panel: sublist };
        item.classList.add('is-flyout-open');
        getExpandBtn(item)?.setAttribute('aria-expanded', 'true');
    }

    allItems.forEach((item) => {
        const level = Number(item.dataset.flyoutLevel);
        const hasChildren = item.classList.contains('akd-sidebar__item--parent');

        const onEnter = () => {
            if (!isCollapsed() || !isDesktop()) return;
            clearTimeout(closeTimer);
            closeFromLevel(level + (hasChildren ? 1 : 0));
            if (hasChildren) openFlyoutFor(item);
        };

        const onLeave = () => {
            if (!isCollapsed() || !isDesktop()) return;
            clearTimeout(closeTimer);
            closeTimer = setTimeout(() => closeFromLevel(level), FLYOUT_CLOSE_DELAY);
        };

        item.addEventListener('mouseenter', onEnter);
        item.addEventListener('mouseleave', onLeave);
        item.addEventListener('focusin', onEnter);
        item.addEventListener('focusout', (e) => {
            if (!item.contains(e.relatedTarget)) onLeave();
        });

        // Once this item's own sublist is showing as a flyout panel,
        // hovering the panel itself must keep this level open too.
        const sublist = getOwnSublist(item);
        if (sublist) {
            sublist.addEventListener('mouseenter', () => {
                if (!isCollapsed() || !isDesktop()) return;
                clearTimeout(closeTimer);
            });
            sublist.addEventListener('mouseleave', () => {
                if (!isCollapsed() || !isDesktop()) return;
                clearTimeout(closeTimer);
                closeTimer = setTimeout(() => closeFromLevel(level), FLYOUT_CLOSE_DELAY);
            });
        }
    });

    const expandButtons = sidebar.querySelectorAll('.akd-sidebar__expand-btn');
    expandButtons.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const item = btn.closest('.akd-sidebar__item--parent');
            if (!item) return;

            if (isCollapsed() && isDesktop()) {
                item.classList.contains('is-flyout-open')
                    ? closeFromLevel(Number(item.dataset.flyoutLevel))
                    : openFlyoutFor(item);
                return;
            }

            const submenu = document.getElementById(btn.getAttribute('aria-controls'));
            if (!submenu) return;
            const isOpen = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', String(!isOpen));
            submenu.classList.toggle('is-open', !isOpen);
        });
    });

    document.addEventListener('click', (e) => {
        if (!profileBtn?.contains(e.target) && !dropdown?.contains(e.target)) {
            closeProfileDropdown();
        }

        const insideOpenFlyout = openLevels.some(
            (entry) => entry && (entry.item.contains(e.target) || entry.panel.contains(e.target))
        );
        if (!insideOpenFlyout) closeAllFlyouts();
    });

    // ---- Custom tooltip — top-level collapsed icons only ----
    // Nested items always surface inside a flyout with their label already
    // visible, so they never need a tooltip.
    let tooltipEl = null;
    let tooltipHideTimer = null;

    function ensureTooltip() {
        if (tooltipEl) return tooltipEl;
        tooltipEl = document.createElement('div');
        tooltipEl.className = 'akd-sidebar-tooltip';
        tooltipEl.setAttribute('role', 'tooltip');
        document.body.appendChild(tooltipEl);
        return tooltipEl;
    }

    function showTooltip(target, label) {
        if (!isDesktop() || !label) return;
        clearTimeout(tooltipHideTimer);
        const el = ensureTooltip();
        const rect = target.getBoundingClientRect();
        el.textContent = label;
        el.style.top = `${rect.top + rect.height / 2}px`;
        el.style.left = `${rect.right + 10}px`;
        el.classList.add('is-visible');
    }

    function hideTooltip(immediate = false) {
        if (!tooltipEl) return;
        clearTimeout(tooltipHideTimer);
        if (immediate) { tooltipEl.classList.remove('is-visible'); return; }
        tooltipHideTimer = setTimeout(() => tooltipEl.classList.remove('is-visible'), 40);
    }

    const topLevelItems = allItems.filter((item) => item.dataset.flyoutLevel === '0');
    topLevelItems.forEach((item) => {
        const trigger = item.querySelector(
            ':scope > .akd-sidebar__link, ' +
            ':scope > .akd-sidebar__row > .akd-sidebar__link--parent, ' +
            ':scope > .akd-sidebar__link--parent.akd-sidebar__expand-btn'
        );
        const label = trigger?.querySelector('.akd-sidebar__label')?.textContent?.trim();
        if (!trigger || !label) return;

        trigger.addEventListener('mouseenter', () => { if (isCollapsed()) showTooltip(trigger, label); });
        trigger.addEventListener('mouseleave', () => hideTooltip());
        trigger.addEventListener('focus', () => { if (isCollapsed()) showTooltip(trigger, label); });
        trigger.addEventListener('blur', () => hideTooltip(true));
    });

    // ---- Desktop collapse / expand ----
    const collapseToggle = document.getElementById('sidebarCollapseToggle');
    const brandRow = sidebar.querySelector('.akd-sidebar__brand-row');
    const COLLAPSE_KEY = `akd-sidebar-collapsed:${userId}`;

    function applyCollapseButton(collapsed) {
        if (!collapseToggle) return;
        collapseToggle.setAttribute('aria-expanded', String(!collapsed));
        collapseToggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
    }

    function setCollapsed(next) {
        if (!isDesktop()) return;
        closeAllFlyouts();
        closeProfileDropdown();
        hideTooltip(true);
        layout.classList.toggle('is-collapsed', next);
        document.documentElement.classList.toggle('akd-sidebar-collapsed', next);
        applyCollapseButton(next);

        try {
            localStorage.setItem(COLLAPSE_KEY, next ? '1' : '0');
        } catch (e) {}
    }

    collapseToggle?.addEventListener('click', (e) => {
        setCollapsed(!isCollapsed());

        // A mouse click leaves the button focused, which would otherwise
        // keep it revealed over the icon logo purely because of that
        // lingering focus (the CSS reveal is driven by :focus-within).
        // Keyboard activation (Enter/Space) dispatches a click with
        // detail === 0, so this only blurs the mouse case — Tab-driven
        // focus is left alone, and the button stays visible for keyboard
        // users exactly as before.
        if (e.detail > 0) {
            collapseToggle.blur();
        }
    });
    applyCollapseButton(isCollapsed());

    // Collapse/expand tooltip — reuses the same tooltip element as the nav
    // icons. Direct hover/focus on the button covers the expanded state,
    // where it's always interactive. The brand-row fallback covers the
    // collapsed state, where the button only becomes hit-testable once
    // :hover/:focus-within matches, so it may not get its own mouseenter
    // the instant that happens.
    function showCollapseTooltip() {
        if (!collapseToggle) return;
        showTooltip(collapseToggle, collapseToggle.getAttribute('aria-label'));
    }

    collapseToggle?.addEventListener('mouseenter', showCollapseTooltip);
    collapseToggle?.addEventListener('mouseleave', () => hideTooltip());
    collapseToggle?.addEventListener('focus', showCollapseTooltip);
    collapseToggle?.addEventListener('blur', () => hideTooltip(true));
    brandRow?.addEventListener('mouseenter', () => { if (isCollapsed()) showCollapseTooltip(); });
    brandRow?.addEventListener('mouseleave', () => { if (isCollapsed()) hideTooltip(); });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= DESKTOP_BP && sidebar.classList.contains('is-open')) {
            closeSidebar();
        }

        if (window.innerWidth >= DESKTOP_BP) {
            resetGestureState();
            sidebar.classList.remove('is-dragging');
            overlay?.classList.remove('is-dragging');
            sidebar.style.transform = '';
            if (overlay) overlay.style.opacity = '';
        } else {
            // Crossing into mobile mid-session — flyouts/tooltip/portals
            // belong to the desktop collapsed mode only.
            closeAllFlyouts();
            closeProfileDropdown();
            hideTooltip(true);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        closeSidebar();
        closeProfileDropdown();
        closeAllFlyouts();
        hideTooltip(true);
    });
}