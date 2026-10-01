const FILTER_SCROLL_STEP = 160;
const OVERFLOW_TOLERANCE = 2;

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function initFilterSelection() {
    const root = document.querySelector('[data-announce-filter]');
    const group = document.querySelector('[data-announce-group]');
    const emptyState = document.querySelector('[data-announce-empty]');
    const status = document.querySelector('[data-announce-status]');

    if (!root || !group) {
        return;
    }

    const buttons = Array.from(root.querySelectorAll('[data-filter-value]'));
    const rows = Array.from(group.querySelectorAll('[data-announce-row]'));

    if (!buttons.length || !rows.length) {
        return;
    }

    const reduceMotion = prefersReducedMotion();

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            if (button.getAttribute('aria-pressed') === 'true') {
                return;
            }

            const value = button.dataset.filterValue;
            const label = button.textContent.trim();
            const matchesFilter = (row) => value === 'all' || row.dataset.announceRow === value;

            buttons.forEach((btn) => {
                btn.setAttribute('aria-pressed', String(btn === button));
            });

            let visibleCount = 0;

            rows.forEach((row) => {
                const matches = matchesFilter(row);

                if (!matches) {
                    // Removed from layout immediately: the filtered result
                    // is established at once, so a surviving card is never
                    // seen sitting at its old grid position.
                    row.hidden = true;
                    row.classList.remove('is-entering');
                    return;
                }

                visibleCount += 1;
                const wasHidden = row.hidden;
                row.hidden = false;

                if (reduceMotion || !wasHidden) {
                    row.classList.remove('is-entering');
                    return;
                }

                // Already in its final layout position the moment it
                // becomes visible; only opacity animates.
                row.classList.add('is-entering');
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        row.classList.remove('is-entering');
                    });
                });
            });

            group.hidden = visibleCount === 0;

            if (emptyState) {
                emptyState.hidden = visibleCount !== 0;
            }

            if (status) {
                status.textContent = visibleCount === 0
                    ? `No announcements in ${label}.`
                    : `Showing ${visibleCount} announcement${visibleCount === 1 ? '' : 's'} in ${label}.`;
            }
        });
    });
}

function initFilterOverflowNav() {
    const root = document.querySelector('[data-announce-filter]');
    if (!root) {
        return;
    }

    const scroller = root.querySelector('[data-filter-scroll]');
    const prevBtn = root.querySelector('[data-filter-nav="prev"]');
    const nextBtn = root.querySelector('[data-filter-nav="next"]');

    if (!scroller || !prevBtn || !nextBtn) {
        return;
    }

    function updateNavState() {
        const hasOverflow = scroller.scrollWidth > scroller.clientWidth + OVERFLOW_TOLERANCE;

        prevBtn.hidden = !hasOverflow;
        nextBtn.hidden = !hasOverflow;

        if (!hasOverflow) {
            return;
        }

        const atStart = scroller.scrollLeft <= OVERFLOW_TOLERANCE;
        const atEnd = scroller.scrollLeft >= scroller.scrollWidth - scroller.clientWidth - OVERFLOW_TOLERANCE;

        prevBtn.disabled = atStart;
        nextBtn.disabled = atEnd;
    }

    prevBtn.addEventListener('click', () => {
        scroller.scrollBy({ left: -FILTER_SCROLL_STEP, behavior: 'smooth' });
    });

    nextBtn.addEventListener('click', () => {
        scroller.scrollBy({ left: FILTER_SCROLL_STEP, behavior: 'smooth' });
    });

    scroller.addEventListener('scroll', updateNavState, { passive: true });

    let resizeTimer;
    window.addEventListener('resize', () => {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(updateNavState, 150);
    });

    updateNavState();
}

// Card loading state. PHP renders every card that has an image as
// .is-loading, which shows that card's skeleton. The real asynchronous
// step is the image (lazy loading included), so the state ends the moment
// the image has loaded or failed: no timers and no artificial delay.
// If this never runs, a CSS failsafe in _announcements.scss reveals the card.
function initCardLoading() {
    const rows = document.querySelectorAll('[data-announce-group] .akd-announce-row.is-loading');

    rows.forEach((row) => {
        const img = row.querySelector('.akd-announce-row__image');

        const markReady = () => {
            row.classList.remove('is-loading');
        };

        if (!img) {
            markReady();
            return;
        }

        // A broken image is hidden so no broken-image icon shows; the card
        // still reveals, and the image area keeps its quiet surface.
        const markError = () => {
            img.hidden = true;
            markReady();
        };

        // Already finished (cached, or loaded before this script ran).
        if (img.complete) {
            if (img.naturalWidth > 0) {
                markReady();
            } else {
                markError();
            }
            return;
        }

        img.addEventListener('load', markReady, { once: true });
        img.addEventListener('error', markError, { once: true });
    });
}

export function initAnnouncementsFilter() {
    initFilterSelection();
    initFilterOverflowNav();
    initCardLoading();
}