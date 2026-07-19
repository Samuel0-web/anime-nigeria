export function initGallery() {
    const gallery = document.querySelector(".an-gallery-browser");
    if (!gallery) return;

    const filters = gallery.querySelectorAll("[data-filter]");
    const items = gallery.querySelectorAll(".an-gallery-item");

    // ==========================================================
    // FILTERING
    // ==========================================================
    filters.forEach(button => {
        button.addEventListener("click", () => {
            const filter = button.dataset.filter;

            filters.forEach(btn =>
                btn.classList.remove("is-active")
            );

            button.classList.add("is-active");

            items.forEach(item => {
                const category = item.dataset.category;

                if (filter === "all" || category === filter) {
                    item.classList.remove("is-hidden");

                    requestAnimationFrame(() => {
                        item.classList.add("is-visible");
                    });

                } else {
                    item.classList.remove("is-visible");
                    item.classList.add("is-hidden");
                }
            });
        });
    });

    // ==========================================================
    // LIGHTBOX
    // ==========================================================
    const lightbox = document.createElement("div");
    lightbox.className = "an-lightbox";

    lightbox.innerHTML = `
        <button class="an-lightbox__close" aria-label="Close">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <button class="an-lightbox__prev" aria-label="Previous">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <img class="an-lightbox__image" alt="">

        <button class="an-lightbox__next" aria-label="Next">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    `;

    document.body.appendChild(lightbox);

    const image = lightbox.querySelector(".an-lightbox__image");

    let visibleItems = [];
    let currentIndex = 0;

    function updateVisibleItems() {
        visibleItems = [...items].filter(
            item => !item.classList.contains("is-hidden")
        );
    }

    function open(index) {
        updateVisibleItems();

        currentIndex = index;

        const img = visibleItems[currentIndex].querySelector("img");

        image.src = img.src;
        image.alt = img.alt;

        lightbox.classList.add("is-open");
        document.body.style.overflow = "hidden";
    }

    function close() {
        lightbox.classList.remove("is-open");
        document.body.style.overflow = "";
    }

    function next() {
        currentIndex =
            (currentIndex + 1) % visibleItems.length;

        const img = visibleItems[currentIndex].querySelector("img");

        image.src = img.src;
        image.alt = img.alt;
    }

    function prev() {
        currentIndex =
            (currentIndex - 1 + visibleItems.length) %
            visibleItems.length;

        const img = visibleItems[currentIndex].querySelector("img");

        image.src = img.src;
        image.alt = img.alt;
    }

    items.forEach(item => {
        item.addEventListener("click", () => {
            updateVisibleItems();

            const index = visibleItems.indexOf(item);

            if (index !== -1) {
                open(index);
            }
        });
    });

    lightbox
        .querySelector(".an-lightbox__close")
        .addEventListener("click", close);

    lightbox
        .querySelector(".an-lightbox__next")
        .addEventListener("click", next);

    lightbox
        .querySelector(".an-lightbox__prev")
        .addEventListener("click", prev);

    lightbox.addEventListener("click", e => {
        if (e.target === lightbox) close();
    });

    // ==========================================================
    // KEYBOARD
    // ==========================================================
    document.addEventListener("keydown", e => {
        if (!lightbox.classList.contains("is-open")) return;

        switch (e.key) {
            case "Escape":
                close();
                break;

            case "ArrowRight":
                next();
                break;

            case "ArrowLeft":
                prev();
                break;
        }
    });
}