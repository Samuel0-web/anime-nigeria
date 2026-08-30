const FILTER_SCROLL_STEP = 160;
const OVERFLOW_TOLERANCE = 2;

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

// Marks whichever currently-visible rows are first/last so their outer
// corners can pick up the group's rounded treatment. Driven by which
// rows are actually visible after filtering, not DOM position, since
// non-matching rows stay in the markup as [hidden] rather than being
// removed outright.
function updateEdgeClasses(rows) {
    let first = null;
    let last = null;

    rows.forEach((row) => {
        row.classList.remove('is-first-visible', 'is-last-visible');

        if (row.hidden) {
            return;
        }

        if (!first) {
            first = row;
        }
        last = row;
    });

    if (first) {
        first.classList.add('is-first-visible');
    }
    if (last) {
        last.classList.add('is-last-visible');
    }
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

    updateEdgeClasses(rows);

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
                    // is established at once, so a surviving row is never
                    // seen sitting at its old position before the layout
                    // catches up.
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
                // becomes visible; only opacity animates, so there's
                // nothing to travel from.
                row.classList.add('is-entering');
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        row.classList.remove('is-entering');
                    });
                });
            });

            updateEdgeClasses(rows);

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

export function initAnnouncementsFilter() {
    initFilterSelection();
    initFilterOverflowNav();
}