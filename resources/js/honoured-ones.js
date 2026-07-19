// =============================================================================
// HONORED ONES
// =============================================================================

document.addEventListener('DOMContentLoaded', () => {

    if (typeof honoredOnes === 'undefined') return;

    const grid = document.getElementById('honoredGrid');
    const awardCount = document.getElementById('awardCount');
    const topHonoree = document.getElementById('topHonoree');
    const topHonoreeAwards = document.getElementById('topHonoreeAwards');
    const yearButtons = document.querySelectorAll('.an-honored__year');

    const icons = {
        'Community MVP': 'fa-crown',
        'Most Active Male': 'fa-bolt',
        'Most Active Female': 'fa-star',
        'Best Newcomer': 'fa-seedling',
        'Top Contributor': 'fa-pen-nib',
        'Funniest': 'fa-face-laugh',
        'Most Mature': 'fa-shield-halved',
        'Most Knowledgeable': 'fa-book-open',
        'Trivia Champion': 'fa-brain',
        'Best Event Participant': 'fa-fire',
        'Most Friendly Admin': 'fa-heart',
        'Most Active Admin': 'fa-user-gear'
    };

    function renderYear(year) {
        const awards = honoredOnes[year];
        grid.classList.add('is-changing');

        setTimeout(() => {
            grid.innerHTML = '';

            awards.forEach(({ category, winner }) => {

                const card = document.createElement('article');
                card.className = 'an-honored__card';

                const icon = icons[category] ?? 'fa-trophy';

                card.innerHTML = `
                    <div class="an-honored__icon">
                        <i class="fa-solid ${icon}" aria-hidden="true"></i>
                    </div>

                    <h3 class="an-honored__category">${category}</h3>
                    <p class="an-honored__winner">${winner}</p>
                `;

                grid.appendChild(card);

            });

            awardCount.textContent = awards.length;
            const counts = {};

            awards.forEach(({ winner }) => {
                counts[winner] = (counts[winner] || 0) + 1;
            });

            const leader = Object.entries(counts).sort((a, b) => b[1] - a[1])[0];
            topHonoree.textContent = leader[0];
            topHonoreeAwards.textContent = `${leader[1]} Awards`;
            grid.classList.remove('is-changing');

        }, 250);

    }

    yearButtons.forEach(button => {

        button.addEventListener('click', () => {

            yearButtons.forEach(btn =>
                btn.classList.remove('is-active')
            );

            button.classList.add('is-active');

            renderYear(button.dataset.year);

        });

    });

    renderYear(yearButtons[0].dataset.year);

});