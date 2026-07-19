export function initStickyHeader() {
    const header = document.getElementById('site-header');
    if (!header) return;

    const SCROLL_THRESHOLD = 40;
    let ticking = false;

    const update = () => {
        header.classList.toggle('is-scrolled', window.scrollY > SCROLL_THRESHOLD);
        ticking = false;
    };

    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(update);
            ticking = true;
        }
    }, { passive: true });

    update();
}