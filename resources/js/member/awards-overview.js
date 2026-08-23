// Nominee rail: scroll-by buttons + disabled state at the ends.
// Native drag/touch scroll already works via scroll-snap, so this
// only handles the desktop click affordance.

function initAwardRail() {
    const wrap = document.querySelector('.akd-award-rail-wrap');
    if (!wrap) return;
    const track = wrap.querySelector('[data-award-rail]');
    const prevBtn = wrap.querySelector('[data-award-rail-prev]');
    const nextBtn = wrap.querySelector('[data-award-rail-next]');
    if (!track || !prevBtn || !nextBtn) return;
    const scrollAmount = () => track.clientWidth * 0.8;

    const updateButtons = () => {
        const maxScroll = track.scrollWidth - track.clientWidth - 1;
        prevBtn.disabled = track.scrollLeft <= 0;
        nextBtn.disabled = track.scrollLeft >= maxScroll;
    };

    prevBtn.addEventListener('click', () => {
        track.scrollBy({ left: -scrollAmount(), behavior: 'smooth' });
    });

    nextBtn.addEventListener('click', () => {
        track.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
    });

    track.addEventListener('scroll', updateButtons, { passive: true });
    window.addEventListener('resize', updateButtons);
    updateButtons();
}

document.addEventListener('DOMContentLoaded', initAwardRail);
export default initAwardRail;