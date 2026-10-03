// resources/js/admin/announcements.js
// Announcements management: tabs, lists, selection, create/edit modals,
// category accent picker, and confirmed deletion. Reuses the shared modal,
// confirm dialog, toast, api and loading-state modules.
//
// Toast messages never include user-entered text (the toast module renders
// its message as HTML). Names and titles only ever go through textContent
// (confirm dialog) or escapeHtml (templates).

import { api, handleApiError } from '../modules/api';
import { success, error as notifyError, info } from '../modules/toast';
import { setLoading, clearLoading } from '../modules/loading-state';
import { useModal } from '../modules/modal';
import { useConfirmDialog } from '../modules/confirm-dialog';
import {
    collapseSpaces, escapeHtml, setFieldError, clearFieldError, hasFieldError, focusFirstError,
} from './form-utils';

const API = '/admin/api/announcements';
const IMAGE_TYPES = ['image/png', 'image/jpeg', 'image/webp'];
const IMAGE_MAX_BYTES = 5 * 1024 * 1024;
const EXCERPT_MAX = 150;
const FALLBACK_HEX = '#8f8f98';

const dateFormatter = new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' });

// 'YYYY-MM-DD' is a calendar date, not an instant: build it in local time so
// it can never shift a day.
function formatDate(ymd) {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(ymd || '');
    if (!match) return ymd || '';
    return dateFormatter.format(new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3])));
}

// 'YYYY-MM-DD HH:MM:SS' timestamps are UTC.
function formatTimestamp(value) {
    const date = new Date(String(value || '').replace(' ', 'T') + 'Z');
    return Number.isNaN(date.getTime()) ? '' : dateFormatter.format(date);
}

function todayYmd() {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

const plural = (count, one, many) => `${count} ${count === 1 ? one : many}`;

function validDestination(url) {
    if (!url || url.length > 500 || /[\u0000-\u0020\u007f]/.test(url)) return false;
    if (url.startsWith('/')) return !url.startsWith('//') && !url.includes('\\');

    try {
        const parsed = new URL(url);
        return parsed.protocol === 'https:' || parsed.protocol === 'http:';
    } catch {
        return false;
    }
}

export function initAdminAnnouncements() {
    const root = document.getElementById('akdAnn');
    const dataEl = document.getElementById('akdAnnouncementsData');
    if (!root || !dataEl) return;

    const boot = JSON.parse(dataEl.textContent || '{}');
    const modal = useModal();
    const confirmDialog = useConfirmDialog();

    const state = {
        tab: 'announcements',
        canDelete: Boolean(boot.canDelete),
        pageSize: boot.pageSize || 20,
        accents: boot.accents || {},
        busy: false,
        announcements: {
            items: boot.announcements?.items || [],
            total: boot.announcements?.total || 0,
            has_more: Boolean(boot.announcements?.has_more),
            loading: false, error: Boolean(boot.loadError), seq: 0,
        },
        categories: {
            items: boot.categories || [],
            loading: false, error: Boolean(boot.loadError), seq: 0,
        },
        selected: { announcements: new Set(), categories: new Set() },
    };

    const tabButtons = Array.from(root.querySelectorAll('[data-ann-tab]'));

    function collectPanel(kind) {
        const panel = root.querySelector(`[data-ann-panel="${kind}"]`);

        return {
            panel,
            summary: panel.querySelector('[data-summary]'),
            head: panel.querySelector('[data-list-head]'),
            selectAll: panel.querySelector('[data-select-all]'),
            list: panel.querySelector('[data-list]'),
            skeleton: panel.querySelector('[data-skeleton]'),
            empty: panel.querySelector('[data-empty]'),
            error: panel.querySelector('[data-error]'),
            more: panel.querySelector('[data-more]'),
        };
    }

    const panels = {
        announcements: collectPanel('announcements'),
        categories: collectPanel('categories'),
    };

    // Fixed bottom bulk bars live outside #akdAnn (container-type would turn
    // #akdAnn into the containing block for fixed children). Absent for moderators.
    const bars = {
        announcements: document.querySelector('[data-bulkbar="announcements"]'),
        categories: document.querySelector('[data-bulkbar="categories"]'),
    };

    function syncBulkbars() {
        let anyVisible = false;

        Object.entries(bars).forEach(([kind, bar]) => {
            if (!bar) return;
            const count = state.selected[kind].size;
            const visible = state.tab === kind && count > 0;
            bar.classList.toggle('is-visible', visible);
            bar.querySelector('[data-bulk-count]').textContent = `${count} selected`;
            if (visible) anyVisible = true;
        });

        root.classList.toggle('has-selection', anyVisible);
    }

    // =====================================================================
    // Lookups and small templates
    // =====================================================================
    const hexFor = (token) => {
        const hex = state.accents[token]?.hex || '';
        return /^#[0-9a-f]{6}$/i.test(hex) ? hex : FALLBACK_HEX;
    };

    const accentLabel = (token) => state.accents[token]?.label || token;
    const categoryById = (id) => state.categories.items.find((c) => c.id === id);

    const chipHtml = (name, token) => `
        <span class="akd-ann-chip" style="--accent:${hexFor(token)}">
            <span class="akd-ann-chip__dot" aria-hidden="true"></span>
            <span class="akd-ann-chip__name">${escapeHtml(name)}</span>
        </span>`;

    const checkboxHtml = (attribute, label, checked) => `
        <label class="akd-ann-check">
            <input type="checkbox" ${attribute} ${checked ? 'checked' : ''} aria-label="${escapeHtml(label)}">
            <span class="akd-ann-check__box" aria-hidden="true">
                <i class="fa-solid fa-check"></i><i class="fa-solid fa-minus"></i>
            </span>
        </label>`;

    const actionsHtml = (editLabel, deleteLabel) => `
        <div class="akd-ann-row__actions">
            <button type="button" class="akd-ann-iconbtn" data-action="edit" aria-label="${escapeHtml(editLabel)}" title="Edit">
                <i class="fa-solid fa-pen" aria-hidden="true"></i>
            </button>
            ${state.canDelete ? `
                <button type="button" class="akd-ann-iconbtn akd-ann-iconbtn--danger" data-action="delete" aria-label="${escapeHtml(deleteLabel)}" title="Delete">
                    <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                </button>` : ''}
        </div>`;

    function announcementRow(item) {
        const selected = state.selected.announcements.has(item.id);
        const thumb = item.image_url
            ? `<img src="${escapeHtml(item.image_url)}" alt="" loading="lazy" decoding="async">`
            : '<i class="fa-regular fa-image" aria-hidden="true"></i>';

        return `
            <li class="akd-ann-row${selected ? ' is-selected' : ''}" data-id="${item.id}">
                ${state.canDelete ? checkboxHtml('data-select-row', `Select ${item.title}`, selected) : ''}
                <div class="akd-ann-row__main">
                    <span class="akd-ann-thumb">${thumb}</span>
                    <div class="akd-ann-row__text">
                        <div class="akd-ann-row__titleline">
                            <button type="button" class="akd-ann-row__title" data-action="edit">${escapeHtml(item.title)}</button>
                            ${item.featured ? '<span class="akd-ann-featured"><i class="fa-solid fa-star" aria-hidden="true"></i> Featured</span>' : ''}
                        </div>
                        <p class="akd-ann-row__excerpt">${escapeHtml(item.excerpt)}</p>
                    </div>
                </div>
                <div class="akd-ann-row__meta">
                    <div class="akd-ann-cell akd-ann-cell--category">${chipHtml(item.category_name, item.accent)}</div>
                    <div class="akd-ann-cell akd-ann-cell--date">
                        <time datetime="${escapeHtml(item.date)}">${escapeHtml(formatDate(item.date))}</time>
                    </div>
                    <div class="akd-ann-cell akd-ann-cell--dest">
                        <span class="akd-ann-cell__cta">${escapeHtml(item.cta)}</span>
                        <span class="akd-ann-cell__url">${escapeHtml(item.url)}</span>
                    </div>
                </div>
                ${actionsHtml(`Edit ${item.title}`, `Delete ${item.title}`)}
            </li>`;
    }

    function categoryRow(item) {
        const selected = state.selected.categories.has(item.id);

        return `
            <li class="akd-ann-row akd-ann-row--category${selected ? ' is-selected' : ''}" data-id="${item.id}">
                ${state.canDelete ? checkboxHtml('data-select-row', `Select ${item.name}`, selected) : ''}
                <div class="akd-ann-row__main">
                    <span class="akd-ann-accentmark" style="--accent:${hexFor(item.accent)}" aria-hidden="true"></span>
                    <div class="akd-ann-row__text">
                        <div class="akd-ann-row__titleline">
                            <button type="button" class="akd-ann-row__title" data-action="edit">${escapeHtml(item.name)}</button>
                        </div>
                        <p class="akd-ann-row__excerpt">${escapeHtml(accentLabel(item.accent))} accent</p>
                    </div>
                </div>
                <div class="akd-ann-row__meta">
                    <div class="akd-ann-cell">${item.usage_count > 0
                        ? plural(item.usage_count, 'announcement', 'announcements')
                        : '<span class="akd-ann-cell__muted">Not used yet</span>'}</div>
                    <div class="akd-ann-cell akd-ann-cell--date">${escapeHtml(formatTimestamp(item.created_at))}</div>
                </div>
                ${actionsHtml(`Edit ${item.name}`, `Delete ${item.name}`)}
            </li>`;
    }

    // =====================================================================
    // Rendering
    // =====================================================================
    function setEmpty(kind, { icon, title, text, label, action }) {
        const empty = panels[kind].empty;
        empty.querySelector('[data-empty-icon]').className = `fa-solid ${icon}`;
        empty.querySelector('[data-empty-title]').textContent = title;
        empty.querySelector('[data-empty-text]').textContent = text;
        const button = empty.querySelector('[data-empty-action]');
        button.textContent = label;
        button.dataset.action = action;
    }

    function fillEmpty(kind) {
        if (kind === 'announcements') {
            const noCategories = state.categories.items.length === 0;

            setEmpty(kind, noCategories ? {
                icon: 'fa-tags',
                title: 'Create a category first',
                text: 'Every announcement is filed under a category. Add one to get started.',
                label: 'Create a category',
                action: 'goto-categories',
            } : {
                icon: 'fa-bullhorn',
                title: 'No announcements yet',
                text: 'Publish your first announcement to show it on the member Announcements page.',
                label: 'New announcement',
                action: 'create',
            });
            return;
        }

        setEmpty(kind, {
            icon: 'fa-tags',
            title: 'No categories yet',
            text: 'Categories group announcements and give each one its own accent colour.',
            label: 'New category',
            action: 'create',
        });
    }

    function render(kind) {
        const data = state[kind];
        const els = panels[kind];
        const hasItems = data.items.length > 0;
        const rowTemplate = kind === 'announcements' ? announcementRow : categoryRow;

        els.list.innerHTML = data.items.map(rowTemplate).join('');
        els.list.classList.toggle('is-loading', data.loading && hasItems);
        els.skeleton.hidden = !(data.loading && !hasItems);
        els.head.hidden = !hasItems;

        const showError = data.error && !hasItems && !data.loading;
        els.error.hidden = !showError;

        const showEmpty = !hasItems && !data.error && !data.loading;
        els.empty.hidden = !showEmpty;
        if (showEmpty) fillEmpty(kind);

        if (els.more) els.more.hidden = !(kind === 'announcements' && data.has_more);

        const total = kind === 'announcements' ? data.total : data.items.length;
        els.summary.textContent = kind === 'announcements'
            ? plural(total, 'announcement', 'announcements')
            : plural(total, 'category', 'categories');

        root.querySelector(`[data-tab-count="${kind}"]`).textContent = String(total);
        updateSelection(kind);
    }

    function updateSelection(kind) {
        const els = panels[kind];
        const items = state[kind].items;

        if (!state.canDelete) return;

        const set = state.selected[kind];
        const count = set.size;
        syncBulkbars();

        els.list.querySelectorAll('[data-id]').forEach((row) => {
            const on = set.has(Number(row.dataset.id));
            row.classList.toggle('is-selected', on);
            const box = row.querySelector('[data-select-row]');
            if (box) box.checked = on;
        });

        if (els.selectAll) {
            const visibleSelected = items.filter((item) => set.has(item.id)).length;
            els.selectAll.checked = items.length > 0 && visibleSelected === items.length;
            els.selectAll.indeterminate = visibleSelected > 0 && visibleSelected < items.length;
        }
    }

    function pruneSelection(kind) {
        const ids = new Set(state[kind].items.map((item) => item.id));
        state.selected[kind].forEach((id) => { if (!ids.has(id)) state.selected[kind].delete(id); });
    }

    // =====================================================================
    // Tabs
    // =====================================================================
    function setTab(tab, { focus = false } = {}) {
        if (!panels[tab]) return;
        state.tab = tab;

        tabButtons.forEach((button) => {
            const active = button.dataset.annTab === tab;
            button.setAttribute('aria-selected', String(active));
            button.tabIndex = active ? 0 : -1;
            if (active && focus) button.focus();
        });

        Object.entries(panels).forEach(([kind, els]) => { els.panel.hidden = kind !== tab; });
        history.replaceState(null, '', tab === 'categories'
            ? '#categories' : location.pathname + location.search);
        syncBulkbars();
    }

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => setTab(button.dataset.annTab));

        button.addEventListener('keydown', (event) => {
            const order = tabButtons.map((b) => b.dataset.annTab);
            const index = order.indexOf(state.tab);
            let next = null;

            if (event.key === 'ArrowRight') next = order[(index + 1) % order.length];
            else if (event.key === 'ArrowLeft') next = order[(index - 1 + order.length) % order.length];
            else if (event.key === 'Home') next = order[0];
            else if (event.key === 'End') next = order[order.length - 1];

            if (next) {
                event.preventDefault();
                setTab(next, { focus: true });
            }
        });
    });

    // =====================================================================
    // Data loading
    // =====================================================================
    async function fetchAnnouncements({ append = false } = {}) {
        const data = state.announcements;
        const token = ++data.seq;
        data.loading = true;
        data.error = false;
        render('announcements');

        try {
            const offset = append ? data.items.length : 0;
            const limit = append
                ? state.pageSize
                : Math.min(100, Math.max(state.pageSize, data.items.length));
            const result = await api(`${API}?offset=${offset}&limit=${limit}`);
            if (token !== data.seq) return;

            const incoming = result.items || [];

            if (append) {
                const known = new Set(data.items.map((item) => item.id));
                data.items = data.items.concat(incoming.filter((item) => !known.has(item.id)));
            } else {
                data.items = incoming;
            }

            data.total = result.total ?? data.items.length;
            data.has_more = Boolean(result.has_more);
            pruneSelection('announcements');
        } catch (err) {
            if (token !== data.seq) return;
            data.error = true;
            if (data.items.length) handleApiError(err, 'Could not refresh announcements.');
        } finally {
            if (token === data.seq) {
                data.loading = false;
                render('announcements');
            }
        }
    }

    async function fetchCategories() {
        const data = state.categories;
        const token = ++data.seq;
        data.loading = true;
        data.error = false;
        render('categories');

        try {
            const result = await api(`${API}/categories`);
            if (token !== data.seq) return;
            data.items = result.items || [];
            pruneSelection('categories');
        } catch (err) {
            if (token !== data.seq) return;
            data.error = true;
            if (data.items.length) handleApiError(err, 'Could not refresh categories.');
        } finally {
            if (token === data.seq) {
                data.loading = false;
                render('categories');
            }
        }
    }

    // Category changes alter announcement rows (name, accent) and announcement
    // changes alter category usage counts, so a mutation always refreshes both.
    const refreshAll = () => Promise.all([fetchAnnouncements(), fetchCategories()]);

    async function runExclusive(task) {
        if (state.busy) return;
        state.busy = true;
        root.classList.add('is-busy');
        root.setAttribute('aria-busy', 'true');
        Object.values(bars).forEach((bar) => bar?.classList.add('is-busy'));

        try {
            await task();
        } finally {
            state.busy = false;
            root.classList.remove('is-busy');
            root.setAttribute('aria-busy', 'false');
            Object.values(bars).forEach((bar) => bar?.classList.remove('is-busy'));
        }
    }

    // =====================================================================
    // Deletion
    // =====================================================================
    async function deleteAnnouncements(ids) {
        if (!state.canDelete || !ids.length || state.busy) return;

        const single = ids.length === 1;
        const item = state.announcements.items.find((entry) => entry.id === ids[0]);

        const confirmed = await confirmDialog.ask({
            title: single ? 'Delete this announcement?' : `Delete ${ids.length} announcements?`,
            message: single
                ? `"${item?.title ?? 'This announcement'}" will be removed from the member Announcements page. This can't be undone.`
                : `These ${ids.length} announcements will be removed from the member Announcements page. This can't be undone.`,
            confirmLabel: single ? 'Delete' : `Delete ${ids.length}`,
            cancelLabel: 'Cancel',
            destructive: true,
        });

        if (!confirmed) return;

        await runExclusive(async () => {
            try {
                const result = await api(API, { method: 'DELETE', body: { ids } });

                if (result.success === false) {
                    notifyError(result.message || 'Could not delete the announcements.');
                    return;
                }

                ids.forEach((id) => state.selected.announcements.delete(id));
                success(single ? 'Announcement deleted.'
                    : `${plural(result.deleted ?? ids.length, 'announcement', 'announcements')} deleted.`);
                await refreshAll();
            } catch (err) {
                handleApiError(err, 'Could not delete the announcements.');
            }
        });
    }

    async function performCategoryDelete(ids, reassignTo) {
        return runExclusive(async () => {
            try {
                const result = await api(`${API}/categories`, {
                    method: 'DELETE',
                    body: { ids, reassign_to: reassignTo },
                });

                if (result.success === false) {
                    notifyError(result.message || 'Could not delete the categories.');
                    return;
                }

                ids.forEach((id) => state.selected.categories.delete(id));
                const moved = result.moved || 0;
                success(moved > 0
                    ? `${plural(result.deleted ?? ids.length, 'category', 'categories')} deleted. ${plural(moved, 'announcement', 'announcements')} moved.`
                    : `${plural(result.deleted ?? ids.length, 'category', 'categories')} deleted.`);
                await modal.close();
                await refreshAll();
            } catch (err) {
                handleApiError(err, 'Could not delete the categories.');
            }
        });
    }

    async function deleteCategories(ids) {
        if (!state.canDelete || !ids.length || state.busy) return;

        const items = ids.map(categoryById).filter(Boolean);
        const inUse = items.filter((category) => category.usage_count > 0);

        // Announcements would be orphaned: ask where they should go instead.
        if (inUse.length) {
            openReassignDialog(ids);
            return;
        }

        const single = ids.length === 1;
        const confirmed = await confirmDialog.ask({
            title: single ? 'Delete this category?' : `Delete ${ids.length} categories?`,
            message: single
                ? `"${items[0]?.name ?? 'This category'}" has no announcements and will be removed. This can't be undone.`
                : `These ${ids.length} categories have no announcements and will be removed. This can't be undone.`,
            confirmLabel: single ? 'Delete' : `Delete ${ids.length}`,
            cancelLabel: 'Cancel',
            destructive: true,
        });

        if (confirmed) await performCategoryDelete(ids, null);
    }

    // =====================================================================
    // Announcement modal
    // =====================================================================
    function openAnnouncementForm(item = null) {
        if (state.busy) return;

        if (!state.categories.items.length) {
            info('Create a category first, then you can add announcements.');
            setTab('categories');
            return;
        }

        const isEdit = item !== null;
        const v = item ?? {
            title: '', excerpt: '', cta: '', url: '', date: todayYmd(), featured: false,
            category_id: null, image: null, image_url: null, image_alt: '',
        };

        const categoryChoices = state.categories.items.map((category) => `
            <label class="akd-ann-choice__item" style="--accent:${hexFor(category.accent)}">
                <input type="radio" name="category_id" value="${category.id}" ${category.id === v.category_id ? 'checked' : ''}>
                <span class="akd-ann-choice__body">
                    <span class="akd-ann-chip__dot" aria-hidden="true"></span>
                    ${escapeHtml(category.name)}
                </span>
            </label>`).join('');

        const template = document.createElement('template');
        template.innerHTML = `
            <form class="akd-ann-form" id="annForm" novalidate>
                <div class="akd-admin-field" data-field="title">
                    <label class="akd-admin-field__label" for="annTitle">Title</label>
                    <div class="akd-admin-field__control">
                        <input type="text" id="annTitle" name="title" class="akd-admin-field__input" maxlength="120"
                            value="${escapeHtml(v.title)}" autocomplete="off" aria-describedby="annTitle-error">
                    </div>
                    <p class="akd-admin-field__error" id="annTitle-error" data-field-error aria-live="polite"></p>
                </div>

                <div class="akd-admin-field" data-field="excerpt">
                    <div class="akd-ann-labelrow">
                        <label class="akd-admin-field__label" for="annExcerpt">Excerpt</label>
                        <span class="akd-ann-counter" data-counter aria-hidden="true"></span>
                    </div>
                    <textarea id="annExcerpt" name="excerpt" class="akd-admin-field__input akd-ann-textarea"
                        maxlength="${EXCERPT_MAX}" rows="3" aria-describedby="annExcerpt-error">${escapeHtml(v.excerpt)}</textarea>
                    <p class="akd-admin-field__error" id="annExcerpt-error" data-field-error aria-live="polite"></p>
                </div>

                <fieldset class="akd-admin-field akd-ann-fieldset" data-field="category_id">
                    <legend class="akd-admin-field__label">Category</legend>
                    <div class="akd-ann-choice">${categoryChoices}</div>
                    <p class="akd-admin-field__error" data-field-error aria-live="polite"></p>
                </fieldset>

                <div class="akd-ann-form__grid">
                    <div class="akd-admin-field" data-field="date">
                        <label class="akd-admin-field__label" for="annDate">Published on</label>
                        <div class="akd-admin-field__control">
                            <input type="date" id="annDate" name="date" class="akd-admin-field__input"
                                value="${escapeHtml(v.date)}" aria-describedby="annDate-error">
                        </div>
                        <p class="akd-admin-field__error" id="annDate-error" data-field-error aria-live="polite"></p>
                    </div>

                    <div class="akd-admin-field">
                        <span class="akd-admin-field__label" id="annFeaturedLabel">Featured</span>
                        <label class="akd-ann-switch">
                            <input type="checkbox" name="featured" role="switch" aria-labelledby="annFeaturedLabel" ${v.featured ? 'checked' : ''}>
                            <span class="akd-ann-switch__track" aria-hidden="true"></span>
                            <span class="akd-ann-switch__text">Show a Featured badge</span>
                        </label>
                    </div>
                </div>

                <div class="akd-ann-form__grid">
                    <div class="akd-admin-field" data-field="cta">
                        <label class="akd-admin-field__label" for="annCta">Button label</label>
                        <div class="akd-admin-field__control">
                            <input type="text" id="annCta" name="cta" class="akd-admin-field__input" maxlength="30"
                                value="${escapeHtml(v.cta)}" placeholder="Vote Now" autocomplete="off" aria-describedby="annCta-error">
                        </div>
                        <p class="akd-admin-field__error" id="annCta-error" data-field-error aria-live="polite"></p>
                    </div>

                    <div class="akd-admin-field" data-field="url">
                        <label class="akd-admin-field__label" for="annUrl">Destination</label>
                        <div class="akd-admin-field__control">
                            <input type="text" id="annUrl" name="url" class="akd-admin-field__input" maxlength="500"
                                value="${escapeHtml(v.url)}" placeholder="/member/awards/voting" autocomplete="off"
                                autocapitalize="none" spellcheck="false" aria-describedby="annUrl-error">
                        </div>
                        <p class="akd-admin-field__error" id="annUrl-error" data-field-error aria-live="polite"></p>
                    </div>
                </div>

                <div class="akd-admin-field" data-field="image">
                    <span class="akd-admin-field__label">Artwork <span class="akd-ann-optional">Optional</span></span>
                    <div class="akd-ann-image">
                        <div class="akd-ann-image__preview" data-image-preview></div>
                        <div class="akd-ann-image__controls">
                            <input type="file" accept="image/png,image/jpeg,image/webp" hidden data-image-input aria-label="Choose artwork">
                            <div class="akd-ann-image__buttons">
                                <button type="button" class="akd-admin-btn" data-image-choose>
                                    <i class="fa-regular fa-image" aria-hidden="true"></i> <span data-image-choose-label>Choose image</span>
                                </button>
                                <button type="button" class="akd-admin-btn akd-admin-btn--ghost akd-admin-btn--danger" data-image-remove hidden>Remove</button>
                            </div>
                            <p class="akd-admin-field__hint">PNG, JPG or WebP, up to 5MB. Without artwork, members see a neutral placeholder.</p>
                        </div>
                    </div>
                    <p class="akd-admin-field__error" data-field-error aria-live="polite"></p>
                </div>

                <div class="akd-admin-field" data-field="image_alt" data-alt-field hidden>
                    <label class="akd-admin-field__label" for="annAlt">Artwork description</label>
                    <div class="akd-admin-field__control">
                        <input type="text" id="annAlt" name="image_alt" class="akd-admin-field__input" maxlength="200"
                            value="${escapeHtml(v.image_alt ?? '')}" autocomplete="off" aria-describedby="annAlt-hint annAlt-error">
                    </div>
                    <p class="akd-admin-field__hint" id="annAlt-hint">Read by screen readers. Defaults to the title when left empty.</p>
                    <p class="akd-admin-field__error" id="annAlt-error" data-field-error aria-live="polite"></p>
                </div>
            </form>`;

        const form = template.content.firstElementChild;
        const field = (name) => form.querySelector(`[data-field="${name}"]`);
        const fields = {
            title: field('title'), excerpt: field('excerpt'), category_id: field('category_id'),
            date: field('date'), cta: field('cta'), url: field('url'),
            image: field('image'), image_alt: field('image_alt'),
        };
        const titleInput = fields.title.querySelector('input');
        const excerptInput = fields.excerpt.querySelector('textarea');
        const dateInput = fields.date.querySelector('input');
        const ctaInput = fields.cta.querySelector('input');
        const urlInput = fields.url.querySelector('input');
        const altInput = fields.image_alt.querySelector('input');
        const featuredInput = form.querySelector('input[name="featured"]');
        const counter = form.querySelector('[data-counter]');
        const preview = form.querySelector('[data-image-preview]');
        const imageInput = form.querySelector('[data-image-input]');
        const chooseBtn = form.querySelector('[data-image-choose]');
        const chooseLabel = form.querySelector('[data-image-choose-label]');
        const removeBtn = form.querySelector('[data-image-remove]');

        const image = { file: null, remove: false, objectUrl: null };
        const selectedCategory = () => form.querySelector('input[name="category_id"]:checked')?.value ?? '';

        const read = () => ({
            category_id: selectedCategory(),
            title: collapseSpaces(titleInput.value),
            excerpt: collapseSpaces(excerptInput.value),
            cta: collapseSpaces(ctaInput.value),
            url: urlInput.value.trim(),
            date: dateInput.value,
            featured: featuredInput.checked,
            image_alt: collapseSpaces(altInput.value),
        });

        const initial = JSON.stringify(read());
        let saving = false;
        let committed = false;

        const isDirty = () => JSON.stringify(read()) !== initial || image.file !== null || image.remove;

        function renderImage() {
            const existing = isEdit && !image.remove ? v.image_url : null;
            const src = image.objectUrl || existing;
            const hasImage = Boolean(image.file || (isEdit && v.image && !image.remove));

            preview.innerHTML = src
                ? `<img src="${escapeHtml(src)}" alt="">`
                : '<i class="fa-regular fa-image" aria-hidden="true"></i>';
            removeBtn.hidden = !hasImage;
            chooseLabel.textContent = hasImage ? 'Replace image' : 'Choose image';
            fields.image_alt.hidden = !hasImage;
        }

        // Code points, to match the server's mb_strlen().
        function updateCounter() {
            const length = Array.from(excerptInput.value).length;
            counter.textContent = `${length} / ${EXCERPT_MAX}`;
            counter.classList.toggle('is-over', length > EXCERPT_MAX);
        }

        chooseBtn.addEventListener('click', () => imageInput.click());

        imageInput.addEventListener('change', () => {
            const file = imageInput.files?.[0];
            imageInput.value = '';
            if (!file) return;
            clearFieldError(fields.image);

            if (!IMAGE_TYPES.includes(file.type)) {
                setFieldError(fields.image, 'Please choose a PNG, JPG or WebP image.');
                return;
            }

            if (file.size > IMAGE_MAX_BYTES) {
                setFieldError(fields.image, 'Image must not exceed 5MB.');
                return;
            }

            if (image.objectUrl) URL.revokeObjectURL(image.objectUrl);
            image.file = file;
            image.remove = false;
            image.objectUrl = URL.createObjectURL(file);
            renderImage();
        });

        removeBtn.addEventListener('click', () => {
            if (image.objectUrl) URL.revokeObjectURL(image.objectUrl);
            image.file = null;
            image.objectUrl = null;
            image.remove = isEdit && Boolean(v.image);
            renderImage();
        });

        const validators = {
            title: () => {
                const value = collapseSpaces(titleInput.value);
                if (!value) return 'Title is required.';
                if (value.length < 3 || value.length > 120) return 'Title must be between 3 and 120 characters.';
                return null;
            },
            excerpt: () => {
                const value = collapseSpaces(excerptInput.value);
                const length = Array.from(value).length;
                if (!value) return 'Excerpt is required.';
                if (length < 10 || length > EXCERPT_MAX) return `Excerpt must be between 10 and ${EXCERPT_MAX} characters.`;
                return null;
            },
            category_id: () => (selectedCategory() ? null : 'Choose a category.'),
            date: () => (dateInput.value ? null : 'Date is required.'),
            cta: () => {
                const value = collapseSpaces(ctaInput.value);
                if (!value) return 'Button label is required.';
                if (value.length < 2 || value.length > 30) return 'Button label must be between 2 and 30 characters.';
                return null;
            },
            url: () => {
                const value = urlInput.value.trim();
                if (!value) return 'Destination URL is required.';
                if (!validDestination(value)) return 'Use a path such as /member/awards/voting or a full https:// link.';
                return null;
            },
        };

        function validate() {
            let valid = true;

            Object.entries(validators).forEach(([name, check]) => {
                clearFieldError(fields[name]);
                const message = check();

                if (message) {
                    setFieldError(fields[name], message);
                    valid = false;
                }
            });

            return valid;
        }

        // Re-validate a field only once it is already showing an error.
        [['title', titleInput], ['excerpt', excerptInput], ['date', dateInput],
         ['cta', ctaInput], ['url', urlInput]].forEach(([name, input]) => {
            input.addEventListener('input', () => {
                if (!hasFieldError(fields[name])) return;
                clearFieldError(fields[name]);
                const message = validators[name]();
                if (message) setFieldError(fields[name], message);
            });
        });

        excerptInput.addEventListener('input', updateCounter);

        form.querySelectorAll('input[name="category_id"]').forEach((radio) => {
            radio.addEventListener('change', () => clearFieldError(fields.category_id));
        });

        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'akd-admin-btn';
        cancelBtn.textContent = 'Cancel';
        cancelBtn.addEventListener('click', () => modal.close());

        const saveBtn = document.createElement('button');
        saveBtn.type = 'submit';
        saveBtn.setAttribute('form', form.id);
        saveBtn.className = 'akd-admin-btn akd-admin-btn--primary';
        saveBtn.textContent = isEdit ? 'Save changes' : 'Create announcement';

        const footer = document.createDocumentFragment();
        footer.append(cancelBtn, saveBtn);

        function buildFormData() {
            const values = read();
            const data = new FormData();

            data.set('category_id', values.category_id);
            data.set('title', values.title);
            data.set('excerpt', values.excerpt);
            data.set('cta', values.cta);
            data.set('url', values.url);
            data.set('date', values.date);
            data.set('featured', values.featured ? '1' : '0');
            data.set('image_alt', values.image_alt);

            if (image.file) data.set('image', image.file);
            else if (image.remove) data.set('remove_image', '1');

            return data;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (saving) return;

            if (!validate()) {
                focusFirstError(form);
                return;
            }

            saving = true;
            cancelBtn.disabled = true;
            setLoading(saveBtn, isEdit ? 'Saving...' : 'Creating...');

            try {
                const result = await api(isEdit ? `${API}/${item.id}` : API, {
                    method: 'POST',
                    body: buildFormData(),
                });

                if (result.success === false) {
                    const unmatched = [];

                    Object.entries(result.errors || {}).forEach(([key, message]) => {
                        if (fields[key]) setFieldError(fields[key], message);
                        else unmatched.push(message);
                    });

                    if (!result.errors) unmatched.push(result.message || 'Could not save the announcement.');
                    if (unmatched.length) notifyError(unmatched[0]);
                    focusFirstError(form);
                    return;
                }

                committed = true;
                success(isEdit ? 'Announcement updated.' : 'Announcement created.');
                await modal.close();
                await refreshAll();
            } catch (err) {
                handleApiError(err, 'Could not save the announcement.');
            } finally {
                saving = false;
                cancelBtn.disabled = false;
                clearLoading(saveBtn);
            }
        });

        renderImage();
        updateCounter();

        modal.open({
            title: isEdit ? 'Edit announcement' : 'New announcement',
            subtitle: isEdit ? 'Changes appear on the member Announcements page immediately.'
                : 'Published announcements appear on the member Announcements page.',
            content: form,
            footer,
            size: 'lg',
            className: 'akd-ann-modal',
            initialFocus: titleInput,
            beforeClose: async () => {
                // A successful save closes the modal while `saving` is still true
                // (it is reset in `finally`), so `committed` must be checked first.
                if (committed) {
                    if (image.objectUrl) URL.revokeObjectURL(image.objectUrl);
                    return true;
                }

                if (saving) return false;

                if (!isDirty()) {
                    if (image.objectUrl) URL.revokeObjectURL(image.objectUrl);
                    return true;
                }

                const discard = await confirmDialog.ask({
                    title: 'Discard changes?',
                    message: 'You have unsaved changes. Are you sure you want to discard them?',
                    confirmLabel: 'Discard',
                    cancelLabel: 'Continue Editing',
                    destructive: true,
                });

                if (discard && image.objectUrl) URL.revokeObjectURL(image.objectUrl);
                return discard;
            },
        });
    }

    // =====================================================================
    // Category modal (name + accent picker)
    // =====================================================================
    function openCategoryForm(item = null) {
        if (state.busy) return;

        const isEdit = item !== null;
        const holders = {};
        state.categories.items.forEach((category) => { holders[category.accent] = category; });

        const isTaken = (token) => holders[token] && holders[token].id !== item?.id;
        const available = Object.keys(state.accents).filter((token) => !isTaken(token));
        const startAccent = isEdit ? item.accent : (available[0] ?? '');

        const swatches = Object.entries(state.accents).map(([token, meta]) => {
            const taken = isTaken(token);
            const holder = holders[token];

            return `
                <label class="akd-ann-swatch${taken ? ' is-disabled' : ''}" style="--accent:${hexFor(token)}"
                    ${taken ? `title="Used by ${escapeHtml(holder.name)}"` : ''}>
                    <input type="radio" name="accent" value="${escapeHtml(token)}" ${token === startAccent ? 'checked' : ''} ${taken ? 'disabled' : ''}>
                    <span class="akd-ann-swatch__body">
                        <span class="akd-ann-swatch__dot" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                        <span class="akd-ann-swatch__label">${escapeHtml(meta.label || token)}</span>
                        ${taken ? `<span class="akd-ann-swatch__note" aria-hidden="true">In use</span>
                            <span class="visually-hidden">, used by ${escapeHtml(holder.name)}</span>` : ''}
                    </span>
                </label>`;
        }).join('');

        const template = document.createElement('template');
        template.innerHTML = `
            <form class="akd-ann-form" id="annCategoryForm" novalidate>
                <div class="akd-ann-preview" aria-live="polite">
                    <span class="akd-ann-preview__label">Preview</span>
                    <span class="akd-ann-chip" data-preview style="--accent:${hexFor(startAccent)}">
                        <span class="akd-ann-chip__dot" aria-hidden="true"></span>
                        <span class="akd-ann-chip__name" data-preview-name></span>
                    </span>
                </div>

                <div class="akd-admin-field" data-field="name">
                    <label class="akd-admin-field__label" for="annCatName">Name</label>
                    <div class="akd-admin-field__control">
                        <input type="text" id="annCatName" name="name" class="akd-admin-field__input" maxlength="30"
                            value="${escapeHtml(item?.name ?? '')}" placeholder="Anime Awards" autocomplete="off"
                            aria-describedby="annCatName-error">
                    </div>
                    <p class="akd-admin-field__error" id="annCatName-error" data-field-error aria-live="polite"></p>
                </div>

                <fieldset class="akd-admin-field akd-ann-fieldset" data-field="accent">
                    <legend class="akd-admin-field__label">Accent</legend>
                    <div class="akd-ann-palette">${swatches}</div>
                    <p class="akd-admin-field__hint">${available.length || isEdit
                        ? 'Each category has its own accent. Colours already in use are dimmed.'
                        : 'Every accent is already assigned. Edit or delete another category to free one up.'}</p>
                    <p class="akd-admin-field__error" data-field-error aria-live="polite"></p>
                </fieldset>
            </form>`;

        const form = template.content.firstElementChild;
        const nameField = form.querySelector('[data-field="name"]');
        const accentField = form.querySelector('[data-field="accent"]');
        const nameInput = nameField.querySelector('input');
        const previewChip = form.querySelector('[data-preview]');
        const previewName = form.querySelector('[data-preview-name]');
        const selectedAccent = () => form.querySelector('input[name="accent"]:checked')?.value ?? '';

        const initial = JSON.stringify([collapseSpaces(nameInput.value), selectedAccent()]);
        let saving = false;
        let committed = false;

        const isDirty = () => JSON.stringify([collapseSpaces(nameInput.value), selectedAccent()]) !== initial;

        function updatePreview() {
            previewName.textContent = collapseSpaces(nameInput.value) || 'Category name';
            previewChip.style.setProperty('--accent', hexFor(selectedAccent()));
        }

        nameInput.addEventListener('input', () => {
            if (hasFieldError(nameField)) clearFieldError(nameField);
            updatePreview();
        });

        form.querySelectorAll('input[name="accent"]').forEach((radio) => {
            radio.addEventListener('change', () => {
                clearFieldError(accentField);
                updatePreview();
            });
        });

        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'akd-admin-btn';
        cancelBtn.textContent = 'Cancel';
        cancelBtn.addEventListener('click', () => modal.close());

        const saveBtn = document.createElement('button');
        saveBtn.type = 'submit';
        saveBtn.setAttribute('form', form.id);
        saveBtn.className = 'akd-admin-btn akd-admin-btn--primary';
        saveBtn.textContent = isEdit ? 'Save changes' : 'Create category';
        saveBtn.disabled = !isEdit && available.length === 0;

        const footer = document.createDocumentFragment();
        footer.append(cancelBtn, saveBtn);

        function validate() {
            let valid = true;
            clearFieldError(nameField);
            clearFieldError(accentField);
            const name = collapseSpaces(nameInput.value);

            if (!name) {
                setFieldError(nameField, 'Category name is required.');
                valid = false;
            } else if (name.length < 2 || name.length > 30) {
                setFieldError(nameField, 'Name must be between 2 and 30 characters.');
                valid = false;
            }

            if (!selectedAccent()) {
                setFieldError(accentField, 'Choose an accent.');
                valid = false;
            }

            return valid;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (saving) return;

            if (!validate()) {
                focusFirstError(form);
                return;
            }

            saving = true;
            cancelBtn.disabled = true;
            setLoading(saveBtn, isEdit ? 'Saving...' : 'Creating...');

            try {
                const result = await api(isEdit ? `${API}/categories/${item.id}` : `${API}/categories`, {
                    method: isEdit ? 'PUT' : 'POST',
                    body: { name: collapseSpaces(nameInput.value), accent: selectedAccent() },
                });

                if (result.success === false) {
                    const map = { name: nameField, accent: accentField };
                    const unmatched = [];

                    Object.entries(result.errors || {}).forEach(([key, message]) => {
                        if (map[key]) setFieldError(map[key], message);
                        else unmatched.push(message);
                    });

                    if (!result.errors) unmatched.push(result.message || 'Could not save the category.');
                    if (unmatched.length) notifyError(unmatched[0]);
                    focusFirstError(form);

                    // Someone else may have just taken this accent: reflect it.
                    if (result.errors?.accent) fetchCategories();
                    return;
                }

                committed = true;
                success(isEdit ? 'Category updated.' : 'Category created.');
                await modal.close();
                await refreshAll();
            } catch (err) {
                handleApiError(err, 'Could not save the category.');
            } finally {
                saving = false;
                cancelBtn.disabled = false;
                clearLoading(saveBtn);
                if (!isEdit) saveBtn.disabled = available.length === 0;
            }
        });

        updatePreview();

        modal.open({
            title: isEdit ? 'Edit category' : 'New category',
            subtitle: 'Categories appear as filter tabs on the member Announcements page.',
            content: form,
            footer,
            size: 'sm',
            className: 'akd-ann-modal',
            initialFocus: nameInput,
            beforeClose: async () => {
                if (committed) return true;
                if (saving) return false;
                if (!isDirty()) return true;

                return confirmDialog.ask({
                    title: 'Discard changes?',
                    message: 'You have unsaved changes. Are you sure you want to discard them?',
                    confirmLabel: 'Discard',
                    cancelLabel: 'Continue Editing',
                    destructive: true,
                });
            },
        });
    }

    // =====================================================================
    // Reassign-and-delete modal (categories that still have announcements)
    // =====================================================================
    function openReassignDialog(ids) {
        const selected = ids.map(categoryById).filter(Boolean);
        const inUse = selected.filter((category) => category.usage_count > 0);
        const moving = inUse.reduce((total, category) => total + category.usage_count, 0);
        const targets = state.categories.items.filter((category) => !ids.includes(category.id));

        const usedList = inUse.map((category) => `
            <li>${chipHtml(category.name, category.accent)}
                <span class="akd-ann-reassign__count">${escapeHtml(plural(category.usage_count, 'announcement', 'announcements'))}</span>
            </li>`).join('');

        const choices = targets.map((category) => `
            <label class="akd-ann-choice__item" style="--accent:${hexFor(category.accent)}">
                <input type="radio" name="target" value="${category.id}">
                <span class="akd-ann-choice__body">
                    <span class="akd-ann-chip__dot" aria-hidden="true"></span>
                    ${escapeHtml(category.name)}
                </span>
            </label>`).join('');

        const template = document.createElement('template');
        template.innerHTML = `
            <div class="akd-ann-form akd-ann-reassign">
                <p class="akd-ann-reassign__lead">
                    ${escapeHtml(plural(moving, 'announcement', 'announcements'))}
                    ${inUse.length === 1 ? 'is' : 'are'} filed under ${inUse.length === 1 ? 'this category' : 'these categories'}.
                    A category can't be deleted while announcements use it, so choose where they should go.
                </p>
                <ul class="akd-ann-reassign__list">${usedList}</ul>

                ${targets.length ? `
                    <fieldset class="akd-admin-field akd-ann-fieldset">
                        <legend class="akd-admin-field__label">Move announcements to</legend>
                        <div class="akd-ann-choice">${choices}</div>
                    </fieldset>` : `
                    <div class="akd-admin-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        <p>There is no other category to move them to. Create another category first.</p>
                    </div>`}
            </div>`;

        const content = template.content.firstElementChild;
        const selectedTarget = () => content.querySelector('input[name="target"]:checked')?.value ?? '';

        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'akd-admin-btn';
        cancelBtn.textContent = 'Cancel';
        cancelBtn.addEventListener('click', () => modal.close());

        const continueBtn = document.createElement('button');
        continueBtn.type = 'button';
        continueBtn.className = 'akd-admin-btn akd-admin-btn--danger';
        continueBtn.textContent = 'Move and delete';
        continueBtn.disabled = true;

        content.querySelectorAll('input[name="target"]').forEach((radio) => {
            radio.addEventListener('change', () => { continueBtn.disabled = !selectedTarget(); });
        });

        continueBtn.addEventListener('click', async () => {
            const targetId = Number(selectedTarget());
            const target = categoryById(targetId);
            if (!target || state.busy) return;

            const confirmed = await confirmDialog.ask({
                title: ids.length === 1 ? 'Delete this category?' : `Delete ${ids.length} categories?`,
                message: `${plural(moving, 'announcement', 'announcements')} will be moved to "${target.name}", then ${ids.length === 1 ? 'the category' : 'the categories'} will be deleted. This can't be undone.`,
                confirmLabel: 'Move and delete',
                cancelLabel: 'Cancel',
                destructive: true,
            });

            if (confirmed) await performCategoryDelete(ids, targetId);
        });

        const footer = document.createDocumentFragment();
        footer.append(cancelBtn, continueBtn);

        modal.open({
            title: ids.length === 1 ? 'Delete category' : `Delete ${ids.length} categories`,
            subtitle: 'Nothing is deleted until you confirm.',
            content,
            footer,
            className: 'akd-ann-modal',
        });
    }

    // =====================================================================
    // Delegated events
    // =====================================================================
    root.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-action], [data-empty-action]');
        if (!trigger || !root.contains(trigger)) return;

        const action = trigger.dataset.action;
        const kind = trigger.closest('[data-ann-panel]')?.dataset.annPanel ?? state.tab;
        const rowId = Number(trigger.closest('[data-id]')?.dataset.id) || null;

        switch (action) {
            case 'create':
                kind === 'announcements' ? openAnnouncementForm() : openCategoryForm();
                break;

            case 'goto-categories':
                setTab('categories');
                break;

            case 'edit': {
                const item = state[kind].items.find((entry) => entry.id === rowId);
                if (!item) break;
                kind === 'announcements' ? openAnnouncementForm(item) : openCategoryForm(item);
                break;
            }

            case 'delete':
                if (rowId === null) break;
                kind === 'announcements' ? deleteAnnouncements([rowId]) : deleteCategories([rowId]);
                break;

            case 'load-more': {
                setLoading(trigger, 'Loading...');
                fetchAnnouncements({ append: true }).finally(() => clearLoading(trigger));
                break;
            }

            case 'retry':
                kind === 'announcements' ? fetchAnnouncements() : fetchCategories();
                break;

            default:
                break;
        }
    });

    root.addEventListener('change', (event) => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || input.type !== 'checkbox') return;

        const kind = input.closest('[data-ann-panel]')?.dataset.annPanel;
        if (!kind) return;

        const set = state.selected[kind];

        if (input.hasAttribute('data-select-all')) {
            state[kind].items.forEach((item) => (input.checked ? set.add(item.id) : set.delete(item.id)));
        } else if (input.hasAttribute('data-select-row')) {
            const id = Number(input.closest('[data-id]')?.dataset.id);
            if (input.checked) set.add(id); else set.delete(id);
        } else {
            return;
        }

        updateSelection(kind);
    });

    Object.entries(bars).forEach(([kind, bar]) => {
        bar?.addEventListener('click', (event) => {
            const button = event.target.closest('[data-bulk-action]');
            if (!button) return;

            if (button.dataset.bulkAction === 'clear') {
                state.selected[kind].clear();
                updateSelection(kind);
                return;
            }

            const ids = Array.from(state.selected[kind]);
            kind === 'announcements' ? deleteAnnouncements(ids) : deleteCategories(ids);
        });
    });

    // =====================================================================
    // Initial render (data was embedded by the page; no request needed)
    // =====================================================================
    render('announcements');
    render('categories');
    setTab(location.hash === '#categories' ? 'categories' : 'announcements');
}