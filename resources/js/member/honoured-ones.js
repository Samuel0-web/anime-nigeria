// Honoured Ones archive: year and category filtering, entirely
// client-side against a small JSON payload, no page reload needed.
// Read-only by design, there is no selection, submission, or
// confirmation flow anywhere on this page.

function initHonouredOnes() {
    const root = document.querySelector('[data-honoured-archive]');
    if (!root) return;

    const dataScript = root.querySelector('[data-honoured-archive-data]');
    if (!dataScript) return;

    let editions;

    try {
        editions = JSON.parse(dataScript.textContent);
    } catch (err) {
        return;
    }

    if (!Array.isArray(editions) || !editions.length) return;

    const yearsEl = root.querySelector('[data-honoured-years]');
    const editionEl = root.querySelector('[data-honoured-edition]');
    const noteEl = root.querySelector('[data-honoured-note]');
    const filterEl = root.querySelector('[data-honoured-filter]');
    const countEl = root.querySelector('[data-honoured-count]');
    const wallEl = root.querySelector('[data-honoured-wall]');

    const latestYear = editions[0].year;

    const state = {
        year: latestYear,
        category: 'all',
    };

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = String(value ?? '');
        return div.innerHTML;
    }

    function initials(fullname) {
        return fullname
            .split(' ')
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part[0].toUpperCase())
            .join('');
    }

    function editionByYear(year) {
        return editions.find((e) => e.year === year);
    }

    function categoriesForYear(edition) {
        const seen = new Set();
        const list = [];

        edition.winners.forEach((winner) => {
            if (!seen.has(winner.category)) {
                seen.add(winner.category);
                list.push(winner.category);
            }
        });

        return list;
    }

    function renderYears() {
        yearsEl.innerHTML = editions.map((edition) => {
            const active = edition.year === state.year;
            const isLatest = edition.year === latestYear;

            return `
                <button type="button" role="tab" class="akd-honoured-years__item${active ? ' akd-honoured-years__item--active' : ''}"
                    aria-selected="${active ? 'true' : 'false'}"
                    data-honoured-year="${escapeHtml(edition.year)}"
                >
                    ${escapeHtml(edition.year)}
                    ${isLatest ? '<span class="akd-honoured-years__latest">Latest</span>' : ''}
                </button>
            `;
        }).join('');
    }

    function renderFilter(edition) {
        const categories = categoriesForYear(edition);

        filterEl.innerHTML = ['all', ...categories].map((cat) => {
            const active = state.category === cat;
            const label = cat === 'all' ? 'All categories' : cat;

            return `
                <button type="button" class="akd-honoured-filter__pill${active ? ' akd-honoured-filter__pill--active' : ''}"
                    aria-pressed="${active ? 'true' : 'false'}"
                    data-honoured-category="${escapeHtml(cat)}"
                >
                    ${escapeHtml(label)}
                </button>
            `;
        }).join('');
    }

    function winnerCardHtml(winner) {
        return `
            <article class="akd-honoured-card">
                <p class="akd-honoured-card__category">${escapeHtml(winner.category)}</p>

                <a href="/member/player/${escapeHtml(winner.username)}" class="akd-honoured-card__portrait-link">
                    <span class="akd-honoured-card__portrait akd-honoured-card__portrait--${escapeHtml(winner.accent)}">
                        <span class="akd-honoured-card__initials" aria-hidden="true">${escapeHtml(initials(winner.fullname))}</span>
                    </span>
                </a>

                <h3 class="akd-honoured-card__name">
                    <a href="/member/player/${escapeHtml(winner.username)}">${escapeHtml(winner.fullname)}</a>
                </h3>
                <p class="akd-honoured-card__username">@${escapeHtml(winner.username)}</p>
                ${winner.reason ? `<p class="akd-honoured-card__reason">${escapeHtml(winner.reason)}</p>` : ''}

                <p class="akd-honoured-card__edition">ANCA ${escapeHtml(winner.year)}</p>
            </article>
        `;
    }

    function render() {
        const edition = editionByYear(state.year);
        if (!edition) return;

        renderYears();
        renderFilter(edition);

        editionEl.textContent = edition.edition;
        noteEl.textContent = `The people recognised during the ${edition.year} edition.`;

        const winners = edition.winners
            .filter((w) => state.category === 'all' || w.category === state.category)
            .map((w) => ({ ...w, year: edition.year }));

        countEl.textContent = winners.length === 1 ? '1 recognition' : `${winners.length} recognitions`;

        wallEl.innerHTML = winners.length
            ? winners.map(winnerCardHtml).join('')
            : '<p class="akd-honoured-wall__empty">No recognitions in this category for this edition.</p>';
    }

    root.addEventListener('click', (event) => {
        const yearBtn = event.target.closest('[data-honoured-year]');
        if (yearBtn) {
            const match = editions.find((e) => String(e.year) === yearBtn.dataset.honouredYear);
            if (match) {
                state.year = match.year;
                state.category = 'all';
                render();
            }
            return;
        }

        const catBtn = event.target.closest('[data-honoured-category]');
        if (catBtn) {
            state.category = catBtn.dataset.honouredCategory;
            render();
        }
    });

    render();
}

document.addEventListener('DOMContentLoaded', initHonouredOnes);
export default initHonouredOnes;