// resources/js/admin/blog-featured.js
// The Featured strip: up to N numbered slots, drag (or button) reordering, optimistic with rollback.
// The database owns the order: every change is confirmed or corrected from the server's list.
import Sortable from 'sortablejs';
import { api } from '../modules/api';
import { optimistic, createSerialQueue } from '../modules/optimistic';
import { escapeHtml } from './form-utils';

const API = '/admin/api/blog';

/** Shared by the strip, the list stars and the editor's switch, so featured changes reach the server in order. */
export const featuredQueue = createSerialQueue();

export function initFeaturedStrip({ root, max, onChange, onRemove, onEdit }) {
    const list = root.querySelector('[data-feat-list]');
    let items = [];

    const renumber = (rows) => rows.map((a, i) => ({ ...a, featured_position: i + 1 }));

    function itemHtml(item, index) {
        const name = item.title_plain || 'Untitled draft';
        const live = item.status === 'published';
        const thumb = item.cover_image_url
            ? `<img src="${escapeHtml(item.cover_image_url)}" alt="" loading="lazy" decoding="async">`
            : '<i class="fa-regular fa-image" aria-hidden="true"></i>';

        return `
            <li class="akd-bl-feat__item" data-id="${item.id}">
                <button type="button" class="akd-bl-feat__grip" aria-label="Drag to reorder ${escapeHtml(name)}" title="Drag to reorder">
                    <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
                </button>
                <span class="akd-bl-feat__pos" aria-hidden="true">${index + 1}</span>
                <span class="akd-ann-thumb akd-bl-feat__thumb">${thumb}</span>
                <div class="akd-bl-feat__text">
                    <button type="button" class="akd-bl-feat__name" data-feat-edit>${escapeHtml(name)}</button>
                    <p class="akd-bl-feat__sub">
                        <span class="akd-bl-status akd-bl-status--${escapeHtml(item.status)}">${live ? 'Live' : escapeHtml(item.status)}</span>
                        ${item.category ? `<span>${escapeHtml(item.category)}</span>` : ''}
                    </p>
                </div>
                <div class="akd-bl-feat__actions">
                    <button type="button" class="akd-ann-iconbtn" data-feat-up aria-label="Move ${escapeHtml(name)} up" ${index === 0 ? 'disabled' : ''}><i class="fa-solid fa-chevron-up" aria-hidden="true"></i></button>
                    <button type="button" class="akd-ann-iconbtn" data-feat-down aria-label="Move ${escapeHtml(name)} down" ${index === items.length - 1 ? 'disabled' : ''}><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
                    <button type="button" class="akd-ann-iconbtn akd-ann-iconbtn--danger" data-feat-remove aria-label="Remove ${escapeHtml(name)} from featured"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
            </li>`;
    }

    function render() {
        const slots = [];

        for (let i = items.length; i < max; i += 1) {
            slots.push(`<li class="akd-bl-feat__slot" aria-hidden="true"><span class="akd-bl-feat__pos">${i + 1}</span>
                <span>Empty slot. Click the star on an article to feature it.</span></li>`);
        }

        list.innerHTML = items.map(itemHtml).join('') + slots.join('');
    }

    function set(next, { silent = false } = {}) {
        items = next;
        render();
        if (!silent) onChange?.(items);
    }

    async function resync() {
        try {
            const result = await api(`${API}/featured`);
            if (result.success !== false && Array.isArray(result.featured)) set(result.featured);
        } catch (err) {
            // Offline: keep what is on screen.
        }
    }

    function reorder(ids) {
        const byId = new Map(items.map((a) => [a.id, a]));
        const next = ids.map((id) => byId.get(id)).filter(Boolean);
        if (next.length !== items.length || ids.every((id, i) => items[i]?.id === id)) {
            render(); // dropped where it started: just redraw
            return;
        }

        optimistic({
            queue: featuredQueue,
            apply: () => { const previous = items; set(renumber(next)); return previous; },
            request: () => api(`${API}/featured/reorder`, { method: 'POST', body: { ids } }),
            commit: (result) => { if (Array.isArray(result.featured)) set(result.featured); },
            rollback: (previous) => { set(previous); resync(); },
            errorMessage: 'Could not reorder the featured articles.',
        });
    }

    const currentIds = () => Array.from(list.querySelectorAll('.akd-bl-feat__item')).map((li) => Number(li.dataset.id));

    Sortable.create(list, {
        animation: 150,
        handle: '.akd-bl-feat__grip',
        draggable: '.akd-bl-feat__item',
        ghostClass: 'is-ghost',
        chosenClass: 'is-chosen',
        dragClass: 'is-dragging',
        fallbackTolerance: 3,
        onMove: (event) => !event.related.classList.contains('akd-bl-feat__slot'), // never past the empty slots
        onEnd: () => reorder(currentIds()),
    });

    list.addEventListener('click', (event) => {
        const row = event.target.closest('.akd-bl-feat__item');
        if (!row) return;
        const id = Number(row.dataset.id);
        const ids = items.map((a) => a.id);
        const index = ids.indexOf(id);

        if (event.target.closest('[data-feat-remove]')) { onRemove?.(id); return; }
        if (event.target.closest('[data-feat-edit]')) { onEdit?.(id); return; }

        const move = event.target.closest('[data-feat-up]') ? -1 : event.target.closest('[data-feat-down]') ? 1 : 0;

        if (move && index + move >= 0 && index + move < ids.length) {
            [ids[index], ids[index + move]] = [ids[index + move], ids[index]];
            reorder(ids);
        }
    });

    return { set, list: () => items.map((a) => ({ ...a })), resync };
}