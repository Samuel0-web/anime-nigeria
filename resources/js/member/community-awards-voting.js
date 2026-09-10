// ANCA Voting: one category at a time. Selecting a nominee updates the
// member's pick immediately, with no confirmation dialog per category.
// Once every category has a pick, the member moves through a short
// review step (with a brief preparing pause) where every selection can
// still be changed, and the single confirmation dialog appears only at
// final submission. ANCA voting follows the same one vote per entry per
// day rule as ANAA, submitting records today's votes, and the member
// can return and vote again once per day while voting remains open.
// Entirely client-side and in-memory by design, nothing here survives a
// real page refresh, matching the simulated nature of the feature.

import { useConfirmDialog } from '../modules/confirm-dialog.js';

const FLOW = {
    VOTING: 'voting',
    PREPARING_REVIEW: 'preparing_review',
    REVIEW: 'review',
    SUCCESS: 'success',
};

const REVIEW_DELAY_MS = 2000;
const ADVANCE_DELAY_MS = 500;
const STAGE_EXIT_MS = 150;

function initAncaVoting() {
    const root = document.querySelector('[data-anca-vote]');
    if (!root) return;
    const dataScript = root.querySelector('[data-anca-vote-data]');
    if (!dataScript) return;
    let data;

    try {
        data = JSON.parse(dataScript.textContent);
    } catch (err) {
        return;
    }

    const categories = data.categories || [];
    const completion = data.completion || {};
    if (!categories.length) return;

    const stageEl = root.querySelector('[data-anca-vote-stage]');
    const dotsEl = root.querySelector('[data-anca-vote-dots]');
    const preparingEl = root.querySelector('[data-anca-vote-preparing]');
    const reviewEl = root.querySelector('[data-anca-vote-review]');
    const reviewListEl = root.querySelector('[data-anca-vote-review-list]');
    const successEl = root.querySelector('[data-anca-vote-success]');
    const successDescEl = root.querySelector('[data-anca-vote-success-desc]');
    const successCtaEl = root.querySelector('[data-anca-vote-success-cta]');
    const titleEl = root.querySelector('[data-anca-vote-title]');
    const questionEl = root.querySelector('[data-anca-vote-question]');
    // The position indicator now lives in the topline, a sibling of
    // [data-anca-vote] rather than a descendant, so this needs a
    // document-level lookup instead of root.
    const positionEl = document.querySelector('[data-anca-vote-position]');
    // Both sit outside [data-anca-vote], topline above it, daily note
    // below it, so these need document-level lookups too.
    const toplineEl = document.querySelector('[data-anca-vote-topline]');
    const dailyNoteEl = document.querySelector('[data-anca-vote-daily-note]');
    const nomineesEl = root.querySelector('[data-anca-nominees]');
    const actionEl = root.querySelector('[data-anca-vote-action]');
    const confirmDialog = useConfirmDialog();

    // Handle for the pending 500ms auto-advance timeout, tracked outside
    // state since it is a timer handle, not serialisable app state.
    let advanceTimer = null;

    const state = {
        flow: FLOW.VOTING,
        activeIndex: 0,
        returningToReview: false,
        selections: {}, // slug to nomineeId, the member's current intended pick
        votedToday: {}, // slug to { nomineeId, date }, the vote actually cast today
    };

    function todayKey() {
        return new Date().toISOString().slice(0, 10);
    }

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

    function clearAdvanceTimer() {
        if (advanceTimer) {
            window.clearTimeout(advanceTimer);
            advanceTimer = null;
        }
    }

    function allSelected() {
        return categories.every((c) => Boolean(state.selections[c.slug]));
    }

    // Finds the next category without a selection, starting after
    // fromIndex and wrapping around. Returns null once every category
    // has a pick.
    function nextIncompleteIndex(fromIndex) {
        const total = categories.length;

        for (let step = 1; step <= total; step += 1) {
            const idx = (fromIndex + step) % total;
            if (!state.selections[categories[idx].slug]) return idx;
        }

        return null;
    }

    function votedTodayFor(slug) {
        const entry = state.votedToday[slug];
        return entry && entry.date === todayKey() && entry.nomineeId === state.selections[slug]
            ? entry : null;
    }

    // With no direction (initial load, jumping back into a category
    // from the review screen), falls back to the original simple fade.
    // With a direction, plays the directional entrance whose matching
    // exit already ran in transitionToStage() before this is called.
    function playEnterAnimation(direction) {
        if (!direction) {
            stageEl.classList.add('akd-anca-vote-stage--entering');
            void stageEl.offsetWidth; // force reflow so the transition restarts

            requestAnimationFrame(() => {
                stageEl.classList.remove('akd-anca-vote-stage--entering');
            });

            return;
        }

        const enterClass = direction === 'backward' ? 'akd-anca-vote-stage--enter-back'
            : 'akd-anca-vote-stage--enter-fwd';

        stageEl.classList.remove('akd-anca-vote-stage--enter-fwd', 'akd-anca-vote-stage--enter-back');
        void stageEl.offsetWidth; // force reflow so the animation restarts
        stageEl.classList.add(enterClass);
    }

    function nomineeCardHtml(nominee, selected) {
        return `
            <li class="akd-anca-nominee-item">
                <button
                    type="button"
                    class="akd-anca-nominee${selected ? ' akd-anca-nominee--selected' : ''}"
                    data-nominee-id="${escapeHtml(nominee.id)}"
                    aria-pressed="${selected ? 'true' : 'false'}"
                >
                    <span class="akd-anca-nominee__portrait akd-anca-nominee__portrait--${escapeHtml(nominee.accent)}">
                        <span class="akd-anca-nominee__initials" aria-hidden="true">${escapeHtml(initials(nominee.fullname))}</span>
                        ${selected ? '<span class="akd-anca-nominee__check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>' : ''}
                    </span>
                    <span class="akd-anca-nominee__name">${escapeHtml(nominee.fullname)}</span>
                    <span class="akd-anca-nominee__username">@${escapeHtml(nominee.username)}</span>
                    ${nominee.reason ? `<span class="akd-anca-nominee__reason">${escapeHtml(nominee.reason)}</span>` : ''}
                </button>
            </li>
        `;
    }

    function renderPosition() {
        const total = categories.length;
        const current = state.activeIndex + 1;
        const pad = (n) => String(n).padStart(2, '0');

        positionEl.innerHTML = `
            <span aria-hidden="true">${pad(current)} / ${pad(total)}</span>
            <span class="visually-hidden">Category ${current} of ${total}</span>
        `;
    }

    function renderNominees(category) {
        const selectedId = state.selections[category.slug];

        nomineesEl.innerHTML = category.nominees
            .map((nominee) => nomineeCardHtml(nominee, nominee.id === selectedId))
            .join('');
    }

    function renderAction(category) {
        const slug = category.slug;
        const selectedId = state.selections[slug];
        let html;

        if (!selectedId) {
            html = '<p class="akd-anca-vote-action__hint">Select a nominee to continue</p>';
        } else {
            const nominee = category.nominees.find((n) => n.id === selectedId);
            const voted = votedTodayFor(slug);

            html = voted
                ? `<p class="akd-anca-vote-action__voted"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Voted today for ${escapeHtml(nominee.fullname)}</p>`
                : `<p class="akd-anca-vote-action__selected"><i class="fa-solid fa-check" aria-hidden="true"></i> Selected: ${escapeHtml(nominee.fullname)}</p>`;
        }

        if (state.returningToReview) {
            html += '<button type="button" class="akd-award-btn akd-award-btn--ghost" data-anca-back-to-review-inline>Back to review</button>';
        }

        actionEl.innerHTML = html;
    }

    function dotState(slug) {
        if (votedTodayFor(slug)) return 'voted';
        if (state.selections[slug]) return 'selected';
        return 'none';
    }

    function renderDots() {
        dotsEl.innerHTML = categories
            .map((c, i) => {
                const dState = dotState(c.slug);
                const active = i === state.activeIndex;
                const classes = [
                    'akd-anca-vote-dot',
                    dState === 'voted' ? 'akd-anca-vote-dot--voted' : '',
                    dState === 'selected' ? 'akd-anca-vote-dot--selected' : '',
                    active ? 'akd-anca-vote-dot--active' : '',
                ].filter(Boolean).join(' ');

                const labelSuffix = dState === 'voted' ? ', voted today' : dState === 'selected' ? ', selected' : '';

                return `
                    <li>
                        <button type="button" class="${classes}" data-anca-dot-index="${i}"
                            aria-current="${active ? 'true' : 'false'}"
                            aria-label="${escapeHtml(c.name)}${labelSuffix}"
                        >
                            ${dState === 'voted' ? '<i class="fa-solid fa-check" aria-hidden="true"></i>' : String(i + 1).padStart(2, '0')}
                        </button>
                    </li>
                `;
            })
            .join('');
    }

    function renderStage(direction) {
        const category = categories[state.activeIndex];
        titleEl.textContent = category.name;
        questionEl.textContent = category.prompt || 'Who should receive this recognition?';
        renderPosition();
        renderNominees(category);
        renderAction(category);
        updateNavButtons();
        playEnterAnimation(direction);
    }

    function updateNavButtons() {
        const prevBtn = root.querySelector('[data-anca-vote-prev]');
        const nextBtn = root.querySelector('[data-anca-vote-next]');
        prevBtn.disabled = state.activeIndex === 0;
        nextBtn.disabled = state.activeIndex === categories.length - 1;
    }

    function goToIndex(index, direction) {
        clearAdvanceTimer();
        const clamped = Math.max(0, Math.min(categories.length - 1, index));
        if (clamped === state.activeIndex) return;

        const dir = direction || (clamped > state.activeIndex ? 'forward' : 'backward');
        state.activeIndex = clamped;
        transitionToStage(dir);
        renderDots();
    }

    // Plays the exit slide for the outgoing category, swaps in the new
    // category's content, then plays the matching entrance slide.
    // Skipped under prefers-reduced-motion, where the category simply
    // swaps using the existing non-directional fade instead.
    function transitionToStage(direction) {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            renderStage();
            return;
        }

        const exitClass = direction === 'backward' ? 'akd-anca-vote-stage--exit-back'
            : 'akd-anca-vote-stage--exit-fwd';

        stageEl.classList.add(exitClass);

        window.setTimeout(() => {
            stageEl.classList.remove(exitClass);
            renderStage(direction);
        }, STAGE_EXIT_MS);
    }

    // Shows exactly one of the four flow blocks, everything else stays
    // hidden via the `hidden` attribute. See the `[hidden]` override
    // added to _voting.scss, without it this toggle alone would not be
    // enough for the success screen, the exact bug being fixed here.
    function showFlow(flow) {
        state.flow = flow;
        stageEl.hidden = flow !== FLOW.VOTING;
        dotsEl.hidden = flow !== FLOW.VOTING;
        preparingEl.hidden = flow !== FLOW.PREPARING_REVIEW;
        reviewEl.hidden = flow !== FLOW.REVIEW;
        successEl.hidden = flow !== FLOW.SUCCESS;

        // The topline and the daily voting rule stay visible through
        // every other state, they only disappear once voting reaches
        // its terminal success state.
        if (toplineEl) toplineEl.hidden = flow === FLOW.SUCCESS;
        if (dailyNoteEl) dailyNoteEl.hidden = flow === FLOW.SUCCESS;
    }

    // Called 500ms after a selection is made. Returns to review if the
    // member was editing a selection from there, otherwise moves on to
    // the next category without a pick, or into the review flow once
    // every category has one. This is the only path into review now,
    // there is no button to press.
    function handleAutoAdvance() {
        if (state.returningToReview) {
            backToReview();
            return;
        }

        if (allSelected()) {
            startPreparingReview();
            return;
        }

        const nextIdx = nextIncompleteIndex(state.activeIndex);
        if (nextIdx !== null) {
            // Always forward: nextIncompleteIndex can wrap to an
            // earlier index if the member skipped categories, but the
            // auto-advance flow is conceptually always moving forward
            // through what's left.
            goToIndex(nextIdx, 'forward');
        }
    }

    function startPreparingReview() {
        clearAdvanceTimer();
        if (!allSelected()) return;
        showFlow(FLOW.PREPARING_REVIEW);

        window.setTimeout(() => {
            // Guard in case the flow somehow changed while waiting.
            if (state.flow !== FLOW.PREPARING_REVIEW) return;
            renderReview();
            showFlow(FLOW.REVIEW);
        }, REVIEW_DELAY_MS);
    }

    function renderReview() {
        reviewListEl.innerHTML = categories.map((category) => {
            const nominee = category.nominees.find((n) => n.id === state.selections[category.slug]);

            return `
                <li class="akd-anca-vote-review__item">
                    <span class="akd-anca-vote-review__portrait akd-anca-vote-review__portrait--${escapeHtml(nominee.accent)}">
                        <span class="akd-anca-vote-review__initials" aria-hidden="true">${escapeHtml(initials(nominee.fullname))}</span>
                        <span class="akd-anca-vote-review__check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                    </span>
                    <span class="akd-anca-vote-review__nominee">${escapeHtml(nominee.fullname)}</span>
                    <span class="akd-anca-vote-review__category">${escapeHtml(category.name)}</span>
                    <button type="button" class="akd-anca-vote-review__change" data-anca-review-change="${escapeHtml(category.slug)}">
                        Change
                    </button>
                </li>
            `;
        }).join('');
    }

    function editFromReview(slug) {
        clearAdvanceTimer();
        const index = categories.findIndex((c) => c.slug === slug);
        if (index === -1) return;
        state.activeIndex = index;
        state.returningToReview = true;
        renderStage();
        renderDots();
        showFlow(FLOW.VOTING);
    }

    function backToReview() {
        clearAdvanceTimer();
        state.returningToReview = false;
        renderReview();
        showFlow(FLOW.REVIEW);
    }

    async function submitVotes() {
        const confirmed = await confirmDialog.ask({
            title: 'Submit your votes?',
            message: `You are casting today's votes across all ${categories.length} categories. You can come back and vote again once per day while voting remains open.`,
            confirmLabel: 'Confirm votes',
            cancelLabel: 'Cancel',
        });

        if (!confirmed) return;

        const key = todayKey();

        categories.forEach((category) => {
            const nomineeId = state.selections[category.slug];
            if (nomineeId) {
                state.votedToday[category.slug] = { nomineeId, date: key };
            }
        });

        renderSuccess();
        showFlow(FLOW.SUCCESS);
    }

    function renderSuccess() {
        successDescEl.textContent = completion.description || '';
        successCtaEl.textContent = completion.ctaLabel || 'ANCA Overview';
        successCtaEl.href = completion.ctaLink || '/member/community/awards/overview';
    }

    root.addEventListener('click', (event) => {
        const nomineeBtn = event.target.closest('[data-nominee-id]');

        if (nomineeBtn) {
            const category = categories[state.activeIndex];
            state.selections[category.slug] = nomineeBtn.dataset.nomineeId;
            renderNominees(category);
            renderAction(category);
            renderDots();
            clearAdvanceTimer();

            advanceTimer = window.setTimeout(() => {
                advanceTimer = null;
                handleAutoAdvance();
            }, ADVANCE_DELAY_MS);

            return;
        }

        if (event.target.closest('[data-anca-vote-prev]')) {
            goToIndex(state.activeIndex - 1);
            return;
        }

        if (event.target.closest('[data-anca-vote-next]')) {
            goToIndex(state.activeIndex + 1);
            return;
        }

        const dotBtn = event.target.closest('[data-anca-dot-index]');
        if (dotBtn) {
            goToIndex(Number(dotBtn.dataset.ancaDotIndex));
            return;
        }

        if (event.target.closest('[data-anca-back-to-review-inline]')) {
            backToReview();
            return;
        }

        const changeBtn = event.target.closest('[data-anca-review-change]');

        if (changeBtn) {
            editFromReview(changeBtn.dataset.ancaReviewChange);
            return;
        }

        if (event.target.closest('[data-anca-submit-votes]')) {
            const btn = event.target.closest('[data-anca-submit-votes]');
            btn.disabled = true;
            submitVotes().finally(() => {
                if (btn.isConnected) btn.disabled = false;
            });
        }
    });

    renderStage();
    renderDots();
    showFlow(FLOW.VOTING);
}

document.addEventListener('DOMContentLoaded', initAncaVoting);
export default initAncaVoting;