// resources/js/modules/lightbox.js
// Sole owner of Fancybox. Declarative consumers render:
// <a href="full.jpg" data-fancybox data-caption="…"><img ...></a>
// Programmatic consumers (e.g. an avatar preview button that has no <a>)
// call openLightbox(src). Both paths share the same options object below.
import { Fancybox } from '@fancyapps/ui';
import { Compactmode } from '@fancyapps/ui/dist/fancybox/fancybox.compactmode.js';
import '@fancyapps/ui/dist/fancybox/fancybox.css';
import '@fancyapps/ui/dist/fancybox/fancybox.compactmode.css';

let initialized = false;

// Extra cleanup for cases where Fancybox's normal scroll-lock cleanup is
// interrupted, preventing the document from remaining frozen after closing.
function releaseScrollLock() {
    const html = document.documentElement;
    const body = document.body;
    html.classList.remove('fancybox-active', 'fancybox-lock', 'with-fancybox', 'compensate-for-scrollbar');
    body.classList.remove('fancybox-active', 'fancybox-lock', 'with-fancybox', 'compensate-for-scrollbar');
    html.style.removeProperty('overflow');
    html.style.removeProperty('padding-right');
    body.style.removeProperty('overflow');
    body.style.removeProperty('padding-right');
    body.style.removeProperty('position');
    body.style.removeProperty('top');
    body.style.removeProperty('left');
    body.style.removeProperty('width');
    body.style.removeProperty('height');
    html.removeAttribute('inert');
    body.removeAttribute('inert');
}

// Shared by Fancybox.bind (declarative anchors) and Fancybox.show
// (programmatic calls). Single source of truth so the two entry points
// can never drift.
const LIGHTBOX_OPTIONS = {
    plugins: { Compactmode },
    hideScrollbar: true,
    Thumbs: false,
    Slideshow: false,
    Carousel: { Navigation: false },
    Toolbar: {
        display: {
            left: [],
            middle: [],
            right: ['close'],
        },
    },
    on: {
        close: releaseScrollLock,
        destroy: releaseScrollLock,
    },
};

// Idempotent — safe to call from any consumer's init.
export function initLightbox() {
    if (initialized) return;
    initialized = true;
    Fancybox.bind('[data-fancybox]', LIGHTBOX_OPTIONS);
}

// Open a single image without a DOM trigger. Used by consumers that
// cannot be declarative anchors (e.g. the avatar preview button).
export function openLightbox(src) {
    Fancybox.show([{ src, type: 'image' }], LIGHTBOX_OPTIONS);
}

export function closeLightbox() {
    Fancybox.close();
    releaseScrollLock();
}