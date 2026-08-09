// resources/js/modules/modal.js
// Generic, reusable modal shell. Knows nothing about any specific page's content.

let instance = null;
let uid = 0;

const FOCUSABLE_SELECTOR = [
    'button:not([disabled])',
    '[href]',
    'input:not([disabled])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(',');

function createModalShell() {
    const id = `akd-modal-${++uid}`;
    const overlay = document.createElement('div');
    overlay.className = 'akd-modal-overlay';
    overlay.setAttribute('data-modal-overlay', '');
    const modal = document.createElement('div');
    modal.className = 'akd-modal';
    modal.id = id;
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('tabindex', '-1');
    const top = document.createElement('div');
    top.className = 'akd-modal__top';
    const dragHandle = document.createElement('div');
    dragHandle.className = 'akd-modal__drag-handle';
    dragHandle.setAttribute('aria-hidden', 'true');
    const header = document.createElement('div');
    header.className = 'akd-modal__header';
    const headerText = document.createElement('div');
    headerText.className = 'akd-modal__header-text';
    const title = document.createElement('h2');
    title.className = 'akd-modal__title';
    title.id = `${id}-title`;
    const subtitle = document.createElement('p');
    subtitle.className = 'akd-modal__subtitle';
    subtitle.id = `${id}-subtitle`;
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'akd-modal__close';
    closeButton.setAttribute('data-modal-close', '');
    closeButton.setAttribute('aria-label', 'Close');
    closeButton.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i>';
    headerText.append(title, subtitle);
    header.append(headerText, closeButton);
    top.append(dragHandle, header);
    const body = document.createElement('div');
    body.className = 'akd-modal__body';
    body.setAttribute('data-modal-body', '');
    const footer = document.createElement('div');
    footer.className = 'akd-modal__footer';
    footer.setAttribute('data-modal-footer', '');
    footer.hidden = true;
    modal.append(top, body, footer);
    overlay.appendChild(modal);

    return { id, overlay, modal, top, dragHandle, header, title, subtitle, closeButton,
        body, footer
    };
}

function getFocusable(modal) {
    return Array.from(modal.querySelectorAll(FOCUSABLE_SELECTOR))
        .filter((el) => !el.closest('[inert]'));
}

export function useModal() {
    if (instance) return instance.api;
    const el = createModalShell();
    document.body.appendChild(el.overlay);
    let isOpen = false;
    let lastFocusedEl = null;
    let closeHandler = null;
    let closeRequest = null;

    function handleKeydown(event) {
        if (!isOpen) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            requestClose();
            return;
        }

        if (event.key !== 'Tab') return;
        const focusables = getFocusable(el.modal);

        if (!focusables.length) {
            event.preventDefault();
            el.modal.focus();
            return;
        }

        const first = focusables[0];
        const last = focusables[focusables.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    function setTitle(value) {
        el.title.textContent = value || '';
        el.title.hidden = !value;

        if (value) {
            el.modal.setAttribute('aria-labelledby', el.title.id);
        } else {
            el.modal.removeAttribute('aria-labelledby');
        }
    }

    function setSubtitle(value) {
        el.subtitle.textContent = value || '';
        el.subtitle.hidden = !value;

        if (value) {
            el.modal.setAttribute('aria-describedby', el.subtitle.id);
        } else {
            el.modal.removeAttribute('aria-describedby');
        }
    }

    function setContent(content) {
        el.body.replaceChildren();

        if (content instanceof Node) {
            el.body.appendChild(content);
        } else if (typeof content === 'string') {
            el.body.innerHTML = content;
        }
    }

    function setFooter(content) {
        el.footer.replaceChildren();

        if (!content) {
            el.footer.hidden = true;
            return;
        }

        el.footer.hidden = false;

        if (content instanceof Node) {
            el.footer.appendChild(content);
        } else if (typeof content === 'string') {
            el.footer.innerHTML = content;
        }
    }

    function setSize(size = 'default') {
        if (size === 'default') {
            delete el.modal.dataset.size;
        } else {
            el.modal.dataset.size = size;
        }
    }

    function setClassName(className) {
        el.modal.className = 'akd-modal';

        if (className) {
            el.modal.classList.add(...className.split(' ').filter(Boolean));
        }
    }

    function cleanup() {
        el.body.replaceChildren();
        el.footer.replaceChildren();
        el.footer.hidden = true;
        el.modal.removeAttribute('aria-labelledby');
        el.modal.removeAttribute('aria-describedby');
        delete el.modal.dataset.size;
        el.modal.className = 'akd-modal';
    }

    function reallyClose() {
        if (!isOpen) return;
        isOpen = false;
        el.overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        document.removeEventListener('keydown', handleKeydown);
        closeHandler = null;
        cleanup();
        const toFocus = lastFocusedEl;
        lastFocusedEl = null;
        toFocus?.focus();
    }

    // Single source of truth for "can this modal close?". Escape, backdrop
    // click, the close (X) button, and the public close() API all route
    // through here — beforeClose can never be bypassed by any of them.
    async function requestClose() {
        if (!isOpen) return;
        if (closeRequest) return closeRequest;

        const request = (async () => {
            if (typeof closeHandler === 'function') {
                const result = await closeHandler();
                if (result === false || !isOpen) return;
            }

            reallyClose();
        })();

        closeRequest = request;

        try {
            await request;
        } finally {
            if (closeRequest === request) closeRequest = null;
        }
    }

    function open({ title = '', subtitle = '', content = null, footer = null, size = 'default',
        className = '', beforeClose = null, initialFocus = null,
    } = {}) {
        if (isOpen) reallyClose();
        closeRequest = null;
        lastFocusedEl = document.activeElement;
        setTitle(title);
        setSubtitle(subtitle);
        setContent(content);
        setFooter(footer);
        setSize(size);
        setClassName(className);
        closeHandler = beforeClose;
        isOpen = true;
        el.overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', handleKeydown);

        requestAnimationFrame(() => {
            if (initialFocus instanceof HTMLElement) {
                initialFocus.focus();
                return;
            }

            const first = getFocusable(el.modal)[0];
            (first || el.modal).focus();
        });
    }

    function isModalOpen() {
        return isOpen;
    }

    el.closeButton.addEventListener('click', requestClose);

    el.overlay.addEventListener('click', (event) => {
        if (event.target === el.overlay) requestClose();
    });

    instance = {
        elements: el,
        api: {
            open,
            close: requestClose, // was reallyClose — bypassed beforeClose
            isOpen: isModalOpen,
            getBody: () => el.body,
            getFooter: () => el.footer,
            getModal: () => el.modal,
            getOverlay: () => el.overlay,
        },
    };

    return instance.api;
}