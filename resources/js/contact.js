// =============================================================================
// CONTACT FAQ
// =============================================================================

export function initContactFaq() {
    const items = document.querySelectorAll(".an-contact-faq__item");
    if (!items.length) return;

    items.forEach((item, index) => {
        const button = item.querySelector(".an-contact-faq__question");
        const answer = item.querySelector(".an-contact-faq__answer");

        if (!button || !answer) return;

        // Open first item
        if (index === 0) {
            openContactFaqItem(item, button, answer);
        }

        button.addEventListener("click", () => {
            const isOpen = item.classList.contains("is-open");

            // Close every item
            items.forEach(otherItem => {
                const otherButton = otherItem.querySelector(".an-contact-faq__question");
                const otherAnswer = otherItem.querySelector(".an-contact-faq__answer");

                if (!otherButton || !otherAnswer) return;

                closeContactFaqItem(otherItem, otherButton, otherAnswer);
            });

            // Reopen clicked item if it wasn't already open
            if (!isOpen) {
                openContactFaqItem(item, button, answer);
            }
        });
    });
}

function openContactFaqItem(item, button, answer) {
    item.classList.add("is-open");
    button.setAttribute("aria-expanded", "true");
    answer.style.maxHeight = `${answer.scrollHeight}px`;
}

function closeContactFaqItem(item, button, answer) {
    item.classList.remove("is-open");
    button.setAttribute("aria-expanded", "false");
    answer.style.maxHeight = null;
}