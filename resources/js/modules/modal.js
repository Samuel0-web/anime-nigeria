let instance = null;

const FOCUSABLE_SELECTOR = ['button:not([disabled])', '[href]', 'input:not([disabled])',
    'select:not([disabled])', 'textarea:not([disabled])', '[tabindex]:not([tabindex="-1"])',
].join(',');

function createModalShell() {
    const overlay = document.createElement('div');
    overlay.className = 'akd-modal-overlay';
    overlay.setAttribute('data-modal-overlay', '');
    const modal = document.createElement('div');
    modal.className = 'akd-modal';
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
    title.setAttribute('data-modal-title', '');
    const subtitle = document.createElement('p');
    subtitle.className = 'akd-modal__subtitle';
    subtitle.setAttribute('data-modal-subtitle', '');
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
    modal.append(top, body, footer);
    overlay.appendChild(modal);

    return { overlay, modal, top, dragHandle, header, title, subtitle, closeButton, body, footer, };
}

function getFocusable(modal) {
    return Array.from(
        modal.querySelectorAll(FOCUSABLE_SELECTOR)
    ).filter((el) => !el.closest('[inert]'));
}

export function useModal() {
    if (instance) {
        return instance.api;
    }

    const elements = createModalShell();
    document.body.appendChild(elements.overlay);
    let isOpen = false;
    let lastFocusedEl = null;
    let closeHandler = null;

    function handleKeydown(event) {
        if (!isOpen) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            requestClose();
            return;
        }

        if (event.key !== 'Tab') return;
        const focusables = getFocusable(elements.modal);

        if (!focusables.length) {
            event.preventDefault();
            elements.modal.focus();
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
        elements.title.textContent = value || '';
        elements.title.hidden = !value;

        if (value) {
            elements.modal.setAttribute('aria-labelledby', elements.title.id || ensureTitleId());
        } else {
            elements.modal.removeAttribute('aria-labelledby');
        }
    }

    function ensureTitleId() {
        const id = 'akdModalTitle';

        if (!elements.title.id) {
            elements.title.id = id;
        }

        return id;
    }

    function setSubtitle(value) {
        elements.subtitle.textContent = value || '';
        elements.subtitle.hidden = !value;

        if (value) {
            if (!elements.subtitle.id) {
                elements.subtitle.id = 'akdModalDescription';
            }

            elements.modal.setAttribute('aria-describedby', elements.subtitle.id);
        } else {
            elements.modal.removeAttribute('aria-describedby');
        }
    }

    function setContent(content) {
        elements.body.replaceChildren();

        if (content instanceof Node) {
            elements.body.appendChild(content);
            return;
        }

        if (typeof content === 'string') {
            elements.body.innerHTML = content;
        }
    }

    function setFooter(content) {
        elements.footer.replaceChildren();

        if (!content) {
            elements.footer.hidden = true;
            return;
        }

        elements.footer.hidden = false;

        if (content instanceof Node) {
            elements.footer.appendChild(content);
            return;
        }

        if (typeof content === 'string') {
            elements.footer.innerHTML = content;
        }
    }

    function setSize(size = 'default') {
        elements.modal.dataset.size = size;
    }

    function setClassName(className) {
        elements.modal.className = 'akd-modal';

        if (className) {
            elements.modal.classList.add(...className.split(' '));
        }
    }

    function cleanup() {
        elements.body.replaceChildren();
        elements.footer.replaceChildren();
        elements.footer.hidden = true;
        elements.modal.removeAttribute('aria-labelledby');
        elements.modal.removeAttribute('aria-describedby');
        elements.modal.removeAttribute('data-size');
        elements.modal.className = 'akd-modal';
    }

    function reallyClose() {
        if (!isOpen) return;
        isOpen = false;
        elements.overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        document.removeEventListener('keydown', handleKeydown);
        const handler = closeHandler;
        closeHandler = null;
        cleanup();

        if (typeof handler === 'function') {
            handler();
        }

        lastFocusedEl?.focus();
        lastFocusedEl = null;
    }

    async function requestClose() {
        if (!isOpen) return;

        if (typeof closeHandler === 'function') {
            // Close handling is deliberately owned by the caller.
            // The handler may return false or a Promise<boolean>.
            const result = await closeHandler();

            if (result === false) {
                return;
            }
        }

        reallyClose();
    }

    function open({ title = '', subtitle = '', content = null, footer = null, size = 'default',
        className = '', beforeClose = null, initialFocus = null,
    } = {}) {
        if (isOpen) {
            reallyClose();
        }

        lastFocusedEl = document.activeElement;
        setTitle(title);
        setSubtitle(subtitle);
        setContent(content);
        setFooter(footer);
        setSize(size);
        setClassName(className);
        closeHandler = beforeClose;
        isOpen = true;
        elements.overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', handleKeydown);

        requestAnimationFrame(() => {
            if (initialFocus instanceof HTMLElement) {
                initialFocus.focus();
                return;
            }

            const firstFocusable = getFocusable(elements.modal)[0];

            if (firstFocusable) {
                firstFocusable.focus();
            } else {
                elements.modal.focus();
            }
        });
    }

    function close() {
        reallyClose();
    }

    function isModalOpen() {
        return isOpen;
    }

    function getBody() {
        return elements.body;
    }

    function getFooter() {
        return elements.footer;
    }

    function getModal() {
        return elements.modal;
    }

    function getOverlay() {
        return elements.overlay;
    }

    elements.closeButton.addEventListener('click', requestClose);

    elements.overlay.addEventListener('click', (event) => {
        if (event.target === elements.overlay) {
            requestClose();
        }
    });

    instance = { elements, api: { open, close, isOpen: isModalOpen, getBody, getFooter, getModal,
        getOverlay,
    },};

    return instance.api;
}