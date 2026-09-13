// resources/js/modules/lightbox.js
// Generic, reusable lightbox shell for viewing a single image with optional
// metadata (title, category/date, caption). Knows nothing about any
// specific page's content. Mirrors the singleton/focus-trap/scroll-lock
// pattern already established by modules/modal.js, but built on the
// existing .an-lightbox markup and styling in _layout.scss, since a
// full-bleed image viewer is a different visual system from the boxed
// .akd-modal dialog.
//
// Touch handling: desktop closes on an outside click/tap (see the overlay
// click handler below), mobile closes on a dedicated vertical swipe on the
// content area instead, the two are deliberately separate interactions and
// never both active at once. Neither one touches the sidebar's own touch
// gesture system in modules/sidebar.js, that module guards itself by
// checking for `.an-lightbox.is-open` in the document.

let instance = null;
const FOCUSABLE_SELECTOR = 'button:not([disabled]), [href], [tabindex]:not([tabindex="-1"])';

// Matches modules/sidebar.js's own DESKTOP_BP, the established split for
// touch-gesture purposes in this codebase, so the two modules never
// disagree about which one owns a given viewport width.
const MOBILE_BP = 1024;

function isMobileViewport() {
    return window.innerWidth < MOBILE_BP;
}

// Forgiving margin, in px, around the actual rendered image/metadata
// before an outside click or tap counts as background. Without this, the
// desktop outside-tap-to-close effectively only fires deep inside
// .an-lightbox__content's own oversized box (min(92vw,1100px) x
// min(92vh,900px)), which can leave a wide empty-looking margin around a
// small or narrow image that does not actually close anything, that dead
// zone is the "distance feels too long" issue.
const CLOSE_HITBOX_PADDING = 24;

function pointIsNearRect(x, y, rect, padding) {
    return (
        x >= rect.left - padding && x <= rect.right + padding &&
        y >= rect.top - padding && y <= rect.bottom + padding
    );
}

// Direction-commit threshold for the mobile swipe-to-dismiss gesture,
// same value and purpose as sidebar.js's own DIRECTION_THRESHOLD_PX: a
// small dead zone so an accidental tremor during a tap is never mistaken
// for the start of a swipe.
const SWIPE_DIRECTION_THRESHOLD_PX = 10;

function createLightboxShell() {
    const overlay = document.createElement('div');
    overlay.className = 'an-lightbox';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-label', 'Image viewer');
    const content = document.createElement('div');
    content.className = 'an-lightbox__content';
    const image = document.createElement('img');
    image.className = 'an-lightbox__image';
    const meta = document.createElement('div');
    meta.className = 'an-lightbox__meta';
    const title = document.createElement('p');
    title.className = 'an-lightbox__title';
    const metaLine = document.createElement('p');
    metaLine.className = 'an-lightbox__meta-line';
    const caption = document.createElement('p');
    caption.className = 'an-lightbox__caption';
    meta.append(title, metaLine, caption);
    content.append(image, meta);
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'an-lightbox__close';
    closeButton.setAttribute('aria-label', 'Close image viewer');
    closeButton.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i>';
    overlay.append(content, closeButton);
    return { overlay, content, image, meta, title, metaLine, caption, closeButton };
}

export function useLightbox() {
    if (instance) return instance.api;
    const el = createLightboxShell();
    let isOpen = false;
    let lastFocusedEl = null;
    let prevButton = null;
    let nextButton = null;
    let touchState = null; // mobile swipe-to-dismiss tracking

    function getFocusable() {
        return Array.from(el.overlay.querySelectorAll(FOCUSABLE_SELECTOR));
    }

    function handleKeydown(event) {
        if (!isOpen) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            close();
            return;
        }

        if (event.key !== 'Tab') return;
        const focusables = getFocusable();
        if (!focusables.length) return;
        const first = focusables[0];
        const last = focusables[focusables.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    function setText(node, value) {
        node.textContent = value || '';
        node.hidden = !value;
    }

    // Optional prev/next support, unused by the Gallery (single image plus
    // close only, by request), kept generic so a future consumer of this
    // shared lightbox that does want navigation does not need a second
    // lightbox implementation to get it.
    function setupNavButton(existing, className, label, onClick) {
        if (!onClick) {
            existing?.remove();
            return null;
        }

        const button = existing || document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.setAttribute('aria-label', label);
        button.onclick = onClick;

        if (!existing) {
            el.overlay.insertBefore(button, el.closeButton);
        }

        return button;
    }

    function open({
        src, alt = '', title: itemTitle = '', categoryLabel = '', date = '',
        caption: itemCaption = '', onPrev = null, onNext = null, triggerEl = null,
    } = {}) {
        lastFocusedEl = triggerEl || document.activeElement;
        el.image.src = src;
        el.image.alt = alt;
        setText(el.title, itemTitle);
        setText(el.metaLine, [categoryLabel, date].filter(Boolean).join(' \u2022 '));
        setText(el.caption, itemCaption);
        prevButton = setupNavButton(prevButton, 'an-lightbox__prev', 'Previous image', onPrev);
        nextButton = setupNavButton(nextButton, 'an-lightbox__next', 'Next image', onNext);
        if (prevButton) prevButton.innerHTML = '<i class="fa-solid fa-chevron-left" aria-hidden="true"></i>';
        if (nextButton) nextButton.innerHTML = '<i class="fa-solid fa-chevron-right" aria-hidden="true"></i>';

        if (!document.body.contains(el.overlay)) {
            document.body.appendChild(el.overlay);
        }

        isOpen = true;
        el.overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', handleKeydown);
        requestAnimationFrame(() => el.closeButton.focus());
    }

    function close() {
        if (!isOpen) return;
        isOpen = false;
        el.overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        document.removeEventListener('keydown', handleKeydown);
        el.image.removeAttribute('src');

        // Defensive reset in case close() is reached mid-drag (Escape or
        // the close button pressed while a swipe is in progress), so
        // reopening the lightbox never starts from a leftover offset.
        touchState = null;
        el.content.classList.remove('is-dragging');
        el.content.style.transform = '';
        el.content.style.opacity = '';

        const toFocus = lastFocusedEl;
        lastFocusedEl = null;
        toFocus?.focus();
    }

    // ---- Desktop: outside click/tap closes ----
    // Position-based, not target-based (see CLOSE_HITBOX_PADDING above):
    // this is what shrinks the effective non-closing area down to the
    // actual visible image and metadata, instead of all of
    // .an-lightbox__content's larger reserved box.
    el.closeButton.addEventListener('click', close);
    el.overlay.addEventListener('click', (event) => {
        if (isMobileViewport()) return; // mobile dismissal is the swipe gesture only, see below
        if (event.target.closest('button')) return; // let close/prev/next handle themselves

        const imageRect = el.image.getBoundingClientRect();
        const metaRect = el.meta.getBoundingClientRect();

        const nearImage = pointIsNearRect(event.clientX, event.clientY, imageRect, CLOSE_HITBOX_PADDING);
        const nearMeta = pointIsNearRect(event.clientX, event.clientY, metaRect, CLOSE_HITBOX_PADDING);

        if (!nearImage && !nearMeta) close();
    });

    // ---- Mobile: vertical swipe on the content area dismisses ----
    // Deliberately separate from the desktop interaction above (that one
    // is disabled entirely below MOBILE_BP). Starting on the metadata
    // block is ignored here so its own scrolling, if it ever needs to
    // scroll, is never hijacked by this gesture.
    function handleContentTouchStart(event) {
        if (!isMobileViewport()) return;
        if (event.touches.length !== 1) return;
        if (event.target.closest('button')) return;
        if (event.target.closest('.an-lightbox__meta')) return;

        const touch = event.touches[0];
        touchState = { startX: touch.clientX, startY: touch.clientY, dragging: false };
    }

    function handleContentTouchMove(event) {
        if (!touchState) return;
        const touch = event.touches[0];
        if (!touch) return;

        const dx = touch.clientX - touchState.startX;
        const dy = touch.clientY - touchState.startY;

        if (!touchState.dragging) {
            if (Math.abs(dx) < SWIPE_DIRECTION_THRESHOLD_PX && Math.abs(dy) < SWIPE_DIRECTION_THRESHOLD_PX) {
                return;
            }

            if (Math.abs(dx) >= Math.abs(dy)) {
                // Predominantly horizontal, not this gesture, and not
                // something this handler needs to act on either.
                touchState = null;
                return;
            }

            touchState.dragging = true;
            el.content.classList.add('is-dragging');
        }

        const now = event.timeStamp;
        const dt = now - (touchState.lastTime || now);
        if (dt > 0) {
            touchState.lastVelocity = (dy - (touchState.lastDy || 0)) / dt;
        }
        touchState.lastDy = dy;
        touchState.lastTime = now;

        event.preventDefault();
        el.content.style.transform = `translateY(${dy}px)`;
        el.content.style.opacity = String(Math.max(0.4, 1 - Math.abs(dy) / (window.innerHeight * 0.6)));
    }

    function handleContentTouchEnd() {
        if (!touchState) return;

        if (!touchState.dragging) {
            touchState = null;
            return;
        }

        const lastDy = touchState.lastDy || 0;
        const lastVelocity = touchState.lastVelocity || 0;
        const distanceThreshold = Math.min(160, window.innerHeight * 0.22);
        const shouldDismiss = Math.abs(lastDy) > distanceThreshold || Math.abs(lastVelocity) > 0.5;

        el.content.classList.remove('is-dragging');

        if (shouldDismiss) {
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (prefersReducedMotion) {
                close();
            } else {
                const direction = lastDy < 0 ? -1 : 1;
                el.content.style.transform = `translateY(${direction * window.innerHeight}px)`;
                el.content.style.opacity = '0';
                window.setTimeout(close, 220);
            }
        } else {
            el.content.style.transform = '';
            el.content.style.opacity = '';
        }

        touchState = null;
    }

    el.content.addEventListener('touchstart', handleContentTouchStart, { passive: true });
    el.content.addEventListener('touchmove', handleContentTouchMove, { passive: false });
    el.content.addEventListener('touchend', handleContentTouchEnd, { passive: true });
    el.content.addEventListener('touchcancel', handleContentTouchEnd, { passive: true });

    // Crossing the mobile/desktop breakpoint mid-open (rotation, a resized
    // window) could otherwise leave a half-dragged transform stuck in
    // place, since the gesture that produced it may no longer be the
    // active interaction model for the new width.
    window.addEventListener('resize', () => {
        if (!isOpen) return;
        touchState = null;
        el.content.classList.remove('is-dragging');
        el.content.style.transform = '';
        el.content.style.opacity = '';
    });

    instance = { api: { open, close, isOpen: () => isOpen } };
    return instance.api;
}