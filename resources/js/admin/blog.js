// resources/js/admin/blog.js
// Blog management: tabs (Blog, Categories, Banned Words, Archive), article list with
// database search/filter/pagination, bulk actions, categories and banned-word CRUD.
// Toast messages never include user text (the toast renders HTML). Names and titles
// only go through textContent (confirm dialog) or escapeHtml (templates).

import { api, handleApiError } from '../modules/api';
import { success, error as notifyError, info } from '../modules/toast';
import { setLoading, clearLoading } from '../modules/loading-state';
import { useModal } from '../modules/modal';
import { useConfirmDialog } from '../modules/confirm-dialog';
import {
    collapseSpaces, escapeHtml, setFieldError, clearFieldError, hasFieldError, focusFirstError,
} from './form-utils';
import { openArticleEditor, BLOG_CHANGED_EVENT } from './blog-editor';
import { initSelect } from '../modules/select-menu';
import { optimistic } from '../modules/optimistic';
import { initFeaturedStrip, featuredQueue } from './blog-featured';

const API = '/admin/api/blog';
const TABS = ['blog', 'categories', 'words', 'archive'];
const HASHES = { blog: '', categories: '#categories', words: '#banned-words', archive: '#archive' };
const STATUS_LABELS = { draft: 'Draft', scheduled: 'Scheduled', published: 'Published', archived: 'Archived' };
const LABEL_PATTERN = /^[\p{L}\p{N}][\p{L}\p{N} &'\u2019().,\/-]*$/u;
const SEARCH_DEBOUNCE_MS = 300;

const dateFormatter = new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' });
const dateTimeFormatter = new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' });

const plural = (count, one, many) => `${count} ${count === 1 ? one : many}`;

function parseIso(value) {
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? null : date;
}

const formatDate = (iso) => { const d = parseIso(iso); return d ? dateFormatter.format(d) : ''; };
const formatDateTime = (iso) => { const d = parseIso(iso); return d ? dateTimeFormatter.format(d) : ''; };

export function initAdminBlog() {
    const root = document.getElementById('akdBlog');
    const dataEl = document.getElementById('akdBlogData');
    if (!root || !dataEl) return;

    const boot = JSON.parse(dataEl.textContent || '{}');
    const modal = useModal();
    const confirmDialog = useConfirmDialog();
    const canAdmin = Boolean(boot.canAdmin);

    const articleList = (items, hasMore, loaded) => ({
        items, has_more: hasMore, page: 1, loading: false, error: false, seq: 0, rseq: 0, stale: false, loaded,
    });

    const state = {
        tab: 'blog',
        busy: false,
        counts: boot.articles?.counts || { draft: 0, scheduled: 0, published: 0, archived: 0 },
        blog: {
            ...articleList(boot.articles?.items || [], Boolean(boot.articles?.has_more), true),
            error: Boolean(boot.loadError),
            filters: { status: '', category: '', q: '' },
        },
        archive: { ...articleList([], false, false), stale: true, filters: { q: '' } },
        categories: { items: boot.categories || [], loading: false, error: Boolean(boot.loadError), seq: 0, loaded: true },
        words: { items: boot.words || [], loading: false, error: Boolean(boot.loadError), seq: 0, loaded: true },
        selected: { blog: new Set(), archive: new Set(), categories: new Set(), words: new Set() },
        last: { blog: null, archive: null, categories: null, words: null },
    };

    // =====================================================================
    // Elements
    // =====================================================================
    const tabButtons = Array.from(root.querySelectorAll('[data-bl-tab]'));
    const panels = {};

    TABS.forEach((kind) => {
        const panel = root.querySelector(`[data-bl-panel="${kind}"]`);
        if (!panel) return;

        panels[kind] = {
            panel,
            summary: panel.querySelector('[data-summary]'),
            head: panel.querySelector('[data-list-head]'),
            selectAll: panel.querySelector('[data-select-all]'),
            list: panel.querySelector('[data-list]'),
            skeleton: panel.querySelector('[data-skeleton]'),
            empty: panel.querySelector('[data-empty]'),
            error: panel.querySelector('[data-error]'),
            more: panel.querySelector('[data-more]'),
            search: panel.querySelector('[data-search]'),
            searchClear: panel.querySelector('[data-search-clear]'),
        };
    });

    // The fixed bulk bars live outside #akdBlog (container-type would trap fixed children).
    const bars = {};
    document.querySelectorAll('[data-bulkbar]').forEach((bar) => { bars[bar.dataset.bulkbar] = bar; });

    const isArticleKind = (kind) => kind === 'blog' || kind === 'archive';

    function syncBulkbars() {
        let anyVisible = false;

        Object.entries(bars).forEach(([kind, bar]) => {
            const count = state.selected[kind].size;
            const visible = state.tab === kind && count > 0;
            bar.classList.toggle('is-visible', visible);
            bar.querySelector('[data-bulk-count]').textContent = `${count} selected`;
            if (visible) anyVisible = true;
        });

        root.classList.toggle('has-selection', anyVisible);
    }

    // =====================================================================
    // Row templates
    // =====================================================================
    const checkboxHtml = (label, checked) => `
        <label class="akd-ann-check">
            <input type="checkbox" data-select-row ${checked ? 'checked' : ''} aria-label="${escapeHtml(label)}">
            <span class="akd-ann-check__box" aria-hidden="true">
                <i class="fa-solid fa-check"></i><i class="fa-solid fa-minus"></i>
            </span>
        </label>`;

    const iconButton = (action, icon, label, extraClass = '', pressed = null) => `
        <button type="button" class="akd-ann-iconbtn${extraClass}" data-action="${action}"
            aria-label="${escapeHtml(label)}" title="${escapeHtml(label)}"
            ${pressed === null ? '' : `aria-pressed="${pressed}"`}>
            <i class="${icon}" aria-hidden="true"></i>
        </button>`;

    function dateCell(item) {
        switch (item.status) {
            case 'published': return { value: formatDate(item.published_at), label: 'Published', iso: item.published_at };
            case 'scheduled': return { value: formatDateTime(item.published_at), label: 'Goes live', iso: item.published_at };
            case 'archived': return { value: formatDate(item.updated_at), label: 'Archived', iso: item.updated_at };
            default: return { value: formatDate(item.updated_at), label: 'Last edited', iso: item.updated_at };
        }
    }

    function articleRow(kind, item) {
        const selected = state.selected[kind].has(item.id);
        const title = item.title_plain || '';
        const name = title || 'Untitled draft';
        const status = STATUS_LABELS[item.status] ? item.status : 'draft';
        const date = dateCell(item);

        const thumb = item.cover_image_url
            ? `<img src="${escapeHtml(item.cover_image_url)}" alt="" loading="lazy" decoding="async">`
            : '<i class="fa-regular fa-image" aria-hidden="true"></i>';

        const titleHtml = kind === 'archive'
            ? `<span class="akd-bl-row__title akd-bl-row__title--static${title ? '' : ' is-empty'}">${escapeHtml(name)}</span>`
            : `<button type="button" class="akd-bl-row__title${title ? '' : ' is-empty'}" data-action="edit">${escapeHtml(name)}</button>`;

        const featured = item.featured_position
            ? `<span class="akd-ann-featured"><i class="fa-solid fa-star" aria-hidden="true"></i> Featured ${Number(item.featured_position)}</span>`
            : '';

        const sub = [item.category || 'No category', item.author].filter(Boolean).map(escapeHtml)
            .join(' <span class="akd-bl-dot" aria-hidden="true">\u00b7</span> ');

        let actions = '';

        if (kind === 'archive') {
            if (canAdmin) {
                actions = iconButton('restore', 'fa-solid fa-rotate-left', `Restore ${name} as a draft`)
                    + iconButton('delete', 'fa-regular fa-trash-can', `Delete ${name} permanently`, ' akd-ann-iconbtn--danger');
            }
        } else {
            const isFeatured = Boolean(item.featured_position);
            actions = iconButton('edit', 'fa-solid fa-pen', `Edit ${name}`)
                + iconButton('feature', `${isFeatured ? 'fa-solid' : 'fa-regular'} fa-star`,
                    isFeatured ? `Remove ${name} from featured` : `Feature ${name}`,
                    isFeatured ? ' akd-bl-iconbtn--on' : '', String(isFeatured))
                + (item.url
                    ? `<a class="akd-ann-iconbtn" href="${escapeHtml(item.url)}" target="_blank" rel="noopener"
                        aria-label="View ${escapeHtml(name)} on the site" title="View on site"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>`
                    : '')
                + (canAdmin ? iconButton('archive', 'fa-solid fa-box-archive', `Archive ${name}`) : '');
        }

        return `
            <li class="akd-bl-row${selected ? ' is-selected' : ''}" data-id="${item.id}">
                ${canAdmin ? checkboxHtml(`Select ${name}`, selected) : ''}
                <div class="akd-bl-row__main">
                    <span class="akd-ann-thumb">${thumb}</span>
                    <div class="akd-bl-row__text">
                        <div class="akd-bl-row__titleline">${titleHtml}${featured}</div>
                        <p class="akd-bl-row__sub">${sub}</p>
                    </div>
                </div>
                <div class="akd-bl-row__meta">
                    <div class="akd-bl-cell"><span class="akd-bl-status akd-bl-status--${status}">${STATUS_LABELS[status]}</span></div>
                    <div class="akd-bl-cell akd-bl-cell--date">
                        <time datetime="${escapeHtml(date.iso || '')}">${escapeHtml(date.value)}</time>
                        <span class="akd-bl-cell__label">${date.label}</span>
                    </div>
                    <div class="akd-bl-cell">${item.word_count > 0
                        ? `${Number(item.reading_minutes)} min read`
                        : '<span class="akd-bl-muted">No content</span>'}</div>
                </div>
                <div class="akd-bl-row__actions">${actions}</div>
            </li>`;
    }

    function categoryRow(item) {
        const selected = state.selected.categories.has(item.id);
        const actions = iconButton('edit', 'fa-solid fa-pen', `Edit ${item.label}`)
            + (canAdmin ? iconButton('delete', 'fa-regular fa-trash-can', `Delete ${item.label}`, ' akd-ann-iconbtn--danger') : '');

        return `
            <li class="akd-bl-row${selected ? ' is-selected' : ''}" data-id="${item.id}">
                ${canAdmin ? checkboxHtml(`Select ${item.label}`, selected) : ''}
                <div class="akd-bl-row__main">
                    <span class="akd-bl-mark" aria-hidden="true"><i class="fa-solid fa-hashtag"></i></span>
                    <div class="akd-bl-row__text">
                        <div class="akd-bl-row__titleline">
                            <button type="button" class="akd-bl-row__title" data-action="edit">${escapeHtml(item.label)}</button>
                        </div>
                        <p class="akd-bl-row__sub akd-bl-mono">/member/blog/category/${escapeHtml(item.slug)}</p>
                    </div>
                </div>
                <div class="akd-bl-row__meta">
                    <div class="akd-bl-cell akd-bl-cell--desc">${item.description
                        ? escapeHtml(item.description) : '<span class="akd-bl-muted">No description</span>'}</div>
                    <div class="akd-bl-cell">${plural(Number(item.published_count), 'published', 'published')}
                        <span class="akd-bl-cell__label">${Number(item.total_count)} in total</span></div>
                </div>
                <div class="akd-bl-row__actions">${actions}</div>
            </li>`;
    }

    function wordRow(item) {
        const selected = state.selected.words.has(item.id);
        const actions = iconButton('edit', 'fa-solid fa-pen', `Edit ${item.word}`)
            + (canAdmin ? iconButton('delete', 'fa-regular fa-trash-can', `Delete ${item.word}`, ' akd-ann-iconbtn--danger') : '');

        return `
            <li class="akd-bl-row${selected ? ' is-selected' : ''}" data-id="${item.id}">
                ${canAdmin ? checkboxHtml(`Select ${item.word}`, selected) : ''}
                <div class="akd-bl-row__main">
                    <span class="akd-bl-mark" aria-hidden="true"><i class="fa-solid fa-ban"></i></span>
                    <div class="akd-bl-row__text">
                        <div class="akd-bl-row__titleline"><span class="akd-bl-word">${escapeHtml(item.word)}</span></div>
                    </div>
                </div>
                <div class="akd-bl-row__meta">
                    <div class="akd-bl-cell">${escapeHtml(formatDate(item.created_at))}</div>
                </div>
                <div class="akd-bl-row__actions">${actions}</div>
            </li>`;
    }

    const rowTemplates = {
        blog: (item) => articleRow('blog', item),
        archive: (item) => articleRow('archive', item),
        categories: categoryRow,
        words: wordRow,
    };

    // =====================================================================
    // Rendering
    // =====================================================================
    const isFiltered = () => Boolean(state.blog.filters.status || state.blog.filters.category || state.blog.filters.q);

    function summaryFor(kind) {
        const c = state.counts;
        const blogTotal = (c.draft || 0) + (c.scheduled || 0) + (c.published || 0);

        if (kind === 'blog') {
            if (!isFiltered()) return plural(blogTotal, 'article', 'articles');
            const n = state.blog.items.length;
            return `${n}${state.blog.has_more ? '+' : ''} ${n === 1 ? 'result' : 'results'}`;
        }

        if (kind === 'archive') {
            if (!state.archive.filters.q) return plural(c.archived || 0, 'archived article', 'archived articles');
            const n = state.archive.items.length;
            return `${n}${state.archive.has_more ? '+' : ''} ${n === 1 ? 'result' : 'results'}`;
        }

        if (kind === 'categories') return plural(state.categories.items.length, 'category', 'categories');
        return plural(state.words.items.length, 'entry', 'entries');
    }

    function emptyFor(kind) {
        if (kind === 'blog') {
            const { status, category, q } = state.blog.filters;

            if (q || category) {
                return { icon: 'fa-magnifying-glass', title: 'No matching articles',
                    text: 'Try a different search, or clear the filters.', label: 'Clear filters', action: 'clear-filters' };
            }

            const byStatus = {
                draft: ['No drafts', 'Every article you start is saved as a draft until you publish or schedule it.'],
                scheduled: ['Nothing scheduled', 'Scheduled articles go live by themselves at the time you choose.'],
                published: ['Nothing published yet', 'Published articles appear on the member Blog straight away.'],
            };

            if (byStatus[status]) {
                return { icon: 'fa-newspaper', title: byStatus[status][0], text: byStatus[status][1],
                    label: 'New article', action: 'create' };
            }

            return { icon: 'fa-newspaper', title: 'No articles yet',
                text: 'Write your first article. It stays a draft until you publish or schedule it.',
                label: 'New article', action: 'create' };
        }

        if (kind === 'archive') {
            return state.archive.filters.q
                ? { icon: 'fa-magnifying-glass', title: 'No matching articles', text: 'Try a different search.', label: '', action: '' }
                : { icon: 'fa-box-archive', title: 'The archive is empty',
                    text: 'Archived articles are kept here. Restoring one brings it back as a draft.', label: '', action: '' };
        }

        if (kind === 'categories') {
            return { icon: 'fa-tags', title: 'No categories yet',
                text: 'Every article needs a category before it can be published.', label: 'New category', action: 'create' };
        }

        return { icon: 'fa-ban', title: 'No banned words yet',
            text: 'Add a word or phrase above. Matches in comments are replaced with [redacted].', label: '', action: '' };
    }

    function fillEmpty(kind) {
        const spec = emptyFor(kind);
        const empty = panels[kind].empty;
        empty.querySelector('[data-empty-icon]').className = `fa-solid ${spec.icon}`;
        empty.querySelector('[data-empty-title]').textContent = spec.title;
        empty.querySelector('[data-empty-text]').textContent = spec.text;
        const button = empty.querySelector('[data-empty-action]');
        button.hidden = !spec.label;
        button.textContent = spec.label;
        button.dataset.action = spec.action;
    }

    function renderCounts() {
        const c = state.counts;
        const blogTotal = (c.draft || 0) + (c.scheduled || 0) + (c.published || 0);
        const setTab = (kind, value) => {
            const el = root.querySelector(`[data-tab-count="${kind}"]`);
            if (el) el.textContent = String(value);
        };

        setTab('blog', blogTotal);
        setTab('archive', c.archived || 0);
        setTab('categories', state.categories.items.length);
        setTab('words', state.words.items.length);

        root.querySelectorAll('[data-status-count]').forEach((el) => {
            const key = el.dataset.statusCount;
            el.textContent = String(key === 'all' ? blogTotal : (c[key] || 0));
        });

        root.querySelectorAll('[data-status-filter]').forEach((chip) => {
            const key = chip.dataset.statusFilter === 'all' ? '' : chip.dataset.statusFilter;
            chip.setAttribute('aria-pressed', String(key === state.blog.filters.status));
        });
    }

    const categorySelect = initSelect(root.querySelector('[data-filter-category]'), {
        options: [{ value: '', label: 'All categories' }],
        value: '',
        title: 'Category',
        onChange: (value) => {
            state.blog.filters.category = value;
            fetchArticles('blog');
        },
    });

    function renderCategorySelect() {
        if (state.blog.filters.category
            && !state.categories.items.some((c) => String(c.id) === state.blog.filters.category)) {
            state.blog.filters.category = '';
        }

        categorySelect.setOptions([{ value: '', label: 'All categories' }]
            .concat(state.categories.items.map((c) => ({ value: String(c.id), label: c.label }))));
        categorySelect.setValue(state.blog.filters.category);
    }

    function render(kind) {
        const els = panels[kind];
        if (!els) return;

        const data = state[kind];
        const hasItems = data.items.length > 0;

        els.list.innerHTML = data.items.map(rowTemplates[kind]).join('');
        els.list.classList.toggle('is-loading', data.loading && hasItems);
        els.skeleton.hidden = !(data.loading && !hasItems);
        els.head.hidden = !hasItems;
        els.error.hidden = !(data.error && !hasItems && !data.loading);

        const showEmpty = !hasItems && !data.error && !data.loading && data.loaded;
        els.empty.hidden = !showEmpty;
        if (showEmpty) fillEmpty(kind);

        if (els.more) els.more.hidden = !data.has_more;
        if (els.summary) els.summary.textContent = summaryFor(kind);

        renderCounts();
        updateSelection(kind);
    }

    function updateSelection(kind) {
        if (!canAdmin || !panels[kind]) return;

        const els = panels[kind];
        const set = state.selected[kind];
        syncBulkbars();

        els.list.querySelectorAll('[data-id]').forEach((row) => {
            const on = set.has(Number(row.dataset.id));
            row.classList.toggle('is-selected', on);
            const box = row.querySelector('[data-select-row]');
            if (box) box.checked = on;
        });

        if (els.selectAll) {
            const items = state[kind].items;
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
    function tabFromHash() {
        const found = Object.entries(HASHES).find(([, hash]) => hash && hash === location.hash)?.[0];
        return found && panels[found] ? found : 'blog';
    }

    function setTab(tab, { focus = false } = {}) {
        if (!panels[tab]) return;
        state.tab = tab;

        tabButtons.forEach((button) => {
            const active = button.dataset.blTab === tab;
            button.setAttribute('aria-selected', String(active));
            button.tabIndex = active ? 0 : -1;

            if (active) {
                if (focus) button.focus();
                button.scrollIntoView({ inline: 'nearest', block: 'nearest' });
            }
        });

        Object.entries(panels).forEach(([kind, els]) => { els.panel.hidden = kind !== tab; });
        history.replaceState(null, '', HASHES[tab] || location.pathname + location.search);

        if (isArticleKind(tab)) {
            const data = state[tab];
            if ((!data.loaded || data.stale) && !data.loading) fetchArticles(tab);
        }

        syncBulkbars();
    }

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => setTab(button.dataset.blTab));

        button.addEventListener('keydown', (event) => {
            const order = tabButtons.map((b) => b.dataset.blTab);
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
    // Data loading (database search, filters and pagination; nothing filtered client-side)
    // =====================================================================
    function articleUrl(kind, page) {
        const data = state[kind];
        const params = new URLSearchParams();

        if (kind === 'archive') params.set('status', 'archived');
        else if (data.filters.status) params.set('status', data.filters.status);

        if (kind === 'blog' && data.filters.category) params.set('category', data.filters.category);
        if (data.filters.q) params.set('q', data.filters.q);
        params.set('page', String(page));

        return `${API}?${params}`;
    }

    async function fetchArticles(kind, { append = false } = {}) {
        const data = state[kind];
        const token = ++data.seq;
        const page = append ? data.page + 1 : 1;
        data.loading = true;
        data.error = false;
        render(kind);

        try {
            const result = await api(articleUrl(kind, page));
            if (token !== data.seq) return;
            if (result.success === false) throw new Error('Request failed');

            const incoming = result.items || [];

            if (append) {
                const known = new Set(data.items.map((item) => item.id));
                data.items = data.items.concat(incoming.filter((item) => !known.has(item.id)));
            } else {
                data.items = incoming;
            }

            data.page = result.page ?? page;
            data.has_more = Boolean(result.has_more);
            data.loaded = true;
            data.stale = false;
            if (result.counts) state.counts = result.counts;
            pruneSelection(kind);
        } catch (err) {
            if (token !== data.seq) return;
            data.error = true;
            if (data.items.length) handleApiError(err, 'Could not refresh the list.');
        } finally {
            if (token === data.seq) {
                data.loading = false;
                render(kind);
            }
        }
    }

    async function fetchSimple(kind, path) {
        const data = state[kind];
        const token = ++data.seq;
        data.loading = true;
        data.error = false;
        render(kind);

        try {
            const result = await api(path);
            if (token !== data.seq) return;
            if (result.success === false) throw new Error('Request failed');
            data.items = result.items || [];
            data.loaded = true;
            pruneSelection(kind);
        } catch (err) {
            if (token !== data.seq) return;
            data.error = true;
            if (data.items.length) handleApiError(err, 'Could not refresh the list.');
        } finally {
            if (token === data.seq) {
                data.loading = false;
                if (kind === 'categories') renderCategorySelect();
                render(kind);
            }
        }
    }

    const fetchCategories = () => fetchSimple('categories', `${API}/categories`);
    const fetchWords = () => fetchSimple('words', `${API}/banned-words`);

    function markArticlesStale() {
        state.blog.stale = true;
        state.archive.stale = true;
    }

    /** Reload the visible article list from page 1 and flag the other one for its next visit. */
    function refreshArticles() {
        markArticlesStale();
        if (isArticleKind(state.tab)) return fetchArticles(state.tab);
        return Promise.resolve();
    }

    /** Quietly refetch the pages already on screen, so optimistic changes are checked against the server. */
    async function reconcileArticles(kind) {
        const data = state[kind];
        if (data.loading) { data.stale = true; return; }
        const seq = data.seq;
        const token = ++data.rseq;

        try {
            let items = [];
            let hasMore = false;
            let counts = null;
            let reached = 0;

            for (let page = 1; page <= Math.max(1, data.page); page += 1) {
                const result = await api(articleUrl(kind, page));
                if (seq !== data.seq || token !== data.rseq) return;
                if (result.success === false) throw new Error('Request failed');
                items = items.concat(result.items || []);
                hasMore = Boolean(result.has_more);
                counts = result.counts || counts;
                reached = page;
                if (!hasMore) break;
            }

            data.items = items;
            data.has_more = hasMore;
            data.page = Math.max(1, reached);
            data.stale = false;
            if (counts) state.counts = counts;
            pruneSelection(kind);
            render(kind);
        } catch (err) {
            data.stale = true; // keep what is on screen; the next visit refetches
        }
    }

    async function runExclusive(task) {
        if (state.busy) return;
        state.busy = true;
        root.classList.add('is-busy');
        root.setAttribute('aria-busy', 'true');
        Object.values(bars).forEach((bar) => bar.classList.add('is-busy'));

        try {
            await task();
        } finally {
            state.busy = false;
            root.classList.remove('is-busy');
            root.setAttribute('aria-busy', 'false');
            Object.values(bars).forEach((bar) => bar.classList.remove('is-busy'));
        }
    }

    const findArticle = (id) => [...state.blog.items, ...state.archive.items].find((item) => item.id === id);
    const displayName = (item) => item?.title_plain || 'Untitled draft';

    // =====================================================================
    // Article actions
    // Optimistic where the outcome is predictable and undoable (archive, restore, featured,
    // reorder). Permanent delete is irreversible, so it only shows a pending state until the
    // server confirms.
    // =====================================================================
    const maxFeatured = Number(boot.maxFeatured) || 3;
    const editorContext = (id) => ({ id, canAdmin, maxFeatured, categories: state.categories.items });
    const pendingDelete = new Set();

    const featuredStrip = initFeaturedStrip({
        root: root.querySelector('[data-featured]'),
        max: maxFeatured,
        onChange: (list) => syncFeaturedBadges(list),
        onRemove: (id) => setFeatured(id, false),
        onEdit: (id) => openArticleEditor(editorContext(id)),
    });

    function syncFeaturedBadges(list) {
        const positions = new Map(list.map((a) => [a.id, a.featured_position]));

        [state.blog, state.archive].forEach((data) => {
            data.items.forEach((item) => { item.featured_position = positions.get(item.id) ?? null; });
        });

        render('blog');
    }

    /** Removes rows from a list immediately. Returns what restoreRemoved() needs to undo it. */
    function removeArticles(kind, ids, nextStatus) {
        const data = state[kind];
        const snapshot = {
            kind, items: data.items, counts: { ...state.counts },
            selected: new Set(state.selected[kind]), featured: featuredStrip.list(),
        };
        const gone = new Set(ids);

        data.items.filter((item) => gone.has(item.id)).forEach((item) => {
            state.counts[item.status] = Math.max(0, (state.counts[item.status] || 0) - 1);
            if (nextStatus) state.counts[nextStatus] = (state.counts[nextStatus] || 0) + 1;
        });

        data.items = data.items.filter((item) => !gone.has(item.id));
        ids.forEach((id) => state.selected[kind].delete(id));
        state[kind === 'blog' ? 'archive' : 'blog'].stale = true;

        if (nextStatus === 'archived') {
            const remaining = snapshot.featured.filter((a) => !gone.has(a.id))
                .map((a, i) => ({ ...a, featured_position: i + 1 }));
            if (remaining.length !== snapshot.featured.length) featuredStrip.set(remaining);
        }

        render(kind);
        return snapshot;
    }

    function restoreRemoved(snapshot) {
        state[snapshot.kind].items = snapshot.items;
        state.counts = snapshot.counts;
        state.selected[snapshot.kind] = snapshot.selected;
        featuredStrip.set(snapshot.featured);
        render(snapshot.kind);
    }

    async function archiveArticles(ids) {
        if (!canAdmin || !ids.length) return;
        const single = ids.length === 1;
        
        const confirmed = await confirmDialog.ask({
            title: single ? 'Archive this article?' : `Archive ${ids.length} articles?`,
            message: single
                ? `"${displayName(findArticle(ids[0]))}" disappears from the member Blog straight away. You can restore it as a draft from the Archive tab.`
                : `These ${ids.length} articles disappear from the member Blog straight away. You can restore them as drafts from the Archive tab.`,
            confirmLabel: single ? 'Archive' : `Archive ${ids.length}`,
            cancelLabel: 'Cancel',
            destructive: true, // the fix: this was false, so the dialog never used the crimson variant
        });

        if (!confirmed) return;

        await optimistic({
            apply: () => removeArticles('blog', ids, 'archived'),
            request: () => api(`${API}/archive`, { method: 'POST', body: { ids } }),
            commit: (result) => {
                success(single ? 'Article archived.' : `${plural(Number(result.archived) || ids.length, 'article', 'articles')} archived.`);
                reconcileArticles('blog');
            },
            rollback: restoreRemoved,
            errorMessage: 'Could not archive the articles.',
        });
    }

    async function restoreArticles(ids) {
        if (!canAdmin || !ids.length) return;

        await optimistic({
            apply: () => removeArticles('archive', ids, 'draft'),
            request: () => api(`${API}/restore`, { method: 'POST', body: { ids } }),
            commit: (result) => {
                success(`${plural(Number(result.restored) || ids.length, 'article', 'articles')} restored as drafts.`);
                reconcileArticles('archive');
            },
            rollback: restoreRemoved,
            errorMessage: 'Could not restore the articles.',
        });
    }

    async function deleteForever(ids) {
        ids = ids.filter((id) => !pendingDelete.has(id));
        if (!canAdmin || !ids.length) return;

        const single = ids.length === 1;
        const confirmed = await confirmDialog.ask({
            title: single ? 'Delete this article permanently?' : `Delete ${ids.length} articles permanently?`,
            message: single
                ? `"${displayName(findArticle(ids[0]))}" will be erased with its comments and uploaded images. This can't be undone.`
                : `These ${ids.length} articles will be erased with their comments and uploaded images. This can't be undone.`,
            confirmLabel: single ? 'Delete forever' : `Delete ${ids.length} forever`,
            cancelLabel: 'Cancel',
            destructive: true,
        });

        if (!confirmed) return;

        const setPending = (on) => ids.forEach((id) => {
            on ? pendingDelete.add(id) : pendingDelete.delete(id);
            const row = panels.archive.list.querySelector(`[data-id="${id}"]`);
            row?.classList.toggle('is-pending', on);
            row?.setAttribute('aria-busy', String(on));
        });

        setPending(true);

        try {
            const result = await api(API, { method: 'DELETE', body: { ids } });

            if (result.success === false) {
                setPending(false);
                notifyError(result.message || 'Could not delete the articles.');
                return;
            }

            ids.forEach((id) => pendingDelete.delete(id));
            removeArticles('archive', ids, null);
            success(`${plural(Number(result.deleted) || ids.length, 'article', 'articles')} deleted.`);
            reconcileArticles('archive');
        } catch (err) {
            setPending(false);
            handleApiError(err, 'Could not delete the articles.');
        }
    }

    function setFeatured(id, on) {
        const current = featuredStrip.list();
        const already = current.some((a) => a.id === id);
        const item = findArticle(id) || current.find((a) => a.id === id);
        if (!item || on === already) return;

        if (on && current.length >= maxFeatured) {
            notifyError(`Only ${maxFeatured} articles can be featured. Remove one first.`);
            return;
        }

        optimistic({
            queue: featuredQueue,
            apply: () => {
                const next = on
                    ? [...current, { ...item, featured_position: current.length + 1 }]
                    : current.filter((a) => a.id !== id).map((a, i) => ({ ...a, featured_position: i + 1 }));
                featuredStrip.set(next);
                return current;
            },
            request: () => api(`${API}/${id}/featured`, { method: 'POST', body: { featured: on } }),
            commit: (result) => { if (Array.isArray(result.featured)) featuredStrip.set(result.featured); },
            rollback: (previous) => { featuredStrip.set(previous); featuredStrip.resync(); },
            errorMessage: 'Could not change the featured state.',
        });
    }

    const toggleFeatured = (id) => setFeatured(id, !featuredStrip.list().some((a) => a.id === id));

    // =====================================================================
    // Shared form modal
    // =====================================================================
    function formModal({ title, subtitle, form, fields, submitLabel, size = 'sm', initialFocus,
        read, validate, send, doneMessage, onDone }) {
        const initial = JSON.stringify(read());
        let saving = false;
        let committed = false;
        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'akd-admin-btn';
        cancelBtn.textContent = 'Cancel';
        cancelBtn.addEventListener('click', () => modal.close());
        const saveBtn = document.createElement('button');
        saveBtn.type = 'submit';
        saveBtn.setAttribute('form', form.id);
        saveBtn.className = 'akd-admin-btn akd-admin-btn--primary';
        saveBtn.textContent = submitLabel;
        const footer = document.createDocumentFragment();
        footer.append(cancelBtn, saveBtn);

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (saving) return;

            if (!validate()) {
                focusFirstError(form);
                return;
            }

            saving = true;
            cancelBtn.disabled = true;
            setLoading(saveBtn, 'Saving...');

            try {
                const result = await send(read());

                if (result.success === false) {
                    const unmatched = [];

                    Object.entries(result.errors || {}).forEach(([key, message]) => {
                        if (fields[key]) setFieldError(fields[key], message);
                        else unmatched.push(message);
                    });

                    if (!result.errors) unmatched.push(result.message || 'Could not save your changes.');
                    if (unmatched.length) notifyError(unmatched[0]);
                    focusFirstError(form);
                    return;
                }

                committed = true;
                success(doneMessage);
                await modal.close();
                await onDone(result);
            } catch (err) {
                handleApiError(err, 'Could not save your changes.');
            } finally {
                saving = false;
                cancelBtn.disabled = false;
                clearLoading(saveBtn);
            }
        });

        modal.open({
            title, subtitle, content: form, footer, size, className: 'akd-bl-modal', initialFocus,
            beforeClose: async () => {
                if (committed) return true;
                if (saving) return false;
                if (JSON.stringify(read()) === initial) return true;

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
    // Categories
    // =====================================================================
    function openCategoryForm(item = null) {
        if (state.busy) return;
        const isEdit = item !== null;

        const template = document.createElement('template');
        template.innerHTML = `
            <form class="akd-ann-form" id="blCategoryForm" novalidate>
                <div class="akd-admin-field" data-field="label">
                    <label class="akd-admin-field__label" for="blCatLabel">Name</label>
                    <div class="akd-admin-field__control">
                        <input type="text" id="blCatLabel" class="akd-admin-field__input" maxlength="40" autocomplete="off"
                            value="${escapeHtml(item?.label ?? '')}" placeholder="Community" aria-describedby="blCatLabel-error">
                    </div>
                    ${isEdit ? `<p class="akd-admin-field__hint">Address: <span class="akd-bl-mono">/member/blog/category/${escapeHtml(item.slug)}</span>. It never changes, so links keep working after a rename.</p>`
                        : '<p class="akd-admin-field__hint">The category address is generated from the name and stays fixed afterwards.</p>'}
                    <p class="akd-admin-field__error" id="blCatLabel-error" data-field-error aria-live="polite"></p>
                </div>
                <div class="akd-admin-field" data-field="description">
                    <div class="akd-ann-labelrow">
                        <label class="akd-admin-field__label" for="blCatDesc">Description <span class="akd-bl-optional">Optional</span></label>
                        <span class="akd-ann-counter" data-counter aria-hidden="true"></span>
                    </div>
                    <textarea id="blCatDesc" class="akd-admin-field__input akd-bl-textarea" maxlength="240" rows="3"
                        aria-describedby="blCatDesc-error">${escapeHtml(item?.description ?? '')}</textarea>
                    <p class="akd-admin-field__error" id="blCatDesc-error" data-field-error aria-live="polite"></p>
                </div>
            </form>`;

        const form = template.content.firstElementChild;
        const fields = {
            label: form.querySelector('[data-field="label"]'),
            description: form.querySelector('[data-field="description"]'),
        };
        const labelInput = fields.label.querySelector('input');
        const descInput = fields.description.querySelector('textarea');
        const counter = form.querySelector('[data-counter]');

        const updateCounter = () => {
            const length = Array.from(descInput.value).length;
            counter.textContent = `${length} / 240`;
            counter.classList.toggle('is-over', length > 240);
        };

        descInput.addEventListener('input', updateCounter);
        labelInput.addEventListener('input', () => hasFieldError(fields.label) && clearFieldError(fields.label));
        descInput.addEventListener('input', () => hasFieldError(fields.description) && clearFieldError(fields.description));
        updateCounter();

        formModal({
            title: isEdit ? 'Edit category' : 'New category',
            subtitle: 'Categories group articles on the member Blog.',
            form, fields, size: 'default', initialFocus: labelInput,
            submitLabel: isEdit ? 'Save changes' : 'Create category',
            read: () => ({ label: collapseSpaces(labelInput.value), description: collapseSpaces(descInput.value) }),
            validate: () => {
                let valid = true;
                clearFieldError(fields.label);
                clearFieldError(fields.description);
                const label = collapseSpaces(labelInput.value);

                if (label.length < 2 || label.length > 40) {
                    setFieldError(fields.label, 'Name must be between 2 and 40 characters.');
                    valid = false;
                } else if (!LABEL_PATTERN.test(label)) {
                    setFieldError(fields.label, 'Use letters, numbers, spaces and basic punctuation only.');
                    valid = false;
                }

                if (Array.from(descInput.value).length > 240) {
                    setFieldError(fields.description, 'Description must not exceed 240 characters.');
                    valid = false;
                }

                return valid;
            },
            send: (body) => api(isEdit ? `${API}/categories/${item.id}` : `${API}/categories`,
                { method: isEdit ? 'PUT' : 'POST', body }),
            doneMessage: isEdit ? 'Category updated.' : 'Category created.',
            onDone: async () => { markArticlesStale(); await fetchCategories(); },
        });
    }

    async function performCategoryDelete(ids, reassignTo) {
        return runExclusive(async () => {
            try {
                const result = await api(`${API}/categories`, { method: 'DELETE', body: { ids, reassign_to: reassignTo } });

                if (result.success === false) {
                    notifyError(result.message || 'Could not delete the categories.');
                    return;
                }

                ids.forEach((id) => state.selected.categories.delete(id));
                const moved = Number(result.moved) || 0;
                const deleted = plural(Number(result.deleted) || ids.length, 'category', 'categories');
                success(moved > 0 ? `${deleted} deleted. ${plural(moved, 'article', 'articles')} moved.` : `${deleted} deleted.`);
                await modal.close();
                markArticlesStale();
                await fetchCategories();
            } catch (err) {
                handleApiError(err, 'Could not delete the categories.');
            }
        });
    }

    async function deleteCategories(ids) {
        if (!canAdmin || !ids.length || state.busy) return;

        const items = ids.map((id) => state.categories.items.find((c) => c.id === id)).filter(Boolean);

        // Articles (drafts and archived included) would be orphaned: ask where they go first.
        if (items.some((c) => c.total_count > 0)) {
            openReassignDialog(ids);
            return;
        }

        const single = ids.length === 1;
        const confirmed = await confirmDialog.ask({
            title: single ? 'Delete this category?' : `Delete ${ids.length} categories?`,
            message: single
                ? `"${items[0]?.label ?? 'This category'}" has no articles and will be removed. This can't be undone.`
                : `These ${ids.length} categories have no articles and will be removed. This can't be undone.`,
            confirmLabel: single ? 'Delete' : `Delete ${ids.length}`,
            cancelLabel: 'Cancel',
            destructive: true,
        });

        if (confirmed) await performCategoryDelete(ids, null);
    }

    function openReassignDialog(ids) {
        const selected = ids.map((id) => state.categories.items.find((c) => c.id === id)).filter(Boolean);
        const inUse = selected.filter((c) => c.total_count > 0);
        const moving = inUse.reduce((sum, c) => sum + Number(c.total_count), 0);
        const targets = state.categories.items.filter((c) => !ids.includes(c.id));

        const usedList = inUse.map((c) => `
            <li><span>${escapeHtml(c.label)}</span>
                <span class="akd-bl-muted">${escapeHtml(plural(Number(c.total_count), 'article', 'articles'))}</span></li>`).join('');

        const choices = targets.map((c) => `
            <label class="akd-bl-choice__item">
                <input type="radio" name="target" value="${c.id}">
                <span class="akd-bl-choice__body">${escapeHtml(c.label)}</span>
            </label>`).join('');

        const template = document.createElement('template');
        template.innerHTML = `
            <div class="akd-ann-form">
                <p class="akd-bl-lead">
                    ${escapeHtml(plural(moving, 'article', 'articles'))} (including drafts and archived ones)
                    ${inUse.length === 1 ? 'is' : 'are'} filed under ${inUse.length === 1 ? 'this category' : 'these categories'}.
                    A category can't be deleted while articles use it, so choose where they should go.
                </p>
                <ul class="akd-bl-reassign">${usedList}</ul>
                ${targets.length ? `
                    <fieldset class="akd-admin-field akd-ann-fieldset">
                        <legend class="akd-admin-field__label">Move articles to</legend>
                        <div class="akd-bl-choice">${choices}</div>
                    </fieldset>` : `
                    <div class="akd-admin-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        <p>There is no other category to move them to. Create another category first.</p></div>`}
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
            const target = state.categories.items.find((c) => c.id === targetId);
            if (!target || state.busy) return;

            const confirmed = await confirmDialog.ask({
                title: ids.length === 1 ? 'Delete this category?' : `Delete ${ids.length} categories?`,
                message: `${plural(moving, 'article', 'articles')} will be moved to "${target.label}", then ${ids.length === 1 ? 'the category' : 'the categories'} will be deleted. This can't be undone.`,
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
            content, footer, className: 'akd-bl-modal',
        });
    }

    // =====================================================================
    // Banned words
    // =====================================================================
    function wordProblem(word) {
        if (!word) return 'Enter a word or phrase.';
        if (Array.from(word).length < 2 || Array.from(word).length > 60) return 'Use between 2 and 60 characters.';
        if (!/[\p{L}\p{N}]/u.test(word)) return 'Use letters or numbers.';
        return null;
    }

    function openWordForm(item) {
        if (state.busy || !item) return;
        const template = document.createElement('template');

        template.innerHTML = `
            <form class="akd-ann-form" id="blWordForm" novalidate>
                <div class="akd-admin-field" data-field="word">
                    <label class="akd-admin-field__label" for="blWord">Word or phrase</label>
                    <div class="akd-admin-field__control">
                        <input type="text" id="blWord" class="akd-admin-field__input" maxlength="60" autocomplete="off"
                            value="${escapeHtml(item.word)}" aria-describedby="blWord-error">
                    </div>
                    <p class="akd-admin-field__hint">Whole words only, any capitalisation. Phrases match across any spacing.</p>
                    <p class="akd-admin-field__error" id="blWord-error" data-field-error aria-live="polite"></p>
                </div>
            </form>`;

        const form = template.content.firstElementChild;
        const fields = { word: form.querySelector('[data-field="word"]') };
        const input = fields.word.querySelector('input');
        input.addEventListener('input', () => hasFieldError(fields.word) && clearFieldError(fields.word));

        formModal({
            title: 'Edit banned word', subtitle: 'The change applies to new comments only.',
            form, fields, initialFocus: input, submitLabel: 'Save changes',
            read: () => ({ word: collapseSpaces(input.value) }),
            validate: () => {
                clearFieldError(fields.word);
                const problem = wordProblem(collapseSpaces(input.value));
                if (problem) setFieldError(fields.word, problem);
                return !problem;
            },
            send: (body) => api(`${API}/banned-words/${item.id}`, { method: 'PUT', body }),
            doneMessage: 'Entry updated.',
            onDone: () => fetchWords(),
        });
    }

    async function deleteWords(ids) {
        if (!canAdmin || !ids.length) return;
        const single = ids.length === 1;
        const word = state.words.items.find((w) => w.id === ids[0])?.word ?? 'This entry';

        const confirmed = await confirmDialog.ask({
            title: single ? 'Remove this entry?' : `Remove ${ids.length} entries?`,
            message: single
                ? `"${word}" will no longer be redacted in new comments.`
                : `These ${ids.length} entries will no longer be redacted in new comments.`,
            confirmLabel: single ? 'Remove' : `Remove ${ids.length}`,
            cancelLabel: 'Cancel',
            destructive: true,
        });

        if (!confirmed) return;

        await optimistic({
            apply: () => {
                const snapshot = { items: state.words.items, selected: new Set(state.selected.words) };
                state.words.items = state.words.items.filter((w) => !ids.includes(w.id));
                ids.forEach((id) => state.selected.words.delete(id));
                render('words');
                return snapshot;
            },
            request: () => api(`${API}/banned-words`, { method: 'DELETE', body: { ids } }),
            commit: () => success('Removed from the list.'),
            rollback: (snapshot) => {
                state.words.items = snapshot.items;
                state.selected.words = snapshot.selected;
                render('words');
            },
            errorMessage: 'Could not remove the entries.',
        });
    }

    const addForm = root.querySelector('[data-word-add]');

    if (addForm) {
        const input = addForm.querySelector('input');
        const button = addForm.querySelector('button[type="submit"]');
        const errorEl = root.querySelector('[data-word-add-error]');

        input.addEventListener('input', () => { errorEl.textContent = ''; });

        addForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (state.busy) return;

            const word = collapseSpaces(input.value);
            const problem = wordProblem(word);
            errorEl.textContent = '';

            if (problem) {
                errorEl.textContent = problem;
                input.focus();
                return;
            }

            await runExclusive(async () => {
                setLoading(button, 'Adding...');

                try {
                    const result = await api(`${API}/banned-words`, { method: 'POST', body: { word } });

                    if (result.success === false) {
                        errorEl.textContent = result.errors?.word || result.message || 'Could not add that.';
                        return;
                    }

                    input.value = '';
                    success('Added to the list.');
                    await fetchWords();
                    input.focus();
                } catch (err) {
                    handleApiError(err, 'Could not add that.');
                } finally {
                    clearLoading(button);
                }
            });
        });
    }

    // =====================================================================
    // Search and filters
    // =====================================================================
    const searchTimers = {};

    function onSearch(kind, input) {
        const els = panels[kind];
        const value = collapseSpaces(input.value);
        if (els.searchClear) els.searchClear.hidden = value === '';
        clearTimeout(searchTimers[kind]);

        searchTimers[kind] = setTimeout(() => {
            if (state[kind].filters.q === value) return;
            state[kind].filters.q = value;
            fetchArticles(kind);
        }, SEARCH_DEBOUNCE_MS);
    }

    function clearFilters() {
        state.blog.filters = { status: '', category: '', q: '' };
        const els = panels.blog;

        if (els.search) els.search.value = '';
        if (els.searchClear) els.searchClear.hidden = true;
        renderCategorySelect();
        fetchArticles('blog');
    }

    root.addEventListener('input', (event) => {
        const input = event.target.closest('[data-search]');
        const kind = input?.closest('[data-bl-panel]')?.dataset.blPanel;
        if (input && isArticleKind(kind)) onSearch(kind, input);
    });

    root.addEventListener('keydown', (event) => {
        const input = event.target.closest?.('[data-search]');
        if (!input || event.key !== 'Enter') return;
        event.preventDefault();
        const kind = input.closest('[data-bl-panel]')?.dataset.blPanel;
        clearTimeout(searchTimers[kind]);
        const value = collapseSpaces(input.value);
        state[kind].filters.q = value;
        fetchArticles(kind);
    });

    root.addEventListener('change', (event) => {
        const target = event.target;

        if (target instanceof HTMLInputElement && target.hasAttribute('data-select-all')) {
            const kind = target.closest('[data-bl-panel]')?.dataset.blPanel;
            if (!kind) return;
            const set = state.selected[kind];
            state[kind].items.forEach((item) => (target.checked ? set.add(item.id) : set.delete(item.id)));
            state.last[kind] = null;
            updateSelection(kind);
        }
    });

    // =====================================================================
    // Clicks
    // =====================================================================
    root.addEventListener('click', (event) => {
        // Row checkboxes (handled on click so Shift can select a range).
        const checkbox = event.target.closest('input[data-select-row]');

        if (checkbox) {
            const kind = checkbox.closest('[data-bl-panel]')?.dataset.blPanel;
            if (!kind) return;

            const set = state.selected[kind];
            const id = Number(checkbox.closest('[data-id]')?.dataset.id);

            if (event.shiftKey && state.last[kind] !== null && state.last[kind] !== id) {
                const items = state[kind].items;
                const a = items.findIndex((item) => item.id === id);
                const b = items.findIndex((item) => item.id === state.last[kind]);

                if (a !== -1 && b !== -1) {
                    const [start, end] = [a, b].sort((x, y) => x - y);
                    items.slice(start, end + 1).forEach((item) => set.add(item.id));
                }
            } else if (checkbox.checked) {
                set.add(id);
            } else {
                set.delete(id);
            }

            state.last[kind] = id;
            updateSelection(kind);
            return;
        }

        const chip = event.target.closest('[data-status-filter]');

        if (chip) {
            const next = chip.dataset.statusFilter === 'all' ? '' : chip.dataset.statusFilter;
            if (state.blog.filters.status === next) return;
            state.blog.filters.status = next;
            fetchArticles('blog');
            return;
        }

        if (event.target.closest('[data-search-clear]')) {
            const kind = event.target.closest('[data-bl-panel]')?.dataset.blPanel;
            const els = panels[kind];
            els.search.value = '';
            els.searchClear.hidden = true;
            state[kind].filters.q = '';
            els.search.focus();
            fetchArticles(kind);
            return;
        }

        const trigger = event.target.closest('[data-action], [data-empty-action]');
        if (!trigger || !root.contains(trigger) || trigger.tagName === 'A') return;

        const action = trigger.dataset.action;
        const kind = trigger.closest('[data-bl-panel]')?.dataset.blPanel ?? state.tab;
        const rowId = Number(trigger.closest('[data-id]')?.dataset.id) || null;
        const item = rowId === null ? null : state[kind]?.items.find((entry) => entry.id === rowId);

        switch (action) {
            case 'create':
                if (kind === 'blog') openArticleEditor(editorContext(null));
                else if (kind === 'categories') openCategoryForm();
                break;

            case 'edit':
                if (!item) break;
                if (kind === 'blog') openArticleEditor(editorContext(item.id));
                else if (kind === 'categories') openCategoryForm(item);
                else if (kind === 'words') openWordForm(item);
                break;

            case 'feature':
                if (item) toggleFeatured(item.id);
                break;

            case 'archive':
                if (item) archiveArticles([item.id]);
                break;

            case 'restore':
                if (item) restoreArticles([item.id]);
                break;

            case 'delete':
                if (!item) break;
                if (kind === 'archive') deleteForever([item.id]);
                else if (kind === 'categories') deleteCategories([item.id]);
                else if (kind === 'words') deleteWords([item.id]);
                break;

            case 'load-more':
                setLoading(trigger, 'Loading...');
                fetchArticles(kind, { append: true }).finally(() => clearLoading(trigger));
                break;

            case 'retry':
                if (isArticleKind(kind)) fetchArticles(kind);
                else if (kind === 'categories') fetchCategories();
                else fetchWords();
                break;

            case 'clear-filters':
                clearFilters();
                break;

            default:
                break;
        }
    });

    Object.entries(bars).forEach(([kind, bar]) => {
        bar.addEventListener('click', (event) => {
            const button = event.target.closest('[data-bulk-action]');
            if (!button) return;

            const action = button.dataset.bulkAction;
            const ids = Array.from(state.selected[kind]);

            if (action === 'clear') {
                state.selected[kind].clear();
                state.last[kind] = null;
                updateSelection(kind);
            } else if (action === 'archive') {
                archiveArticles(ids);
            } else if (action === 'restore') {
                restoreArticles(ids);
            } else if (action === 'delete') {
                if (kind === 'archive') deleteForever(ids);
                else if (kind === 'categories') deleteCategories(ids);
                else deleteWords(ids);
            }
        });
    });

    // A thumbnail that fails to load falls back to the same neutral icon.
    root.addEventListener('error', (event) => {
        const img = event.target;

        if (img instanceof HTMLImageElement && img.closest('.akd-ann-thumb')) {
            const icon = document.createElement('i');
            icon.className = 'fa-regular fa-image';
            icon.setAttribute('aria-hidden', 'true');
            img.replaceWith(icon);
        }
    }, true);

    // The editor fires this after saving, publishing, scheduling or closing. A quiet refresh:
    // no skeleton, no flicker.
    document.addEventListener(BLOG_CHANGED_EVENT, () => {
        markArticlesStale();
        if (isArticleKind(state.tab)) reconcileArticles(state.tab);
        featuredStrip.resync();
    });

    // =====================================================================
    // Initial render (the page embedded the first data; no request needed)
    // =====================================================================
    featuredStrip.set(boot.featured || [], { silent: true });
    renderCategorySelect();
    TABS.forEach((kind) => { if (panels[kind]) render(kind); });
    setTab(tabFromHash());
}