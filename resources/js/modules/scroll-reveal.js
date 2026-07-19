export function initScrollReveal() {
    const items = document.querySelectorAll(`.an-reveal`);
    if (!items.length) return;

    if (!("IntersectionObserver" in window)) {
        items.forEach(item => item.classList.add("is-visible"));
        return;
    }

    const observer = new IntersectionObserver(
        (entries, observer) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;

                entry.target.classList.add("is-visible");
                observer.unobserve(entry.target);
            });
        }, {
            threshold: 0.1,
            rootMargin: "0px 0px -10% 0px"
        }
    );

    items.forEach(item => observer.observe(item));
}