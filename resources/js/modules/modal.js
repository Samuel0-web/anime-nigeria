// resources/js/modules/modal.js
// Generic, reusable modal shell. Knows nothing about any specific page's content.

import { loadPhpComponent } from './load-php-component.js';

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

async function createModalShell() {
    const id = `akd-modal-${++uid}`;
    const overlay = await loadPhpComponent('/components/modal');
    const modal = overlay.querySelector('.akd-modal');
    modal.id = id;
    const top = modal.querySelector('.akd-modal__top');
    const dragHandle = modal.querySelector('.akd-modal__drag-handle');
    const header = modal.querySelector('.akd-modal__header');
    const title = modal.querySelector('.akd-modal__title');
    title.id = `${id}-title`;
    const subtitle = modal.querySelector('.akd-modal__subtitle');
    subtitle.id = `${id}-subtitle`;
    const closeButton = modal.querySelector('[data-modal-close]');
    const body = modal.querySelector('[data-modal-body]');
    const footer = modal.querySelector('[data-modal-footer]');

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
    let el = null;
    let shellPromise = null;
    let isOpen = false;
    let openVersion = 0;
    let lastFocusedEl = null;
    let closeHandler = null;
    let closeRequest = null;

    function ensureShell() {
        if (el) return Promise.resolve(el);
        if (shellPromise) return shellPromise;

        shellPromise = createModalShell().then((shell) => {
            el = shell;
            el.closeButton.addEventListener('click', requestClose);
            el.overlay.addEventListener('click', (event) => {
                if (event.target === el.overlay) requestClose();
            });
            return el;
        }).finally(() => {
            shellPromise = null;
        });

        return shellPromise;
    }

    function restoreFocus() {
        const toFocus = lastFocusedEl;
        lastFocusedEl = null;
        toFocus?.focus();
    }

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
        if (!el) return;
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
        openVersion += 1;
        el?.overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        document.removeEventListener('keydown', handleKeydown);
        closeHandler = null;
        cleanup();
        restoreFocus();
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

            if (el) {
                reallyClose();
                return;
            }

            isOpen = false;
            openVersion += 1;
            closeHandler = null;
            restoreFocus();
        })();

        closeRequest = request;

        try {
            await request;
        } finally {
            if (closeRequest === request) closeRequest = null;
        }
    }

    function open({ title = '', subtitle = '', content = null, footer = null, 
        size = 'default', className = '', beforeClose = null, initialFocus = null,
    } = {}) {
        if (isOpen) {
            if (el) {
                reallyClose();
            } else {
                isOpen = false;
                openVersion += 1;
                closeHandler = null;
                restoreFocus();
            }
        }

        const version = ++openVersion;
        closeRequest = null;
        lastFocusedEl = document.activeElement;
        closeHandler = beforeClose;
        isOpen = true;

        return ensureShell().then(() => {
            if (!isOpen || version !== openVersion) return;

            document.body.appendChild(el.overlay);
            setTitle(title);
            setSubtitle(subtitle);
            setContent(content);
            setFooter(footer);
            setSize(size);
            setClassName(className);
            el.overlay.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            document.addEventListener('keydown', handleKeydown);

            requestAnimationFrame(() => {
                if (!isOpen || version !== openVersion) return;
                if (initialFocus instanceof HTMLElement) {
                    initialFocus.focus();
                    return;
                }

                const first = getFocusable(el.modal)[0];
                (first || el.modal).focus();
            });
        }).catch((error) => {
            if (version === openVersion) {
                isOpen = false;
                closeHandler = null;
                restoreFocus();
            }
            console.error(error);
        });
    }

    function isModalOpen() {
        return isOpen;
    }

    instance = {
        get elements() { return el; },
        api: {
            open,
            close: requestClose, // was reallyClose — bypassed beforeClose
            isOpen: isModalOpen,
            getBody: () => el?.body ?? null,
            getFooter: () => el?.footer ?? null,
            getModal: () => el?.modal ?? null,
            getOverlay: () => el?.overlay ?? null,
        },
    };

    return instance.api;
}