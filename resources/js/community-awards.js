// =============================================================================
// COMMUNITY AWARDS FAQ
// =============================================================================

export function initCommunityAwardsFaq() {
    const items = document.querySelectorAll(
        ".an-community-awards-faq__item"
    );

    if (!items.length) return;

    items.forEach((item, index) => {
        const button = item.querySelector(
            ".an-community-awards-faq__question"
        );

        const answer = item.querySelector(
            ".an-community-awards-faq__answer"
        );

        if (!button || !answer) return;

        // Open the first FAQ by default
        if (index === 0) {
            openCommunityFaqItem(item, button, answer);
        }

        button.addEventListener("click", () => {
            const isOpen = item.classList.contains("is-open");

            // Close every FAQ
            items.forEach(otherItem => {
                const otherButton = otherItem.querySelector(
                    ".an-community-awards-faq__question"
                );

                const otherAnswer = otherItem.querySelector(
                    ".an-community-awards-faq__answer"
                );

                if (!otherButton || !otherAnswer) return;

                closeCommunityFaqItem(
                    otherItem,
                    otherButton,
                    otherAnswer
                );
            });

            // Open the clicked one if it wasn't already open
            if (!isOpen) {
                openCommunityFaqItem(item, button, answer);
            }
        });
    });
}

function openCommunityFaqItem(item, button, answer) {
    item.classList.add("is-open");
    button.setAttribute("aria-expanded", "true");
    answer.style.maxHeight = `${answer.scrollHeight}px`;
}

function closeCommunityFaqItem(item, button, answer) {
    item.classList.remove("is-open");
    button.setAttribute("aria-expanded", "false");
    answer.style.maxHeight = null;
}