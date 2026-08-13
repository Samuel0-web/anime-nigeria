/**
 * Dashboard — desktop event carousel.
 * Mobile keeps its native horizontal scroll untouched; this only
 * initializes at the $bp-lg breakpoint and tears itself down below it.
 */
const DESKTOP_QUERY = '(min-width: 1024px)'; // keep in sync with $bp-lg in _tokens.scss
const AUTOPLAY_MS = 5000;
const DRAG_THRESHOLD = 3; // px of movement before a pointerdown counts as a drag, not a click

function initDashboardCarousel(wrap) {
    const track = wrap.querySelector('[data-dash-carousel-track]');
    const prevBtn = wrap.querySelector('[data-dash-carousel-prev]');
    const nextBtn = wrap.querySelector('[data-dash-carousel-next]');
    if (!track || !prevBtn || !nextBtn) return null;
    const slides = Array.from(track.children);
    if (slides.length < 2) return null;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let offset = 0;
    let maxOffset = 0;
    let slideStep = 0;
    let timer = null;
    let isHovering = false;
    let isDragging = false;
    let dragMoved = false;
    let dragStartX = 0;
    let dragStartOffset = 0;
    let resizeTimer = null;

    // Derives step size and the real max scrollable distance from the
    // current DOM layout — works for any number of slides.
    function measure() {
        const style = window.getComputedStyle(track);
        const gap = parseFloat(style.columnGap || style.gap || '0');
        slideStep = slides[0].getBoundingClientRect().width + gap;
        maxOffset = Math.max(0, track.scrollWidth - wrap.clientWidth);
    }

    function render(withTransition) {
        track.style.transition = withTransition && !reduceMotion ? '' : 'none';
        track.style.transform = `translateX(-${offset}px)`;
    }

    function setOffset(next, withTransition = true) {
        offset = Math.min(Math.max(next, 0), maxOffset);
        render(withTransition);
        updatePrevButton();
    }

    function updatePrevButton() {
        prevBtn.style.display = offset <= 1 ? 'none' : '';
    }

    function next() {
        measure();
        if (maxOffset === 0) return;
        // Snap back to the start once we've reached the real end —
        // never translate past what the content actually supports.
        setOffset(offset >= maxOffset - 1 ? 0 : offset + slideStep);
    }

    function prev() {
        measure();
        if (maxOffset === 0) return;
        setOffset(offset <= 0 ? maxOffset : offset - slideStep);
    }

    function play() {
        if (reduceMotion || isDragging) return;
        stop();
        timer = window.setInterval(next, AUTOPLAY_MS);
    }

    function stop() {
        if (timer) {
            window.clearInterval(timer);
            timer = null;
        }
    }

    function handlePrevClick() { prev(); play(); }
    function handleNextClick() { next(); play(); }
    function handleMouseEnter() { isHovering = true; stop(); }
    function handleMouseLeave() { isHovering = false; if (!isDragging) play(); }
    function handleFocusIn() { stop(); }
    function handleFocusOut() { if (!isHovering && !isDragging) play(); }

    function handleResize() {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(() => {
            measure();
            setOffset(offset, false); // re-clamp to the new maxOffset, no animation
        }, 150);
    }

    // ---- Drag-to-scroll ----
    function handlePointerDown(e) {
        if (e.button !== undefined && e.button !== 0) return; // left click / primary touch only
        isDragging = true;
        dragMoved = false;
        dragStartX = e.clientX;
        dragStartOffset = offset;
        stop();
        measure();
        track.style.transition = 'none';
        wrap.classList.add('is-dragging');
        track.setPointerCapture?.(e.pointerId);
    }

    function handlePointerMove(e) {
        if (!isDragging) return;
        const delta = e.clientX - dragStartX;
        if (Math.abs(delta) > DRAG_THRESHOLD) dragMoved = true;
        setOffset(dragStartOffset - delta, false);
    }

    function handlePointerUp(e) {
        if (!isDragging) return;
        isDragging = false;
        wrap.classList.remove('is-dragging');
        track.releasePointerCapture?.(e.pointerId);

        // Snap to the nearest slide boundary for a clean resting position.
        measure();

        const nearest = slideStep > 0
            ? Math.min(Math.round(offset / slideStep) * slideStep, maxOffset) : offset;

        setOffset(nearest, true);
        if (!isHovering) play();
    }

    // Swallow the click that follows a drag so releasing the mouse
    // over a card link doesn't trigger navigation.
    function handleDragClick(e) {
        if (dragMoved) {
            e.preventDefault();
            e.stopPropagation();
        }
    }

    prevBtn.addEventListener('click', handlePrevClick);
    nextBtn.addEventListener('click', handleNextClick);
    wrap.addEventListener('mouseenter', handleMouseEnter);
    wrap.addEventListener('mouseleave', handleMouseLeave);
    wrap.addEventListener('focusin', handleFocusIn);
    wrap.addEventListener('focusout', handleFocusOut);
    window.addEventListener('resize', handleResize);
    track.addEventListener('pointerdown', handlePointerDown);
    track.addEventListener('pointermove', handlePointerMove);
    track.addEventListener('pointerup', handlePointerUp);
    track.addEventListener('pointercancel', handlePointerUp);
    track.addEventListener('click', handleDragClick, true);
    measure();
    setOffset(0, false);
    play();

    return function teardown() {
        stop();
        prevBtn.removeEventListener('click', handlePrevClick);
        nextBtn.removeEventListener('click', handleNextClick);
        wrap.removeEventListener('mouseenter', handleMouseEnter);
        wrap.removeEventListener('mouseleave', handleMouseLeave);
        wrap.removeEventListener('focusin', handleFocusIn);
        wrap.removeEventListener('focusout', handleFocusOut);
        window.removeEventListener('resize', handleResize);
        track.removeEventListener('pointerdown', handlePointerDown);
        track.removeEventListener('pointermove', handlePointerMove);
        track.removeEventListener('pointerup', handlePointerUp);
        track.removeEventListener('pointercancel', handlePointerUp);
        track.removeEventListener('click', handleDragClick, true);
        wrap.classList.remove('is-dragging');
        track.style.transition = '';
        track.style.transform = '';
    };
}

document.addEventListener('DOMContentLoaded', () => {
    const wraps = document.querySelectorAll('[data-dash-carousel]');
    if (!wraps.length) return;
    const mql = window.matchMedia(DESKTOP_QUERY);
    const teardowns = new Map();

    function sync() {
        wraps.forEach((wrap) => {
            if (mql.matches && !teardowns.has(wrap)) {
                teardowns.set(wrap, initDashboardCarousel(wrap));
            } else if (!mql.matches && teardowns.has(wrap)) {
                const teardown = teardowns.get(wrap);
                if (teardown) teardown();
                teardowns.delete(wrap);
            }
        });
    }

    mql.addEventListener('change', sync);
    sync();
});