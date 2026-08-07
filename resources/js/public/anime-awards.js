// =============================================================================
// ANIME AWARDS
// =============================================================================

export function initAnimeAwards() {
    initAwardsFaq();
}

// =============================================================================
// FAQ
// =============================================================================

function initAwardsFaq() {
    const items = document.querySelectorAll('.an-awards-faq__item');
    if (!items.length) return;

    items.forEach((item, index) => {
        const button = item.querySelector('.an-awards-faq__question');
        const answer = item.querySelector('.an-awards-faq__answer');
        if (!button || !answer) return;

        // Open the first item by default.
        if (index === 0) {
            openItem(item, button, answer);
        }

        button.addEventListener('click', () => {
            const isOpen = item.classList.contains('is-open');

            items.forEach(otherItem => {
                const otherButton = otherItem.querySelector('.an-awards-faq__question');
                const otherAnswer = otherItem.querySelector('.an-awards-faq__answer');
                if (!otherButton || !otherAnswer) return;
                closeItem(otherItem, otherButton, otherAnswer);
            });

            if (!isOpen) {
                openItem(item, button, answer);
            }
        });
    });
}

function openItem(item, button, answer) {
    item.classList.add('is-open');
    button.setAttribute('aria-expanded', 'true');
    answer.style.maxHeight = `${answer.scrollHeight}px`;
}

function closeItem(item, button, answer) {
    item.classList.remove('is-open');
    button.setAttribute('aria-expanded', 'false');
    answer.style.maxHeight = null;
}