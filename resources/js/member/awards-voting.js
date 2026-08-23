// ANAA Voting: one category at a time, auto-advance on selection,
// review, simulated submit, then a downloadable vote card via
// html2canvas. Nothing here persists across a refresh by design; the
// "already voted today" state only exists after a submission this
// session, and reloading the page returns voting to an open state.

import html2canvas from 'html2canvas';
const ANIM_MS = 220; // matches $transition-base in _tokens.scss
const EXIT_MS = 120;
const PROCESSING_MS = 2500;

function initVoting() {
    const app = document.querySelector('[data-vote-app]');
    if (!app) return;

    const dataScript = app.querySelector('[data-vote-data]');
    if (!dataScript) return;

    let payload;

    try {
        payload = JSON.parse(dataScript.textContent);
    } catch (err) {
        return;
    }

    const categories = payload.categories || [];
    if (!categories.length) return;
    const stepEl = app.querySelector('[data-vote-step]');
    const titleEl = app.querySelector('[data-vote-title]');
    const infoBtn = app.querySelector('[data-vote-info]');
    const tooltipAnchor = app.querySelector('[data-vote-tooltip-anchor]');
    const tooltipEl = app.querySelector('[data-vote-tooltip]');
    const prevBtn = app.querySelector('[data-vote-prev]');
    const nextBtn = app.querySelector('[data-vote-next]');
    const progressFill = app.querySelector('[data-vote-progress-fill]');
    const progressLabel = app.querySelector('[data-vote-progress-label]');
    const hintEl = app.querySelector('[data-vote-hint]');
    const nomineesEl = app.querySelector('[data-vote-nominees]');
    const reviewEl = app.querySelector('[data-vote-review]');
    const reviewGridEl = app.querySelector('[data-vote-review-grid]');
    const successEl = app.querySelector('[data-vote-success]');
    const submitBtn = app.querySelector('[data-vote-submit]');
    const downloadBtn = app.querySelector('[data-vote-download]');
    const cardEl = document.querySelector('[data-vote-card]');
    const cardGridEl = cardEl ? cardEl.querySelector('[data-vote-card-grid]') : null;
    const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    const processingEl = document.createElement('div');
    processingEl.className = 'akd-vote-processing';
    processingEl.hidden = true;
    processingEl.setAttribute('aria-live', 'polite');
    processingEl.setAttribute('aria-busy', 'true');

    processingEl.innerHTML = `
        <div class="akd-vote-processing__spinner" aria-hidden="true">
            <span class="akd-vote-processing__spinner-track"></span>
            <span class="akd-vote-processing__spinner-progress"></span>
        </div>

        <p class="akd-vote-processing__label"></p>
    `;

    reviewEl.parentNode.insertBefore(processingEl, reviewEl);

    const state = {
        view: 'vote', // vote | processing | review | success
        index: 0,
        votes: {}, // slug -> nomineeId
        animating: false,
        processingLabel: '',
        processingComplete: null,
    };

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = String(value ?? '');
        return div.innerHTML;
    }

    function isComplete() {
        return categories.every((category) => Boolean(state.votes[category.slug]));
    }

    function firstIncompleteIndex() {
        return categories.findIndex((category) => !state.votes[category.slug]);
    }

    function closeTooltip() {
        tooltipEl.hidden = true;
        tooltipEl.classList.remove('akd-vote-tooltip--visible', 'akd-vote-tooltip--left');
        infoBtn.setAttribute('aria-expanded', 'false');
    }

    function openTooltip() {
        const category = categories[state.index];
        tooltipEl.textContent = category.description || '';
        tooltipEl.hidden = false;
        infoBtn.setAttribute('aria-expanded', 'true');

        // Flip to the left if the tooltip would overflow the right edge.
        tooltipEl.classList.remove('akd-vote-tooltip--left');
        const rect = tooltipEl.getBoundingClientRect();
        if (rect.right > window.innerWidth - 12) {
            tooltipEl.classList.add('akd-vote-tooltip--left');
        }

        // Double rAF so the initial (hidden) state paints before the
        // transition-triggering class is added.
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                tooltipEl.classList.add('akd-vote-tooltip--visible');
            });
        });
    }

    function renderNomineeArt(nominee) {
        if (nominee.image) {
            return `<span class="akd-vote-nominee__art"><img src="${escapeHtml(nominee.image)}" alt=""></span>`;
        }
        return `<span class="akd-vote-nominee__art akd-vote-nominee__art--fallback"><i class="fa-solid fa-clapperboard" aria-hidden="true"></i></span>`;
    }

    function renderNomineesMarkup(category, selectedId) {
        return category.nominees.map((nominee) => {
            const selected = nominee.id === selectedId;
            return `
                <li>
                    <button type="button"
                        class="akd-vote-nominee${selected ? ' akd-vote-nominee--selected' : ''}"
                        data-nominee-id="${escapeHtml(nominee.id)}"
                        aria-pressed="${selected ? 'true' : 'false'}"
                    >
                        ${renderNomineeArt(nominee)}
                        <span class="akd-vote-nominee__name">${escapeHtml(nominee.name)}</span>
                        ${selected ? '<i class="fa-solid fa-circle-check akd-vote-nominee__check" aria-hidden="true"></i>' : ''}
                    </button>
                </li>
            `;
        }).join('');
    }

    // Updates the static header chrome (title, progress) instantly. The
    // nominee list itself is what animates; see transitionStep() below.
    function renderHeader() {
        const category = categories[state.index];
        titleEl.textContent = category.name;
        prevBtn.disabled = state.index === 0;

        const progressPct = Math.round(((state.index + 1) / categories.length) * 100);
        progressFill.style.width = `${progressPct}%`;
        progressLabel.textContent = `${state.index + 1} of ${categories.length}`;

        closeTooltip();
        hintEl.hidden = true;
    }

    function renderNomineesInstant() {
        const category = categories[state.index];
        const selectedId = state.votes[category.slug] || null;
        nomineesEl.innerHTML = renderNomineesMarkup(category, selectedId);
    }

    // Animates the nominee list from the current category to
    // categories[nextIndex]. 'forward' exits left / enters from right.
    // 'back' exits right / enters from left.
    function transitionStep(nextIndex, direction) {
        if (state.animating) return;
        state.animating = true;

        const titleExitClass = direction === 'forward' ? 'akd-vote-step__title--exit-fwd'
            : 'akd-vote-step__title--exit-back';

        const titleEnterClass = direction === 'forward' ? 'akd-vote-step__title--enter-fwd'
            : 'akd-vote-step__title--enter-back';

        const exitClass = direction === 'forward' ? 'akd-vote-nominees--exit-fwd'
            : 'akd-vote-nominees--exit-back';

        const enterClass = direction === 'forward' ? 'akd-vote-nominees--enter-fwd'
            : 'akd-vote-nominees--enter-back';

        // Start moving the current category out.
        titleEl.classList.add(titleExitClass);
        nomineesEl.classList.add(exitClass);

        // Replace the content before the exit animation has completely
        // finished, eliminating the visible pause between categories.
        window.setTimeout(() => {
            state.index = nextIndex;
            titleEl.classList.remove(titleExitClass);
            renderHeader();
            renderNomineesInstant();
            void titleEl.offsetWidth;
            titleEl.classList.add(titleEnterClass);
            nomineesEl.classList.remove(exitClass);

            // Force a layout pass so the browser registers the new
            // starting position before the enter animation begins.
            void nomineesEl.offsetWidth;
            nomineesEl.classList.add(enterClass);

            window.setTimeout(() => {
                nomineesEl.classList.remove(enterClass);
                titleEl.classList.remove(titleEnterClass);
                state.animating = false;
            }, ANIM_MS);
        }, EXIT_MS);
    }

    function renderReview() {
        reviewGridEl.innerHTML = categories.map((category, index) => {
            const nominee = category.nominees.find((n) => n.id === state.votes[category.slug]);
            if (!nominee) return '';

            return `
                <li class="akd-vote-review__item">
                    <button type="button" class="akd-vote-review__card" data-review-index="${index}">
                        ${renderNomineeArt(nominee)}
                        <span class="akd-vote-review__body">
                            <span class="akd-vote-review__category">${escapeHtml(category.name)}</span>
                            <span class="akd-vote-review__nominee">${escapeHtml(nominee.name)}</span>
                        </span>
                        <span class="akd-vote-review__change">Change</span>
                    </button>
                </li>
            `;
        }).join('');
    }

    function render() {
        stepEl.hidden = state.view !== 'vote';
        processingEl.hidden = state.view !== 'processing';
        reviewEl.hidden = state.view !== 'review';
        successEl.hidden = state.view !== 'success';

        if (state.view === 'vote') {
            renderHeader();
            renderNomineesInstant();
        } else if (state.view === 'processing') {
            startProcessingAnimation(state.processingLabel, state.processingComplete);
        } else if (state.view === 'review') {
            renderReview();
            reviewEl.classList.remove('akd-vote-review--enter');

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    reviewEl.classList.add('akd-vote-review--enter');
                });
            });
        }
    }

    function startProcessingAnimation(labelText, onComplete) {
        const progress = processingEl.querySelector(
            '.akd-vote-processing__spinner-progress'
        );

        const label = processingEl.querySelector('.akd-vote-processing__label');
        label.textContent = labelText;
        processingEl.classList.remove('akd-vote-processing--visible');

        requestAnimationFrame(() => {
            processingEl.classList.add('akd-vote-processing--visible');
        });

        const start = performance.now();

        function updateProgress(now) {
            const elapsed = now - start;
            const ratio = Math.min(elapsed / PROCESSING_MS, 1);
            progress.style.setProperty('--processing-progress', `${ratio * 360}deg`);

            if (ratio < 1) {
                requestAnimationFrame(updateProgress);
                return;
            }

            window.setTimeout(() => {
                onComplete();
            }, 180);
        }

        requestAnimationFrame(updateProgress);
    }

    function goToCategory(index) {
        state.index = index;
        state.view = 'vote';
        render();
    }

    function advanceAfterSelection() {
        if (state.index < categories.length - 1) {
            transitionStep(state.index + 1, 'forward');
            return;
        }

        if (isComplete()) {
            state.processingLabel = 'Preparing your review...';
            state.processingComplete = () => {
                state.view = 'review';
                render();
            };

            state.view = 'processing';
            render();
            return;
        }

        goToCategory(firstIncompleteIndex());
    }

    function showHint() {
        hintEl.hidden = false;
        hintEl.classList.remove('akd-vote-step__hint--shake');
        void hintEl.offsetWidth;
        hintEl.classList.add('akd-vote-step__hint--shake');
    }

    nomineesEl.addEventListener('click', (event) => {
        if (state.animating) return;

        const button = event.target.closest('[data-nominee-id]');
        if (!button) return;

        const category = categories[state.index];
        state.votes[category.slug] = button.dataset.nomineeId;

        // Brief selection pop before advancing, so the choice registers
        // visually instead of jumping straight to the next category.
        button.classList.add('akd-vote-nominee--pop');
        window.setTimeout(() => advanceAfterSelection(), 160);
    });

    prevBtn.addEventListener('click', () => {
        if (state.index === 0 || state.animating) return;
        transitionStep(state.index - 1, 'back');
    });

    nextBtn.addEventListener('click', () => {
        if (state.animating) return;

        if (state.index < categories.length - 1) {
            transitionStep(state.index + 1, 'forward');
            return;
        }

        if (isComplete()) {
            state.view = 'review';
            render();
        } else {
            showHint();
        }
    });

    if (canHover) {
        tooltipAnchor.addEventListener('mouseenter', openTooltip);
        tooltipAnchor.addEventListener('mouseleave', closeTooltip);
        infoBtn.addEventListener('focus', openTooltip);
        infoBtn.addEventListener('blur', closeTooltip);
        // Prevent a stray click (trackpad taps register as click too)
        // from fighting the hover state.
        infoBtn.addEventListener('click', (event) => event.preventDefault());
    } else {
        infoBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            if (!tooltipEl.hidden) {
                closeTooltip();
            } else {
                openTooltip();
            }
        });

        document.addEventListener('click', (event) => {
            if (!tooltipEl.hidden && !tooltipAnchor.contains(event.target)) {
                closeTooltip();
            }
        });

        document.addEventListener('scroll', closeTooltip, { passive: true });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeTooltip();
    });

    reviewGridEl.addEventListener('click', (event) => {
        const button = event.target.closest('[data-review-index]');
        if (!button) return;
        goToCategory(Number(button.dataset.reviewIndex));
    });

    submitBtn.addEventListener('click', () => {
        submitBtn.disabled = true;
        state.processingLabel = 'Casting your votes...';
        state.processingComplete = () => {
            state.view = 'success';
            submitBtn.disabled = false;
            submitBtn.textContent = "Cast Today's Votes";
            render();
        };

        state.view = 'processing';
        render();
    });

    function buildVoteCard() {
        if (!cardGridEl) return;
        const columns = categories.length > 9 ? 4 : 3;
        cardGridEl.style.setProperty('--vote-card-cols', String(columns));

        cardGridEl.innerHTML = categories.map((category) => {
            const nominee = category.nominees.find((n) => n.id === state.votes[category.slug]);
            if (!nominee) return '';

            const art = nominee.image
                ? `<img src="${escapeHtml(nominee.image)}" alt="" class="akd-vote-card__item-img">`
                : `<div class="akd-vote-card__item-img akd-vote-card__item-img--fallback"></div>`;

            return `
                <div class="akd-vote-card__item">
                    ${art}
                    <p class="akd-vote-card__item-category">${escapeHtml(category.name)}</p>
                    <p class="akd-vote-card__item-nominee">${escapeHtml(nominee.name)}</p>
                </div>
            `;
        }).join('');
    }

    downloadBtn.addEventListener('click', () => {
        if (!cardEl) return;
        buildVoteCard();
        downloadBtn.disabled = true;
        const originalLabel = downloadBtn.innerHTML;
        downloadBtn.innerHTML = 'Preparing...';

        html2canvas(cardEl, { backgroundColor: null, scale: 2 }).then((canvas) => {
            canvas.toBlob((blob) => {
                if (!blob) return;
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = 'anaa-2026-vote-card.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            }, 'image/png');
        }).finally(() => {
            downloadBtn.disabled = false;
            downloadBtn.innerHTML = originalLabel;
        });
    });

    render();
}

document.addEventListener('DOMContentLoaded', initVoting);
export default initVoting;