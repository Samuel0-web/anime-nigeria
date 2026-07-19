document.addEventListener("DOMContentLoaded", () => {

    const sections = document.querySelectorAll(".an-legal-card");
    const links = document.querySelectorAll(".an-legals__nav a");

    if (!sections.length || !links.length) return;

    const activateLink = (id) => {

        links.forEach(link => {
            link.classList.toggle(
                "is-active",
                link.getAttribute("href") === `#${id}`
            );
        });

    };

    // Click
    links.forEach(link => {

        link.addEventListener("click", () => {
            activateLink(link.hash.substring(1));
        });

    });

    // Scroll
    const observer = new IntersectionObserver(entries => {

        entries.forEach(entry => {

            if(entry.isIntersecting){
                activateLink(entry.target.id);
            }

        });

    },{
        rootMargin:"-35% 0px -55% 0px",
        threshold:0
    });

    sections.forEach(section => observer.observe(section));

});