let container;

const icons = {
    success: "fa-solid fa-circle-check",
    error: "fa-solid fa-circle-xmark",
    warning: "fa-solid fa-triangle-exclamation",
    info: "fa-solid fa-circle-info",
};

function getContainer() {
    if (container) return container;
    container = document.createElement("div");
    container.className = "an-toast-container";
    document.body.appendChild(container);
    return container;
}

export function toast({message, type = "info", duration = 4000}) {
    const toastContainer  = getContainer();
    const el = document.createElement("div");
    el.className = `an-toast an-toast--${type}`;

    el.innerHTML = `
        <div class="an-toast__icon">
            <i class="${icons[type] ?? icons.info}"></i>
        </div>

        <div class="an-toast__body">
            <p class="an-toast__message">${message}</p>
        </div>

        <button class="an-toast__close" type="button" aria-label="Close notification">
            <i class="fa-solid fa-xmark"></i>
        </button>
    `;

    toastContainer .appendChild(el);

    requestAnimationFrame(() => {
        el.classList.add("is-visible");
    });

    let timer = setTimeout(remove, duration);

    function remove() {
        clearTimeout(timer);
        el.classList.remove("is-visible");

        el.addEventListener("transitionend",() => {
            el.remove();

            if (!toastContainer.children.length) {
                toastContainer.remove();
                container = null;
            }
        }, { once: true });
    }

    el.querySelector(".an-toast__close").addEventListener("click", remove);

    return {
        close: remove,
    };
}

export const success = (message, duration) => toast({ type: "success", message, duration });
export const error = (message, duration) => toast({ type: "error", message, duration });
export const warning = (message, duration) => toast({ type: "warning", message, duration });
export const info = (message, duration) => toast({ type: "info", message, duration });