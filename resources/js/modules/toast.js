let container;

function getContainer() {
    if (container) return container;

    container = document.createElement("div");
    container.className = "an-toast-container";
    document.body.appendChild(container);

    return container;
}

export function toast({message, type = "info", duration = 4000}) {
    const container = getContainer();
    const el = document.createElement("div");
    el.className = `an-toast an-toast--${type}`;

    el.innerHTML = `
        <div class="an-toast__content">
            <p class="an-toast__message">${message}</p>
            <button class="an-toast__close" type="button" aria-label="Close notification">
                &times;
            </button>
        </div>
    `;

    container.appendChild(el);

    requestAnimationFrame(() => {
        el.classList.add("is-visible");
    });

    let timer = setTimeout(remove, duration);

    function remove() {
        clearTimeout(timer);
        el.classList.remove("is-visible");

        el.addEventListener("transitionend", () => {
            el.remove();

            if (!container.children.length) {
                container.remove();
                container = null;
            }
        }, { once: true });
    }

    el.querySelector(".an-toast__close").addEventListener("click", remove);

    return {
        close: remove
    };
}

export const success = (message, duration) => toast({ type: "success", message, duration });
export const error = (message, duration) => toast({ type: "error", message, duration });
export const warning = (message, duration) => toast({ type: "warning", message, duration });
export const info = (message, duration) => toast({ type: "info", message, duration });