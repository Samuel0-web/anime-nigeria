// resources/js/modules/lightbox.js
// Sole owner of Fancybox. Consumers render:
// <a href="full.jpg" data-fancybox data-caption="…"><img ...></a>
// A delegated bind covers all current and future consumers. No attribute
// value means each image is a single-item set.
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

export function initLightbox() {
    if (initialized) return;
    initialized = true;

    Fancybox.bind('[data-fancybox]', {
        plugins: { Compactmode },

        // Keep the scrollbar visible while the viewer is open.
        hideScrollbar: true,

        // Single-image viewer with no thumbnails, slideshow, navigation,
        // or toolbar items other than close.
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
    });
}

export function closeLightbox() {
    Fancybox.close();
    // Ensure cleanup even if the instance is already mid-teardown.
    releaseScrollLock();
}