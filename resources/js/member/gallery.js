// resources/js/member/gallery.js
// Member Gallery: category filter, a JS-computed masonry layout (shortest
// column first, using each item's real rendered dimensions once its image
// has loaded, an estimated aspect ratio otherwise), and the shared
// lightbox from modules/lightbox.js (built on the existing .an-lightbox
// markup in _layout.scss). No prev/next controls, by request: single
// image and close only.
import { useLightbox } from '../modules/lightbox.js';

// Mirrors the breakpoints in _tokens.scss ($bp-md, $bp-lg, $bp-xl) and the
// $space-sm / $space-md spacing scale. Kept in sync by hand, the same
// tradeoff already accepted elsewhere in this codebase (see
// akd_challenge_accent_hex() in challenges-support.php).
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

// The overlap/gap bug traced to here: dataset.width/height are only ever
// the PHP-side estimated placeholders (see the comment in gallery-data.php),
// not each asset's real dimensions. Once the browser finishes loading an
// image, .akd-gallery__image's width:100%/height:auto renders it at the
// image's ACTUAL intrinsic aspect ratio, which can differ from the
// estimate, while the column bookkeeping was still reserving space for
// the estimated height. That mismatch is what let one item's real
// rendered edge fall past the top reserved for the item below it in the
// same column. Reading naturalWidth/naturalHeight once an image is
// actually complete (already true immediately for cached images) closes
// that gap: the space reserved and the space rendered become the same
// number.
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

    // Bottom edge of the tallest column, nothing more: once columnHeights
    // reflects each item's REAL rendered height instead of an estimate
    // (see getItemDimensions above), this already lands on the true
    // bottom edge of the lowest item, no separate fix needed here.
    grid.style.height = `${Math.max(...columnHeights) - gap}px`;
    grid.classList.add('is-ready');
}

function readGalleryData(root) {
    const script = root.querySelector('[data-gallery-data]');
    if (!script) return new Map();

    try {
        const list = JSON.parse(script.textContent);
        return new Map(list.map((entry) => [String(entry.id), entry]));
    } catch (error) {
        console.error('Gallery data could not be parsed', error);
        return new Map();
    }
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
    const galleryData = readGalleryData(root);
    const lightbox = useLightbox();
    let openTriggerEl = null;

    function refresh() {
        const visible = getVisibleItems(grid);

        if (filteredEmpty) filteredEmpty.hidden = visible.length !== 0;
        grid.hidden = visible.length === 0;

        // If a filter change hid the item currently shown in the lightbox,
        // close it rather than leaving it pointing at something no longer
        // in the visible collection.
        if (lightbox.isOpen() && openTriggerEl && openTriggerEl.hidden) {
            lightbox.close();
            openTriggerEl = null;
        }

        layoutMasonry(grid);
    }

    initFilter(root, grid, refresh);

    grid.addEventListener('click', (event) => {
        const item = event.target.closest('.akd-gallery__item');
        if (!item) return;

        const data = galleryData.get(item.dataset.galleryItem);
        const img = item.querySelector('.akd-gallery__image');
        if (!data || !img) return;

        openTriggerEl = item;
        lightbox.open({
            src: img.currentSrc || img.src,
            alt: img.alt,
            title: data.title,
            categoryLabel: data.categoryLabel,
            date: data.date,
            caption: data.caption,
            triggerEl: item,
        });
    });

    // One shared debounced relayout, reused by both the resize observer
    // below and the per-image load listeners: several images finishing
    // around the same time (a fast scroll through lazy-loaded items)
    // should collapse into a single recalculation, not one per image.
    const scheduleLayout = debounce(() => layoutMasonry(grid), 120);

    grid.querySelectorAll('.akd-gallery__image').forEach((img) => {
        img.addEventListener('error', () => {
            img.closest('.akd-gallery__item')?.classList.add('has-error');
        }, { once: true });

        // Cached images are already `complete` here, getItemDimensions()
        // picks up their real size on the very first layoutMasonry() call
        // with no extra event needed. This listener only matters for
        // images NOT yet complete at that first call (the ones still
        // waiting on loading="lazy"), so their placeholder estimate gets
        // replaced with the real size the moment it's known.
        if (!img.complete) {
            img.addEventListener('load', scheduleLayout, { once: true });
        }
    });

    // ResizeObserver rather than a window 'resize' listener: it also picks
    // up the sidebar collapse/expand transition, which changes .akd-content
    // (and so this grid)'s available width without the window itself
    // resizing at all.
    const resizeObserver = new ResizeObserver(scheduleLayout);
    resizeObserver.observe(grid);
    refresh();
}

document.addEventListener('DOMContentLoaded', initGallery);