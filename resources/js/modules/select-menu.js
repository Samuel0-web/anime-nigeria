// resources/js/modules/select-menu.js
// Shared dropdown. Same structure as the modal and the confirm dialog: a PHP skeleton loaded
// once, one singleton that owns it, and an enhancer for the trigger markup.
//
//   <div class="akd-select" data-select>
//     <button type="button" class="akd-admin-field__input akd-select__trigger" aria-haspopup="listbox" aria-expanded="false">
//       <span class="akd-select__label" data-select-label></span>
//       <i class="fa-solid fa-chevron-down akd-select__chevron" aria-hidden="true"></i>
//     </button>
//   </div>
//
//   const select = initSelect(el, { options: [{ value, label, description? }], value, placeholder, title, onChange });
import { loadPhpComponent } from './load-php-component.js';

const SHEET_QUERY = '(max-width: 640px)';
const MAX_HEIGHT = 320;
let instance = null;
let uid = 0;

export function useSelectMenu() {
    if (instance) return instance;

    let el = null;
    let shellPromise = null;
    let current = null;
    let previousOverflow = '';
    let typeBuffer = '';
    let typeTimer = 0;

    function ensureShell() {
        if (el) return Promise.resolve(el);
        if (shellPromise) return shellPromise;

        shellPromise = loadPhpComponent('/components/select-menu').then((overlay) => {
            el = {
                overlay,
                panel: overlay.querySelector('[data-select-panel]'),
                title: overlay.querySelector('[data-select-title]'),
                list: overlay.querySelector('[data-select-list]'),
            };
            document.body.appendChild(overlay);

            overlay.addEventListener('pointerdown', (event) => {
                if (event.target === overlay) close();
            });
            el.list.addEventListener('click', (event) => {
                const item = event.target.closest('[data-index]');
                if (item) choose(Number(item.dataset.index));
            });
            el.list.addEventListener('pointermove', (event) => {
                const item = event.target.closest('[data-index]');
                if (item && current) setActive(Number(item.dataset.index), { scroll: false });
            });

            return el;
        }).finally(() => { shellPromise = null; });

        return shellPromise;
    }

    function render() {
        el.list.replaceChildren();
        const base = `akd-select-opt-${++uid}`;

        current.options.forEach((option, index) => {
            const li = document.createElement('li');
            li.className = 'akd-select-option';
            li.id = `${base}-${index}`;
            li.dataset.index = String(index);
            li.setAttribute('role', 'option');
            li.setAttribute('aria-selected', String(option.value === current.value));

            const text = document.createElement('span');
            text.className = 'akd-select-option__text';
            const label = document.createElement('span');
            label.className = 'akd-select-option__label';
            label.textContent = option.label;
            text.appendChild(label);

            if (option.description) {
                const description = document.createElement('span');
                description.className = 'akd-select-option__desc';
                description.textContent = option.description;
                text.appendChild(description);
            }

            const check = document.createElement('i');
            check.className = 'fa-solid fa-check akd-select-option__check';
            check.setAttribute('aria-hidden', 'true');
            li.append(text, check);
            el.list.appendChild(li);
        });

        el.title.textContent = current.title || '';
        el.title.hidden = !current.title;
        el.panel.setAttribute('aria-label', current.title || 'Options');
    }

    function setActive(index, { scroll = true } = {}) {
        const items = el.list.children;
        if (!items.length) return;
        const next = Math.max(0, Math.min(items.length - 1, index));

        Array.from(items).forEach((item, i) => item.classList.toggle('is-active', i === next));
        current.active = next;
        el.panel.setAttribute('aria-activedescendant', items[next].id);
        if (scroll) items[next].scrollIntoView({ block: 'nearest' });
    }

    function place() {
        const sheet = window.matchMedia(SHEET_QUERY).matches;
        el.overlay.dataset.mode = sheet ? 'sheet' : 'popover';
        el.panel.removeAttribute('style');

        if (sheet) {
            previousOverflow = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
            return;
        }

        const rect = current.anchor.getBoundingClientRect();
        const gap = 6;
        const pad = 8;
        const width = Math.max(rect.width, 208);
        el.panel.style.width = `${width}px`;

        const natural = Math.min(el.panel.scrollHeight, MAX_HEIGHT);
        const below = window.innerHeight - rect.bottom - gap - pad;
        const above = rect.top - gap - pad;
        const flip = below < natural && above > below;
        const height = Math.min(natural, Math.max(flip ? above : below, 120));
        const left = Math.max(pad, Math.min(rect.left, window.innerWidth - width - pad));

        el.panel.style.maxHeight = `${height}px`;
        el.panel.style.top = `${flip ? rect.top - gap - height : rect.bottom + gap}px`;
        el.panel.style.left = `${left}px`;
        el.panel.dataset.placement = flip ? 'top' : 'bottom';
    }

    function onKeydown(event) {
        if (!current) return;
        const stop = () => { event.preventDefault(); event.stopPropagation(); };

        switch (event.key) {
            case 'Escape': stop(); event.stopImmediatePropagation(); close(); return;
            case 'ArrowDown': stop(); setActive(current.active + 1); return;
            case 'ArrowUp': stop(); setActive(current.active - 1); return;
            case 'Home': stop(); setActive(0); return;
            case 'End': stop(); setActive(current.options.length - 1); return;
            case 'Enter':
            case ' ': stop(); choose(current.active); return;
            case 'Tab': close(); return;
            default: break;
        }

        if (event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
            stop();
            clearTimeout(typeTimer);
            typeBuffer += event.key.toLowerCase();
            typeTimer = setTimeout(() => { typeBuffer = ''; }, 600);
            const start = current.options.findIndex((o) => o.label.toLowerCase().startsWith(typeBuffer));
            if (start !== -1) setActive(start);
        }
    }

    // A page or modal scroll would leave the popover floating in the wrong place: close it instead.
    function onScroll(event) {
        if (current && el.overlay.dataset.mode === 'popover' && !el.panel.contains(event.target)) close();
    }

    function bind() {
        document.addEventListener('keydown', onKeydown, true);
        window.addEventListener('scroll', onScroll, true);
        window.addEventListener('resize', close);
    }

    function unbind() {
        document.removeEventListener('keydown', onKeydown, true);
        window.removeEventListener('scroll', onScroll, true);
        window.removeEventListener('resize', close);
    }

    function choose(index) {
        const option = current?.options[index];
        if (!option) return;
        const { onSelect } = current;
        close();
        onSelect?.(option.value, option);
    }

    function close({ restoreFocus = true } = {}) {
        if (!current) return;
        const { anchor, onClose } = current;
        current = null;
        unbind();
        clearTimeout(typeTimer);
        typeBuffer = '';

        if (el) {
            el.overlay.classList.remove('is-open');
            if (el.overlay.dataset.mode === 'sheet') document.body.style.overflow = previousOverflow;
        }

        onClose?.();
        if (restoreFocus && anchor?.isConnected) anchor.focus();
    }

    async function open({ anchor, options, value = '', title = '', onSelect = null, onClose = null }) {
        if (current) close({ restoreFocus: false });
        const mine = { anchor, options, value, title, onSelect, onClose, active: 0 };
        current = mine;
        await ensureShell();
        if (current !== mine) return;

        render();
        place();
        bind();
        el.overlay.classList.add('is-open');
        setActive(Math.max(0, options.findIndex((o) => o.value === value)));
        requestAnimationFrame(() => { if (current === mine) el.panel.focus({ preventScroll: true }); });
    }

    instance = {
        open,
        close,
        isOpen: () => current !== null,
        anchor: () => current?.anchor ?? null,
    };

    return instance;
}

export function initSelect(root, { options = [], value = '', placeholder = '', title = '', onChange = null } = {}) {
    const trigger = root.querySelector('.akd-select__trigger');
    const labelEl = root.querySelector('[data-select-label]');
    const menu = useSelectMenu();
    let items = options;
    let selected = value;

    function sync() {
        const found = items.find((o) => o.value === selected);
        const text = found ? found.label : (placeholder || items[0]?.label || '');
        labelEl.textContent = text;
        root.classList.toggle('is-placeholder', !found && Boolean(placeholder));
    }

    function openMenu() {
        if (trigger.disabled) return;
        trigger.setAttribute('aria-expanded', 'true');
        menu.open({
            anchor: trigger,
            options: items,
            value: selected,
            title,
            onSelect: (next) => {
                if (next === selected) return;
                selected = next;
                sync();
                onChange?.(next);
            },
            onClose: () => trigger.setAttribute('aria-expanded', 'false'),
        });
    }

    trigger.addEventListener('click', openMenu);
    trigger.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            openMenu();
        }
    });

    sync();

    return {
        setOptions(next) { items = next; sync(); },
        setValue(next, { silent = true } = {}) {
            selected = next;
            sync();
            if (!silent) onChange?.(next);
        },
        getValue: () => selected,
        setDisabled(disabled) { trigger.disabled = Boolean(disabled); },
        focus: () => trigger.focus(),
    };
}