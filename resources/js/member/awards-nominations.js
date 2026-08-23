// ANAA Nomination workspace: category select -> search -> select entry ->
// review -> submit. Entirely client-side and in-memory by design; nothing
// here survives a real page refresh, matching the simulated feature.
// Re-selecting a category that already has a submitted nomination requires
// an explicit "Change nomination" tap rather than silently overwriting it.

function initNominationWorkspace() {
    const root = document.querySelector('[data-nomination-workspace]');
    if (!root) return;

    const dataScript = root.querySelector('[data-nomination-data]');
    if (!dataScript) return;

    let data;
    try {
        data = JSON.parse(dataScript.textContent);
    } catch (err) {
        return;
    }

    const categories = data.categories || [];
    const categoryEntries = data.categoryEntries || {};
    if (!categories.length) return;

    const categoryListEl = root.querySelector('[data-nomination-category-list]');
    const searchWrap = root.querySelector('.akd-nomination-panel__search');
    const searchInput = root.querySelector('[data-nomination-search]');
    const resultsEl = root.querySelector('[data-nomination-results]');
    const reviewEl = root.querySelector('[data-nomination-review]');
    const submittedWrap = root.querySelector('[data-nomination-submitted]');
    const submittedListEl = root.querySelector('[data-nomination-submitted-list]');

    const state = {
        activeCategory: categories[0].slug,
        search: '',
        selected: null,       // entry pending submission
        submitted: {},         // slug -> entry
        unlocked: new Set(),   // slugs allowed to pick again despite being submitted
    };

    function categoryBySlug(slug) {
        return categories.find((c) => c.slug === slug);
    }

    function isLocked(slug) {
        return Boolean(state.submitted[slug]) && !state.unlocked.has(slug);
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = String(value ?? '');
        return div.innerHTML;
    }

    function renderCategoryList() {
        categoryListEl.innerHTML = categories.map((category) => {
            const active = category.slug === state.activeCategory;
            const nominated = isLocked(category.slug);
            const classes = [
                'akd-nomination-categories__item',
                active ? 'akd-nomination-categories__item--active' : '',
                nominated ? 'akd-nomination-categories__item--done' : '',
            ].filter(Boolean).join(' ');

            return `
                <li>
                    <button type="button" class="${classes}" data-category="${category.slug}">
                        <span class="akd-nomination-categories__name">${escapeHtml(category.name)}</span>
                        ${nominated ? '<i class="fa-solid fa-check" aria-hidden="true"></i>' : ''}
                    </button>
                </li>
            `;
        }).join('');
    }

    function renderPanel() {
        const slug = state.activeCategory;
        const category = categoryBySlug(slug);
        if (!category) return;

        searchInput.value = state.search;

        if (isLocked(slug)) {
            searchWrap.hidden = true;
            resultsEl.hidden = true;
            reviewEl.hidden = false;

            const entry = state.submitted[slug];
            reviewEl.innerHTML = `
                <div class="akd-nomination-review__card akd-nomination-review__card--locked">
                    <p class="akd-nomination-review__note">You've already submitted a nomination for this category.</p>
                    <div class="akd-nomination-review__entry">
                        <span class="akd-nomination-review__entry-title">${escapeHtml(entry.title)}</span>
                        ${entry.subtitle ? `<span class="akd-nomination-review__entry-subtitle">${escapeHtml(entry.subtitle)}</span>` : ''}
                    </div>
                    <button type="button" class="akd-award-btn akd-award-btn--ghost" data-action="unlock">
                        Change nomination
                    </button>
                </div>
            `;
            return;
        }

        searchWrap.hidden = false;

        if (state.selected) {
            resultsEl.hidden = true;
            reviewEl.hidden = false;
            reviewEl.innerHTML = `
                <div class="akd-nomination-review__card">
                    <p class="akd-nomination-review__label">Your nomination</p>
                    <p class="akd-nomination-review__category">${escapeHtml(category.name)}</p>
                    <div class="akd-nomination-review__entry">
                        <span class="akd-nomination-review__entry-title">${escapeHtml(state.selected.title)}</span>
                        ${state.selected.subtitle ? `<span class="akd-nomination-review__entry-subtitle">${escapeHtml(state.selected.subtitle)}</span>` : ''}
                    </div>
                    <div class="akd-nomination-review__actions">
                        <button type="button" class="akd-award-btn akd-award-btn--ghost" data-action="change">Change</button>
                        <button type="button" class="akd-award-btn akd-award-btn--primary" data-action="submit">Submit nomination</button>
                    </div>
                </div>
            `;
            return;
        }

        reviewEl.hidden = true;
        resultsEl.hidden = false;

        const pool = categoryEntries[slug] || [];
        const query = state.search.trim().toLowerCase();
        const matches = query
            ? pool.filter((entry) => (
                entry.title.toLowerCase().includes(query)
                || (entry.subtitle || '').toLowerCase().includes(query)
            ))
            : pool;

        if (!matches.length) {
            resultsEl.innerHTML = `
                <li class="akd-nomination-results__empty">
                    <p>No eligible entries found.</p>
                    <p class="akd-nomination-results__empty-hint">Check the spelling or review the eligibility requirements above.</p>
                </li>
            `;
            return;
        }

        resultsEl.innerHTML = matches.slice(0, 8).map((entry) => `
            <li>
                <button type="button" class="akd-nomination-result" data-entry-id="${escapeHtml(entry.id)}">
                    <span class="akd-nomination-result__title">${escapeHtml(entry.title)}</span>
                    ${entry.subtitle ? `<span class="akd-nomination-result__subtitle">${escapeHtml(entry.subtitle)}</span>` : ''}
                </button>
            </li>
        `).join('');
    }

    function renderSubmitted() {
        const slugs = Object.keys(state.submitted);
        submittedWrap.hidden = slugs.length === 0;

        submittedListEl.innerHTML = slugs.map((slug) => {
            const category = categoryBySlug(slug);
            const entry = state.submitted[slug];
            if (!category || !entry) return '';

            return `
                <li class="akd-nomination-submitted__item">
                    <div>
                        <span class="akd-nomination-submitted__category">${escapeHtml(category.name)}</span>
                        <span class="akd-nomination-submitted__title">${escapeHtml(entry.title)}</span>
                    </div>
                    <span class="akd-nomination-submitted__status">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Submitted
                    </span>
                </li>
            `;
        }).join('');
    }

    function render() {
        renderCategoryList();
        renderPanel();
        renderSubmitted();
    }

    categoryListEl.addEventListener('click', (event) => {
        const button = event.target.closest('[data-category]');
        if (!button) return;

        state.activeCategory = button.dataset.category;
        state.search = '';
        state.selected = null;
        render();
    });

    searchInput.addEventListener('input', (event) => {
        state.search = event.target.value;
        renderPanel();
    });

    resultsEl.addEventListener('click', (event) => {
        const button = event.target.closest('[data-entry-id]');
        if (!button) return;

        const pool = categoryEntries[state.activeCategory] || [];
        const entry = pool.find((item) => item.id === button.dataset.entryId);
        if (!entry) return;

        state.selected = entry;
        renderPanel();
    });

    reviewEl.addEventListener('click', (event) => {
        const action = event.target.closest('[data-action]')?.dataset.action;
        if (!action) return;

        if (action === 'change') {
            state.selected = null;
            renderPanel();
            return;
        }

        if (action === 'unlock') {
            state.unlocked.add(state.activeCategory);
            state.selected = null;
            render();
            return;
        }

        if (action === 'submit' && state.selected) {
            const button = event.target.closest('[data-action="submit"]');
            button.disabled = true;
            button.textContent = 'Submitting...';

            window.setTimeout(() => {
                state.submitted[state.activeCategory] = state.selected;
                state.unlocked.delete(state.activeCategory);
                state.selected = null;
                state.search = '';
                render();
            }, 500);
        }
    });

    render();
}

document.addEventListener('DOMContentLoaded', initNominationWorkspace);
export default initNominationWorkspace;