let modalRefs = null;

function buildModal() {
    if (modalRefs) return modalRefs;

    const overlay = document.createElement('div');
    overlay.className = 'akd-modal-overlay';
    overlay.id = 'akdAchievementModalOverlay';

    overlay.innerHTML = `
        <div class="akd-modal akd-achievement-modal" role="dialog" aria-modal="true" aria-labelledby="akdAchievementModalTitle">
            <div class="akd-modal__header">
                <h2 class="akd-modal__title" id="akdAchievementModalTitle">Achievement</h2>
                <button type="button" class="akd-modal__close" data-achievement-modal-close aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="akd-modal__body akd-achievement-modal__body">
                <span class="akd-achievement-modal__art" data-achievement-modal-icon></span>
                <h3 class="akd-achievement-modal__name" data-achievement-modal-name></h3>
                <p class="akd-achievement-modal__desc" data-achievement-modal-desc></p>
                <span class="akd-achievement-modal__date" data-achievement-modal-date></span>
                <p class="akd-achievement-modal__story" data-achievement-modal-story></p>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    modalRefs = {
        overlay,
        modal: overlay.querySelector('.akd-achievement-modal'),
        closeBtn: overlay.querySelector('[data-achievement-modal-close]'),
        titleEl: overlay.querySelector('#akdAchievementModalTitle'),
        artEl: overlay.querySelector('[data-achievement-modal-icon]'),
        nameEl: overlay.querySelector('[data-achievement-modal-name]'),
        descEl: overlay.querySelector('[data-achievement-modal-desc]'),
        dateEl: overlay.querySelector('[data-achievement-modal-date]'),
        storyEl: overlay.querySelector('[data-achievement-modal-story]'),
    };

    return modalRefs;
}

/**
 * Creates the achievement modal once (appended to document.body) and wires
 * a single delegated click listener for ANY .akd-achievement-card on the
 * page — Profile preview, the full Achievements page, or anywhere else
 * this class shows up later. No IDs, no per-page re-binding.
 */
export function initAchievementModal() {
    const refs = buildModal();
    const { overlay, modal, closeBtn } = refs;

    const focusableSelector = 'button:not([disabled]), [href], [tabindex]:not([tabindex="-1"])';
    let lastFocusedEl = null;

    function getFocusable() {
        return Array.from(modal.querySelectorAll(focusableSelector));
    }

    function openAchievementModal(trigger) {
        lastFocusedEl = trigger;

        refs.titleEl.textContent = trigger.dataset.achievementName;
        refs.artEl.innerHTML = `<i class="${trigger.dataset.achievementIcon}"></i>`;
        refs.nameEl.textContent = trigger.dataset.achievementName;
        refs.descEl.textContent = trigger.dataset.achievementDesc;
        refs.dateEl.textContent = `Earned ${trigger.dataset.achievementDate}`;
        refs.storyEl.textContent = trigger.dataset.achievementStory;

        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', handleKeydown);
        (getFocusable()[0] || modal).focus();
    }

    function closeAchievementModal() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        document.removeEventListener('keydown', handleKeydown);
        lastFocusedEl?.focus();
    }

    function handleKeydown(e) {
        if (e.key === 'Escape') {
            e.preventDefault();
            closeAchievementModal();
            return;
        }

        if (e.key === 'Tab') {
            const focusables = getFocusable();
            if (!focusables.length) return;
            const first = focusables[0];
            const last = focusables[focusables.length - 1];

            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    }

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.akd-achievement-card');
        if (trigger) openAchievementModal(trigger);
    });

    closeBtn.addEventListener('click', closeAchievementModal);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeAchievementModal();
    });
}