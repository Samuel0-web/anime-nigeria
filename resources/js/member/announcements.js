import { api } from '../modules/api';
import { setLoading, clearLoading } from '../modules/loading-state';

const FILTER_SCROLL_STEP = 160;
const OVERFLOW_TOLERANCE = 2;
const PLACEHOLDER_COUNT = 3; // one desktop row of skeleton cards

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
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
// .is-loading, which shows that card's skeleton. The state ends the moment
// the image has loaded or failed: no timers and no artificial delay.
// Takes the rows to watch, so batches appended later get the same treatment.
function watchCardImages(rows) {
    rows.forEach((row) => {
        if (!row.classList.contains('is-loading')) {
            return;
        }

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

// Category tabs + "See more", both backed by /member/api/announcements.
// Filtering and paging happen in SQL; this only swaps or appends the rows
// the server renders.
function initAnnouncementsBrowser() {
    const root = document.querySelector('[data-announce-root]');
    const group = root?.querySelector('[data-announce-group]');

    if (!root || !group) {
        return;
    }

    const endpoint = root.dataset.announceEndpoint;
    const filterRoot = root.querySelector('[data-announce-filter]');
    const buttons = filterRoot ? Array.from(filterRoot.querySelectorAll('[data-filter-value]')) : [];
    const emptyState = root.querySelector('[data-announce-empty]');
    const status = root.querySelector('[data-announce-status]');
    const more = root.querySelector('[data-announce-more]');
    const moreBtn = root.querySelector('[data-announce-more-btn]');
    const moreError = root.querySelector('[data-announce-more-error]');
    const placeholder = root.querySelector('template[data-announce-placeholder]');

    if (!more || !moreBtn || !moreError) {
        return;
    }

    const reduceMotion = prefersReducedMotion();

    const state = {
        category: 'all',
        label: 'All',
        cursor: group.dataset.nextCursor || null,
        hasMore: group.dataset.hasMore === '1',
        seq: 0,        // a response is applied only if it belongs to the latest request
        retry: null,   // { reset } of the request that last failed
    };

    // Ids currently on the page. A row is never appended twice.
    const seen = new Set(
        Array.from(group.querySelectorAll('[data-announce-id]')).map((row) => row.dataset.announceId)
    );

    const isMoreLoading = () => moreBtn.dataset.originalText !== undefined;

    function plural(count) {
        return `${count} announcement${count === 1 ? '' : 's'}`;
    }

    function announce(message) {
        if (status) {
            status.textContent = message;
        }
    }

    function renderMore() {
        const failed = state.retry !== null;

        more.hidden = !(state.hasMore || failed);
        moreError.hidden = !failed;
        moreBtn.textContent = failed ? 'Try again' : 'See more';
    }

    function finishRender() {
        const hasRows = group.children.length > 0;

        group.hidden = !hasRows;

        if (emptyState) {
            emptyState.hidden = hasRows || state.retry !== null;
        }

        renderMore();
    }

    function parseRows(html) {
        const template = document.createElement('template');
        template.innerHTML = html;

        return Array.from(template.content.children).filter((el) => el.tagName === 'LI');
    }

    function addRows(rows) {
        const fresh = rows.filter((row) => {
            const id = row.dataset.announceId;

            if (!id) {
                return true;
            }

            if (seen.has(id)) {
                return false;
            }

            seen.add(id);
            return true;
        });

        if (!reduceMotion) {
            fresh.forEach((row) => row.classList.add('is-entering'));
        }

        group.append(...fresh);
        watchCardImages(fresh);

        if (!reduceMotion) {
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    fresh.forEach((row) => row.classList.remove('is-entering'));
                });
            });
        }

        return fresh.length;
    }

    function showPlaceholders() {
        group.replaceChildren();
        seen.clear();

        const card = placeholder?.content.firstElementChild;

        if (!card) {
            return;
        }

        for (let i = 0; i < PLACEHOLDER_COUNT; i += 1) {
            group.append(card.cloneNode(true));
        }
    }

    async function load({ reset }) {
        const seq = ++state.seq;
        state.retry = null;
        moreError.hidden = true;

        if (reset) {
            // New category: forget the old cursor, clear the old results, and
            // show skeletons until the first batch for the new category arrives.
            if (isMoreLoading()) {
                clearLoading(moreBtn);
            }

            more.hidden = true;
            state.cursor = null;
            state.hasMore = false;

            if (emptyState) {
                emptyState.hidden = true;
            }

            group.hidden = false;
            showPlaceholders();
        } else {
            // "See more": the loaded announcements stay put.
            setLoading(moreBtn, 'Loading...');
        }

        group.setAttribute('aria-busy', 'true');

        try {
            const params = new URLSearchParams();

            if (state.category !== 'all') {
                params.set('category', state.category);
            }

            if (!reset && state.cursor) {
                params.set('cursor', state.cursor);
            }

            const result = await api(`${endpoint}?${params.toString()}`);

            if (seq !== state.seq) {
                return;
            }

            if (reset) {
                group.replaceChildren();
                seen.clear();
            }

            const added = addRows(parseRows(result.html || ''));

            state.cursor = result.next_cursor || null;
            state.hasMore = Boolean(result.has_more) && state.cursor !== null;

            const total = group.children.length;

            if (reset) {
                announce(total === 0
                    ? `No announcements in ${state.label}.`
                    : `Showing ${plural(total)} in ${state.label}.`);
            } else {
                announce(added === 0
                    ? 'No more announcements.'
                    : `Loaded ${added} more. Showing ${plural(total)}.`);
            }
        } catch (error) {
            if (seq !== state.seq) {
                return;
            }

            if (reset) {
                group.replaceChildren();
                seen.clear();
            }

            // Already-loaded announcements stay visible for a failed "See more".
            state.retry = { reset };
            announce('Could not load announcements.');
        } finally {
            if (seq === state.seq) {
                group.setAttribute('aria-busy', 'false');

                if (isMoreLoading()) {
                    clearLoading(moreBtn);
                }

                finishRender();
            }
        }
    }

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            if (button.getAttribute('aria-pressed') === 'true') {
                return;
            }

            buttons.forEach((btn) => {
                btn.setAttribute('aria-pressed', String(btn === button));
            });

            state.category = button.dataset.filterValue;
            state.label = button.textContent.trim();
            load({ reset: true });
        });
    });

    // One control serves both "See more" and "Try again". While a request is
    // running it is disabled (setLoading), so it cannot fire duplicates.
    moreBtn.addEventListener('click', () => {
        if (moreBtn.disabled) {
            return;
        }

        load({ reset: state.retry ? state.retry.reset : false });
    });

    renderMore();
}

export function initAnnouncementsFilter() {
    initFilterOverflowNav();
    initAnnouncementsBrowser();

    watchCardImages(Array.from(
        document.querySelectorAll('[data-announce-group] .akd-announce-row.is-loading')
    ));
}