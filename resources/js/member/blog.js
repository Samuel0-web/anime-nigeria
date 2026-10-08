/** Runs onLoad/onError exactly once, including when the image settled before this ran. */
export function settleImage(img, onLoad, onError) {
    if (img.complete) {
        (img.naturalWidth > 0 ? onLoad : onError)();
        return;
    }

    img.addEventListener('load', onLoad, { once: true });
    img.addEventListener('error', onError, { once: true });
}

export function initBlogCardImages(root = document) {
    root.querySelectorAll('.akd-blog-card__media').forEach((media) => {
        if (media.dataset.imageReady === '1') return;
        const img = media.querySelector('.akd-blog-card__image');
        if (!img) return; // no image: the icon placeholder simply stays
        media.dataset.imageReady = '1';

        settleImage(
            img,
            () => media.classList.add('is-loaded'), // image fades in, icon fades out
            () => img.remove(),                      // failed: icon stays for good
        );
    });
}