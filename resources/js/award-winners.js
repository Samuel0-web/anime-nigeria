// =============================================================================
// WINNERS FAQ
// =============================================================================
// Behaviour mirrors initContactFaq() / initVotingFaq(): first item open by
// default, only one item open at a time, JS-driven max-height animation.

export function initWinnersFaq() {
    const items = document.querySelectorAll(".an-winners-faq__item");
    if (!items.length) return;

    items.forEach((item, index) => {
        const button = item.querySelector(".an-winners-faq__question");
        const answer = item.querySelector(".an-winners-faq__answer");

        if (!button || !answer) return;

        // Open first item
        if (index === 0) {
            openWinnersFaqItem(item, button, answer);
        }

        button.addEventListener("click", () => {
            const isOpen = item.classList.contains("is-open");

            // Close every item
            items.forEach(otherItem => {
                const otherButton = otherItem.querySelector(".an-winners-faq__question");
                const otherAnswer = otherItem.querySelector(".an-winners-faq__answer");

                if (!otherButton || !otherAnswer) return;

                closeWinnersFaqItem(otherItem, otherButton, otherAnswer);
            });

            // Reopen clicked item if it wasn't already open
            if (!isOpen) {
                openWinnersFaqItem(item, button, answer);
            }
        });
    });
}

function openWinnersFaqItem(item, button, answer) {
    item.classList.add("is-open");
    button.setAttribute("aria-expanded", "true");
    answer.style.maxHeight = `${answer.scrollHeight}px`;
}

function closeWinnersFaqItem(item, button, answer) {
    item.classList.remove("is-open");
    button.setAttribute("aria-expanded", "false");
    answer.style.maxHeight = null;
}