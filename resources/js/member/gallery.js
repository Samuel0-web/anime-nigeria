// resources/js/member/gallery.js
// Member Gallery: category filter and a JS-computed masonry layout
// (shortest column first, using each item's real rendered dimensions
// once its image has loaded, an estimated aspect ratio otherwise).
//
// The lightbox is Fancybox (see modules/lightbox.js). The Gallery is
// responsible only for rendering Fancybox-compatible triggers; it does
// not open, close, or configure the viewer itself.
import { initLightbox } from '../modules/lightbox.js';

// Mirrors the breakpoints in _tokens.scss ($bp-md, $bp-lg, $bp-xl) and the
// $space-sm / $space-md spacing scale. Kept in sync by hand, the same
// tradeoff already accepted elsewhere in this codebase.
function getColumnCount(width) {
    if (width >= 1280) return 4;
    if (width >= 1024) return 3;
    return 2;
}

function getGap(width) {
    return width >= 1024 ? 24 : 16;
}

function debounce(fn, wait) {
    let timeoutId;

    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), wait);
    };
}

function getVisibleItems(grid) {
    return Array.from(grid.querySelectorAll('.akd-gallery__item')).filter((item) => !item.hidden);
}

// Reads real natural dimensions when the image is complete, and falls
// back to the PHP-supplied estimate otherwise. Reading naturalWidth/
// naturalHeight once the image is actually loaded closes the
// estimated-vs-rendered aspect ratio gap that used to cause column
// overlap.
function getItemDimensions(item) {
    const img = item.querySelector('.akd-gallery__image');

    if (img && img.complete && img.naturalWidth > 0 && img.naturalHeight > 0) {
        return { width: img.naturalWidth, height: img.naturalHeight };
    }

    return {
        width: Number(item.dataset.width) || 1,
        height: Number(item.dataset.height) || 1,
    };
}

function layoutMasonry(grid) {
    const items = getVisibleItems(grid);
    const containerWidth = grid.clientWidth;

    if (!containerWidth || items.length === 0) {
        grid.style.height = '0px';
        return;
    }

    const gap = getGap(containerWidth);
    const columns = Math.max(1, Math.min(getColumnCount(containerWidth), items.length));
    const columnWidth = (containerWidth - gap * (columns - 1)) / columns;
    const columnHeights = new Array(columns).fill(0);

    items.forEach((item) => {
        const { width, height } = getItemDimensions(item);
        const renderedHeight = columnWidth * (height / width);

        let targetColumn = 0;
        for (let i = 1; i < columnHeights.length; i++) {
            if (columnHeights[i] < columnHeights[targetColumn]) targetColumn = i;
        }

        const x = targetColumn * (columnWidth + gap);
        const y = columnHeights[targetColumn];

        item.style.width = `${columnWidth}px`;
        item.style.transform = `translate(${x}px, ${y}px)`;

        columnHeights[targetColumn] += renderedHeight + gap;
    });

    grid.style.height = `${Math.max(...columnHeights) - gap}px`;
    grid.classList.add('is-ready');
}

function initFilter(root, grid, onChange) {
    const filterGroup = root.querySelector('.akd-gallery__filter');
    if (!filterGroup) return;

    filterGroup.addEventListener('click', (event) => {
        const button = event.target.closest('.akd-gallery__filter-btn');
        if (!button) return;

        filterGroup.querySelectorAll('.akd-gallery__filter-btn').forEach((btn) => {
            const isActive = btn === button;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-pressed', String(isActive));
        });

        const category = button.dataset.galleryFilter;

        grid.querySelectorAll('.akd-gallery__item').forEach((item) => {
            item.hidden = category !== 'all' && item.dataset.galleryCategory !== category;
        });

        onChange();
    });
}

function initGallery() {
    const root = document.querySelector('.akd-gallery');
    const grid = root?.querySelector('[data-gallery-grid]');
    if (!root || !grid) return;
    const filteredEmpty = root.querySelector('[data-gallery-filtered-empty]');

    function refresh() {
        const visible = getVisibleItems(grid);
        if (filteredEmpty) filteredEmpty.hidden = visible.length !== 0;
        grid.hidden = visible.length === 0;
        layoutMasonry(grid);
    }

    initFilter(root, grid, refresh);

    // Global Fancybox binding. The Gallery does not pass a selector —
    // every `[data-fancybox]` anchor on the site is covered by the one
    // delegated listener. initLightbox is idempotent, so this same call
    // from single-post or any other consumer is a no-op.
    initLightbox();

    // One shared debounced relayout, reused by both the resize observer
    // and the per-image load listeners: several images finishing around
    // the same time should collapse into one recalculation.
    const scheduleLayout = debounce(() => layoutMasonry(grid), 120);

    grid.querySelectorAll('.akd-gallery__image').forEach((img) => {
        img.addEventListener('error', () => {
            img.closest('.akd-gallery__item')?.classList.add('has-error');
        }, { once: true });

        // Cached images are already `complete` here. This listener only
        // matters for images still waiting on loading="lazy".
        if (!img.complete) {
            img.addEventListener('load', scheduleLayout, { once: true });
        }
    });

    // ResizeObserver rather than a window 'resize' listener: it also
    // picks up the sidebar collapse/expand transition, which changes
    // .akd-content's available width without the window resizing.
    const resizeObserver = new ResizeObserver(scheduleLayout);
    resizeObserver.observe(grid);
    refresh();
}

document.addEventListener('DOMContentLoaded', initGallery);