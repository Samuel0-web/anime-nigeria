// ANCA Nomination wall: an editorial list of categories that each expand
// inline into a member-search nomination workspace. Entirely client-side
// and in-memory, matching the simulated nature of the feature, nothing
// here survives a real page refresh. Only one category row can be
// expanded at a time, opening another row collapses whichever was open.
// Reuses the app's existing confirmation dialog rather than a second one.

import { useConfirmDialog } from '../modules/confirm-dialog.js';

function initAncaNominationBoard() {
    const board = document.querySelector('[data-nom-board]');
    if (!board) return;

    const dataScript = board.querySelector('[data-nom-board-data]');
    if (!dataScript) return;

    let data;

    try {
        data = JSON.parse(dataScript.textContent);
    } catch (err) {
        return;
    }

    const categories = data.categories || [];
    const members = data.members || [];
    const reasonMaxLen = data.reasonMaxLen || 160;
    if (!categories.length) return;

    const summaryList = document.querySelector('[data-nom-summary-list]');
    const confirmDialog = useConfirmDialog();

    const state = {
        active: null,          // slug of the currently expanded row, or null
        step: 'search',        // search | selected | review
        search: '',
        selectedMember: null,
        reason: '',
        nominations: {},       // slug -> { member, reason }
    };

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = String(value ?? '');
        return div.innerHTML;
    }

    function categoryBySlug(slug) {
        return categories.find((c) => c.slug === slug);
    }

    function rowFor(slug) {
        return document.querySelector(`[data-nom-row][data-category="${CSS.escape(slug)}"]`);
    }

    function initials(fullname) {
        return fullname
            .split(' ')
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part[0].toUpperCase())
            .join('');
    }

    function memberChip(member) {
        return `
            <span class="akd-anca-member-chip__avatar akd-anca-member-chip__avatar--${escapeHtml(member.accent)}">
                ${escapeHtml(initials(member.fullname))}
            </span>
            <span class="akd-anca-member-chip__text">
                <span class="akd-anca-member-chip__name">${escapeHtml(member.fullname)}</span>
                <span class="akd-anca-member-chip__username">@${escapeHtml(member.username)}</span>
            </span>
        `;
    }

    function renderSummary() {
        summaryList.innerHTML = categories.map((category) => {
            const nom = state.nominations[category.slug];

            return `
                <li class="akd-anca-your-noms__item">
                    <span class="akd-anca-your-noms__category">${escapeHtml(category.name)}</span>
                    <span class="akd-anca-your-noms__name">${nom ? escapeHtml(nom.member.fullname) : 'No nomination'}</span>
                </li>
            `;
        }).join('');
    }

    function renderCollapsedAction(slug) {
        const row = rowFor(slug);
        if (!row) return;
        const area = row.querySelector('[data-nom-action-area]');
        const nom = state.nominations[slug];

        if (nom) {
            area.innerHTML = `
                <p class="akd-anca-nom-row__nominated">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    Nominated: <span>${escapeHtml(nom.member.fullname)}</span>
                </p>
                <button type="button" class="akd-anca-nom-row__action akd-anca-nom-row__action--change" data-nom-action>
                    Change nomination
                </button>
            `;
        } else {
            area.innerHTML = `
                <button type="button" class="akd-anca-nom-row__action" data-nom-action>
                    Nominate someone <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </button>
            `;
        }
    }

    function memberResults() {
        const query = state.search.trim().toLowerCase();
        const pool = query
            ? members.filter((m) => (
                m.fullname.toLowerCase().includes(query)
                || m.username.toLowerCase().includes(query)
            ))
            : members;

        return pool.slice(0, 8);
    }

    function resultsMarkup(results) {
        if (!results.length) {
            return '<li class="akd-anca-nom-results__empty">No members found. Check the spelling and try again.</li>';
        }

        return results.map((member) => `
            <li>
                <button type="button" class="akd-anca-nom-result" data-member-id="${escapeHtml(member.id)}">
                    ${memberChip(member)}
                </button>
            </li>
        `).join('');
    }

    function renderExpandedContent(slug) {
        const category = categoryBySlug(slug);
        const content = rowFor(slug)?.querySelector('[data-nom-content]');
        if (!category || !content) return;

        if (state.step === 'review') {
            const member = state.selectedMember;
            content.innerHTML = `
                <p class="akd-anca-nom-review__label">Your nomination</p>
                <div class="akd-anca-member-chip akd-anca-member-chip--lg">
                    ${memberChip(member)}
                </div>
                ${state.reason ? `<p class="akd-anca-nom-review__reason">"${escapeHtml(state.reason)}"</p>` : ''}
                <div class="akd-anca-nom-row__actions">
                    <button type="button" class="akd-award-btn akd-award-btn--ghost" data-nom-back>Change</button>
                    <button type="button" class="akd-award-btn akd-award-btn--primary" data-nom-submit>Submit nomination</button>
                </div>
            `;
            return;
        }

        if (state.step === 'selected') {
            content.innerHTML = `
                <p class="akd-anca-nom-row__question">${escapeHtml(category.prompt || 'Who would you nominate?')}</p>
                <div class="akd-anca-member-chip akd-anca-member-chip--lg">
                    ${memberChip(state.selectedMember)}
                    <button type="button" class="akd-anca-member-chip__change" data-nom-reselect>Change</button>
                </div>
                <label class="akd-anca-nom-reason__label" for="anca-nom-reason-${escapeHtml(slug)}">
                    Why are you nominating them? <span class="akd-anca-nom-reason__optional">Optional</span>
                </label>
                <textarea
                    class="akd-anca-nom-reason__input"
                    id="anca-nom-reason-${escapeHtml(slug)}"
                    maxlength="${reasonMaxLen}"
                    placeholder="Tell us why they deserve this recognition"
                    data-nom-reason
                >${escapeHtml(state.reason)}</textarea>
                <p class="akd-anca-nom-reason__count" data-nom-reason-count>${state.reason.length}/${reasonMaxLen}</p>
                <div class="akd-anca-nom-row__actions">
                    <button type="button" class="akd-award-btn akd-award-btn--ghost" data-nom-cancel>Cancel</button>
                    <button type="button" class="akd-award-btn akd-award-btn--primary" data-nom-review>Review nomination</button>
                </div>
            `;
            return;
        }

        // step === 'search'
        content.innerHTML = `
            <p class="akd-anca-nom-row__question">${escapeHtml(category.prompt || 'Who would you nominate?')}</p>
            <label class="visually-hidden" for="anca-nom-search-${escapeHtml(slug)}">Search community members</label>
            <div class="akd-anca-nom-search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input
                    type="text"
                    id="anca-nom-search-${escapeHtml(slug)}"
                    class="akd-anca-nom-search__input"
                    placeholder="Search community members"
                    autocomplete="off"
                    value="${escapeHtml(state.search)}"
                    data-nom-search
                >
            </div>
            <ul class="akd-anca-nom-results" data-nom-results aria-live="polite">
                ${resultsMarkup(memberResults())}
            </ul>
            <button type="button" class="akd-anca-nom-row__cancel-link" data-nom-cancel>Cancel</button>
        `;

        content.querySelector('[data-nom-search]')?.focus();
    }

    function collapseActive() {
        if (!state.active) return;

        const row = rowFor(state.active);

        if (row) {
            row.classList.remove('akd-anca-nom-row--expanded');
            row.querySelector('[data-nom-expanded]').hidden = true;
            row.querySelector('[data-nom-collapsed]').hidden = false;
        }

        state.active = null;
        state.step = 'search';
        state.search = '';
        state.selectedMember = null;
        state.reason = '';
    }

    function expandRow(slug) {
        if (state.active === slug) return;

        collapseActive();

        const row = rowFor(slug);
        if (!row) return;

        const existing = state.nominations[slug];
        state.active = slug;
        state.step = existing ? 'selected' : 'search';
        state.search = '';
        state.selectedMember = existing ? existing.member : null;
        state.reason = existing ? existing.reason : '';

        row.classList.add('akd-anca-nom-row--expanded');
        row.querySelector('[data-nom-collapsed]').hidden = true;
        row.querySelector('[data-nom-expanded]').hidden = false;

        renderExpandedContent(slug);
    }

    async function submitActive() {
        const slug = state.active;
        const category = categoryBySlug(slug);
        const member = state.selectedMember;
        if (!slug || !category || !member) return;

        const confirmed = await confirmDialog.ask({
            title: `Nominate ${member.fullname}?`,
            message: `${category.name}. You are putting ${member.fullname} forward for this category.`,
            confirmLabel: 'Confirm nomination',
            cancelLabel: 'Cancel',
        });

        if (!confirmed) return;

        state.nominations[slug] = { member, reason: state.reason.trim() };

        const content = rowFor(slug)?.querySelector('[data-nom-content]');

        if (content) {
            content.innerHTML = `
                <p class="akd-anca-nom-success" role="status">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    ${escapeHtml(member.fullname)} has been nominated for ${escapeHtml(category.name)}.
                </p>
            `;
        }

        renderCollapsedAction(slug);
        renderSummary();

        window.setTimeout(() => {
            if (state.active === slug) {
                collapseActive();
            }
        }, 1100);
    }

    board.addEventListener('click', (event) => {
        const actionBtn = event.target.closest('[data-nom-action]');
        if (actionBtn) {
            const row = actionBtn.closest('[data-nom-row]');
            expandRow(row.dataset.category);
            return;
        }

        const cancelBtn = event.target.closest('[data-nom-cancel]');
        if (cancelBtn) {
            collapseActive();
            return;
        }

        const resultBtn = event.target.closest('[data-member-id]');
        if (resultBtn) {
            const member = members.find((m) => m.id === resultBtn.dataset.memberId);
            if (member) {
                state.selectedMember = member;
                state.step = 'selected';
                renderExpandedContent(state.active);
            }
            return;
        }

        const reselectBtn = event.target.closest('[data-nom-reselect]');
        if (reselectBtn) {
            state.step = 'search';
            state.search = '';
            renderExpandedContent(state.active);
            return;
        }

        const reviewBtn = event.target.closest('[data-nom-review]');
        if (reviewBtn) {
            state.step = 'review';
            renderExpandedContent(state.active);
            return;
        }

        const backBtn = event.target.closest('[data-nom-back]');
        if (backBtn) {
            state.step = 'selected';
            renderExpandedContent(state.active);
            return;
        }

        const submitBtn = event.target.closest('[data-nom-submit]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitActive().finally(() => {
                if (submitBtn.isConnected) submitBtn.disabled = false;
            });
        }
    });

    board.addEventListener('input', (event) => {
        if (event.target.matches('[data-nom-search]')) {
            state.search = event.target.value;
            const resultsEl = rowFor(state.active)?.querySelector('[data-nom-results]');
            if (resultsEl) resultsEl.innerHTML = resultsMarkup(memberResults());
            return;
        }

        if (event.target.matches('[data-nom-reason]')) {
            state.reason = event.target.value;
            const counter = rowFor(state.active)?.querySelector('[data-nom-reason-count]');
            if (counter) counter.textContent = `${state.reason.length}/${reasonMaxLen}`;
        }
    });

    renderSummary();
}

document.addEventListener('DOMContentLoaded', initAncaNominationBoard);
export default initAncaNominationBoard;