// =============================================================================
// VOTING FAQ
// =============================================================================
// Behaviour is a direct mirror of initContactFaq(): first item open by
// default, only one item open at a time, JS-driven max-height animation.

export function initVotingFaq() {
    const items = document.querySelectorAll(".an-voting-faq__item");
    if (!items.length) return;

    items.forEach((item, index) => {
        const button = item.querySelector(".an-voting-faq__question");
        const answer = item.querySelector(".an-voting-faq__answer");

        if (!button || !answer) return;

        // Open first item
        if (index === 0) {
            openVotingFaqItem(item, button, answer);
        }

        button.addEventListener("click", () => {
            const isOpen = item.classList.contains("is-open");

            // Close every item
            items.forEach(otherItem => {
                const otherButton = otherItem.querySelector(".an-voting-faq__question");
                const otherAnswer = otherItem.querySelector(".an-voting-faq__answer");

                if (!otherButton || !otherAnswer) return;

                closeVotingFaqItem(otherItem, otherButton, otherAnswer);
            });

            // Reopen clicked item if it wasn't already open
            if (!isOpen) {
                openVotingFaqItem(item, button, answer);
            }
        });
    });
}

function openVotingFaqItem(item, button, answer) {
    item.classList.add("is-open");
    button.setAttribute("aria-expanded", "true");
    answer.style.maxHeight = `${answer.scrollHeight}px`;
}

function closeVotingFaqItem(item, button, answer) {
    item.classList.remove("is-open");
    button.setAttribute("aria-expanded", "false");
    answer.style.maxHeight = null;
}