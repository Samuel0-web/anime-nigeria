import { useConfirmDialog } from '../modules/confirm-dialog';

const DESKTOP_QUERY = '(min-width: 1024px)';
const FILTER_SCROLL_STEP = 160;
const OVERFLOW_TOLERANCE = 2;

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/* ============================================================
 * Desktop notification dropdown (header bell)
 * ============================================================ */
let dropdownInitialized = false;

export function initNotificationDropdown() {
    if (dropdownInitialized) return; // guards against accidental double init

    const root = document.querySelector('[data-notif-root]');
    const trigger = document.querySelector('[data-notif-trigger]');
    const dropdown = document.querySelector('[data-notif-dropdown]');

    if (!root || !trigger || !dropdown) {
        return;
    }

    dropdownInitialized = true;

    const desktopQuery = window.matchMedia(DESKTOP_QUERY);
    let isOpen = false;

    function close({ returnFocus = false } = {}) {
        if (!isOpen) return;
        isOpen = false;
        dropdown.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        if (returnFocus) trigger.focus();
    }

    function open() {
        if (isOpen) return;
        isOpen = true;
        dropdown.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
    }

    trigger.addEventListener('click', (event) => {
        if (!desktopQuery.matches) {
            window.location.assign('/member/notifications');
            return;
        }

        isOpen ? close() : open();
    });

    document.addEventListener('click', (event) => {
        if (isOpen && !root.contains(event.target)) close();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen) close({ returnFocus: true });
    });

    desktopQuery.addEventListener('change', (event) => {
        if (!event.matches) close();
    });
}

/* ============================================================
 * Full Notifications page
 * ============================================================ */
function updateEdgeClasses(rows) {
    let first = null;
    let last = null;

    rows.forEach((row) => {
        row.classList.remove('is-first-visible', 'is-last-visible');
        if (row.hidden) return;
        if (!first) first = row;
        last = row;
    });

    if (first) first.classList.add('is-first-visible');
    if (last) last.classList.add('is-last-visible');
}

// Owns the selected-id set and keeps the bulk bar / Select all control in
// sync with it. Created once per page load and shared between the filter
// and the selection/delete logic, so a category change and a selection
// change both funnel through the same refresh() and can never disagree
// about what "currently visible" means. This is the fix for the Select
// all state bug: previously the filter had no way to tell the selection
// UI that the visible set had changed.
function createSelectionController(rows) {
    const bulkBar = document.querySelector('[data-notify-bulkbar]');
    const bulkCount = document.querySelector('[data-notify-bulk-count]');
    const selectAllBtn = document.querySelector('[data-notify-select-all]');
    const selectAllLabel = selectAllBtn?.querySelector('[data-notify-select-all-label]');
    const selected = new Set();

    function refresh() {
        const count = selected.size;
        if (bulkBar) bulkBar.classList.toggle('is-visible', count > 0);
        if (bulkCount) bulkCount.textContent = `${count} selected`;

        if (selectAllBtn) {
            const visibleRows = rows.filter((r) => !r.hidden);
            const allSelected = visibleRows.length > 0 &&
                visibleRows.every((r) => selected.has(r.dataset.notifyId));

            selectAllBtn.setAttribute('aria-pressed', String(allSelected));

            if (selectAllLabel) {
                selectAllLabel.textContent = allSelected ? 'Clear selection' : 'Select all';
            }
        }
    }

    return { selected, refresh };
}

function initNotifyFilter(rows, selection) {
    const root = document.querySelector('[data-notify-filter]');
    const group = document.querySelector('[data-notify-group]');
    const emptyState = document.querySelector('[data-notify-empty]');
    const status = document.querySelector('[data-notify-status]');
    if (!root || !group) return;
    const buttons = Array.from(root.querySelectorAll('[data-filter-value]'));
    const reduceMotion = prefersReducedMotion();
    updateEdgeClasses(rows);

    function applyFilter(value, label) {
        const matchesFilter = (row) => value === 'all' || row.dataset.notifyRow === value;
        let visibleCount = 0;

        rows.forEach((row) => {
            const matches = matchesFilter(row);

            if (!matches) {
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

            row.classList.add('is-entering');
            requestAnimationFrame(() => {
                requestAnimationFrame(() => row.classList.remove('is-entering'));
            });
        });

        updateEdgeClasses(rows);
        group.hidden = visibleCount === 0;
        if (emptyState) emptyState.hidden = visibleCount !== 0;

        if (status) {
            status.textContent = visibleCount === 0 ? `No notifications in ${label}.`
                : `Showing ${visibleCount} notification${visibleCount === 1 ? '' : 's'} in ${label}.`;
        }

        // Recalculate Select all / Clear selection and the bulk bar
        // count against the newly visible set.
        selection.refresh();
    }

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            if (button.getAttribute('aria-pressed') === 'true') return;
            buttons.forEach((btn) => btn.setAttribute('aria-pressed', String(btn === button)));
            applyFilter(button.dataset.filterValue, button.textContent.trim());
        });
    });
}

function initNotifyFilterOverflowNav() {
    const root = document.querySelector('[data-notify-filter]');
    if (!root) return;
    const scroller = root.querySelector('[data-filter-scroll]');
    const prevBtn = root.querySelector('[data-filter-nav="prev"]');
    const nextBtn = root.querySelector('[data-filter-nav="next"]');
    if (!scroller || !prevBtn || !nextBtn) return;

    function updateNavState() {
        const hasOverflow = scroller.scrollWidth > scroller.clientWidth + OVERFLOW_TOLERANCE;
        prevBtn.hidden = !hasOverflow;
        nextBtn.hidden = !hasOverflow;
        if (!hasOverflow) return;
        prevBtn.disabled = scroller.scrollLeft <= OVERFLOW_TOLERANCE;
        nextBtn.disabled = scroller.scrollLeft >= scroller.scrollWidth - scroller.clientWidth - OVERFLOW_TOLERANCE;
    }

    prevBtn.addEventListener('click', () => scroller.scrollBy({ left: -FILTER_SCROLL_STEP, behavior: 'smooth' }));
    nextBtn.addEventListener('click', () => scroller.scrollBy({ left: FILTER_SCROLL_STEP, behavior: 'smooth' }));
    scroller.addEventListener('scroll', updateNavState, { passive: true });
    let resizeTimer;

    window.addEventListener('resize', () => {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(updateNavState, 150);
    });

    updateNavState();
}

// Mock only: no backend, so marking as read just clears the header badge
// for the rest of this pageview. Only ever called from the full page,
// never from the dropdown, preserving the existing read-state timing.
function markAllRead() {
    const badge = document.querySelector('.akd-header__badge');
    if (badge) badge.remove();
    const trigger = document.querySelector('[data-notif-trigger]');
    if (trigger) trigger.setAttribute('aria-label', 'Notifications');
}

function removeRow(row, { rows, selection, group, emptyState }) {
    const id = row.dataset.notifyId;

    const finish = () => {
        row.remove();
        const idx = rows.indexOf(row);
        if (idx !== -1) rows.splice(idx, 1);
        selection.selected.delete(id);
        selection.refresh();
        const stillVisible = rows.some((r) => !r.hidden);
        if (group) group.hidden = !stillVisible;
        if (emptyState) emptyState.hidden = stillVisible;
        document.querySelector(`[data-notif-dropdown] [data-notif-id="${CSS.escape(id)}"]`)?.remove();
    };

    if (prefersReducedMotion()) {
        finish();
        return;
    }

    row.classList.add('is-removing');
    row.addEventListener('transitionend', finish, { once: true });
    window.setTimeout(finish, 260);
}

function initNotifySelectionAndDelete(rows, selection) {
    const group = document.querySelector('[data-notify-group]');
    const emptyState = document.querySelector('[data-notify-empty]');
    const bulkDeleteBtn = document.querySelector('[data-notify-bulk-delete]');
    const clearBtn = document.querySelector('[data-notify-clear-selection]');
    const selectAllBtn = document.querySelector('[data-notify-select-all]');
    const confirmDialog = useConfirmDialog();

    function toggleRowSelection(row) {
        const id = row.dataset.notifyId;
        const btn = row.querySelector('[data-notify-select]');
        const willSelect = !selection.selected.has(id);
        willSelect ? selection.selected.add(id) : selection.selected.delete(id);
        row.classList.toggle('is-selected', willSelect);
        if (btn) btn.setAttribute('aria-pressed', String(willSelect));

        selection.refresh();
    }

    rows.forEach((row) => {
        row.querySelector('[data-notify-select]')?.addEventListener('click', () => toggleRowSelection(row));

        row.querySelector('[data-notify-delete]')?.addEventListener('click', async () => {
            const title = row.querySelector('.akd-notify-row__title')?.textContent?.trim() || 'this notification';

            const confirmed = await confirmDialog.ask({
                title: 'Delete notification',
                message: `Delete "${title}"? This can't be undone.`,
                confirmLabel: 'Delete',
                cancelLabel: 'Cancel',
                destructive: true,
            });

            if (confirmed) removeRow(row, { rows, selection, group, emptyState });
        });
    });

    clearBtn?.addEventListener('click', () => {
        rows.forEach((row) => {
            row.classList.remove('is-selected');
            row.querySelector('[data-notify-select]')?.setAttribute('aria-pressed', 'false');
        });
        selection.selected.clear();
        selection.refresh();
    });

    selectAllBtn?.addEventListener('click', () => {
        const visibleRows = rows.filter((r) => !r.hidden);
        const allSelected = visibleRows.length > 0 &&
            visibleRows.every((r) => selection.selected.has(r.dataset.notifyId));

        visibleRows.forEach((row) => {
            const btn = row.querySelector('[data-notify-select]');
            if (allSelected) {
                selection.selected.delete(row.dataset.notifyId);
                row.classList.remove('is-selected');
                btn?.setAttribute('aria-pressed', 'false');
            } else {
                selection.selected.add(row.dataset.notifyId);
                row.classList.add('is-selected');
                btn?.setAttribute('aria-pressed', 'true');
            }
        });

        selection.refresh();
    });

    bulkDeleteBtn?.addEventListener('click', async () => {
        const count = selection.selected.size;
        if (count === 0) return;

        const confirmed = await confirmDialog.ask({
            title: 'Delete notifications',
            message: `Delete ${count} selected notification${count === 1 ? '' : 's'}? This can't be undone.`,
            confirmLabel: 'Delete',
            cancelLabel: 'Cancel',
            destructive: true,
        });

        if (!confirmed) return;
        const idsToRemove = new Set(selection.selected);
        
        rows.filter((row) => idsToRemove.has(row.dataset.notifyId))
            .forEach((row) => removeRow(row, { rows, selection, group, emptyState }));
    });
}

export function initNotificationsPage() {
    const page = document.querySelector('[data-notify-page]');
    if (!page) return;
    markAllRead();
    const rows = Array.from(document.querySelectorAll('[data-notify-row]'));
    if (!rows.length) return;
    const selection = createSelectionController(rows);
    initNotifyFilter(rows, selection);
    initNotifyFilterOverflowNav();
    initNotifySelectionAndDelete(rows, selection);
}
