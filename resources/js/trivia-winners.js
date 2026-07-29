// =============================================================================
// TRIVIA WINNERS FAQ
// =============================================================================
// Behaviour mirrors initContactFaq() / initVotingFaq() / initWinnersFaq() /
// initTriviaFaq() / initLeaderboardFaq(): first item open by default, only
// one item open at a time, JS-driven max-height animation.

export function initTriviaWinnersFaq() {
    const items = document.querySelectorAll(".an-trivia-winners-faq__item");
    if (!items.length) return;

    items.forEach((item, index) => {
        const button = item.querySelector(".an-trivia-winners-faq__question");
        const answer = item.querySelector(".an-trivia-winners-faq__answer");

        if (!button || !answer) return;

        // Open first item
        if (index === 0) {
            openTriviaWinnersFaqItem(item, button, answer);
        }

        button.addEventListener("click", () => {
            const isOpen = item.classList.contains("is-open");

            // Close every item
            items.forEach(otherItem => {
                const otherButton = otherItem.querySelector(".an-trivia-winners-faq__question");
                const otherAnswer = otherItem.querySelector(".an-trivia-winners-faq__answer");

                if (!otherButton || !otherAnswer) return;

                closeTriviaWinnersFaqItem(otherItem, otherButton, otherAnswer);
            });

            // Reopen clicked item if it wasn't already open
            if (!isOpen) {
                openTriviaWinnersFaqItem(item, button, answer);
            }
        });
    });
}

function openTriviaWinnersFaqItem(item, button, answer) {
    item.classList.add("is-open");
    button.setAttribute("aria-expanded", "true");
    answer.style.maxHeight = `${answer.scrollHeight}px`;
}

function closeTriviaWinnersFaqItem(item, button, answer) {
    item.classList.remove("is-open");
    button.setAttribute("aria-expanded", "false");
    answer.style.maxHeight = null;
}