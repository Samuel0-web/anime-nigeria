// =============================================================================
// BLOG HERO
// =============================================================================
// Handles two purely visual behaviours: a fake search submission (no real
// filtering or API call) and category pill selection using the ARIA
// radiogroup pattern (roving tabindex, arrow-key navigation).

export function initBlogHero() {
    initFakeSearch();
    initCategoryPills();
}

function initFakeSearch() {
    const form = document.getElementById("blog-search-form");
    const input = document.getElementById("blog-search-input");
    const feedback = document.getElementById("blog-search-feedback");
    if (!form || !input || !feedback) return;

    let feedbackTimer = null;

    form.addEventListener("submit", (event) => {
        event.preventDefault();

        const query = input.value.trim();

        // No real search — this is a visual-only placeholder.
        console.log("[Blog search] fake submission:", query || "(empty)");

        feedback.textContent = query
            ? `Showing results for "${query}"`
            : "Enter a search term to explore articles.";
        feedback.classList.add("is-visible");

        clearTimeout(feedbackTimer);
        feedbackTimer = setTimeout(() => {
            feedback.classList.remove("is-visible");
        }, 4000);
    });
}

function initCategoryPills() {
    const group = document.querySelector(".an-blog-hero__categories");
    if (!group) return;
    const pills = Array.from(group.querySelectorAll(".an-blog-hero__category"));
    if (!pills.length) return;

    const selectPill = (pill) => {
        pills.forEach((p) => {
            p.setAttribute("aria-checked", "false");
            p.setAttribute("tabindex", "-1");
        });

        pill.setAttribute("aria-checked", "true");
        pill.setAttribute("tabindex", "0");

        // No real filtering — visual state only.
        console.log("[Blog categories] selected:", pill.dataset.category);
    };

    pills.forEach((pill, index) => {
        pill.addEventListener("click", () => selectPill(pill));

        pill.addEventListener("keydown", (event) => {
            let targetIndex = null;

            switch (event.key) {
                case "ArrowRight":
                case "ArrowDown":
                    targetIndex = (index + 1) % pills.length;
                    break;
                case "ArrowLeft":
                case "ArrowUp":
                    targetIndex = (index - 1 + pills.length) % pills.length;
                    break;
                case "Home":
                    targetIndex = 0;
                    break;
                case "End":
                    targetIndex = pills.length - 1;
                    break;
                default:
                    return;
            }

            event.preventDefault();
            const target = pills[targetIndex];
            selectPill(target);
            target.focus();
            target.scrollIntoView({ inline: "nearest", behavior: "smooth", block: "nearest" });
        });
    });
}