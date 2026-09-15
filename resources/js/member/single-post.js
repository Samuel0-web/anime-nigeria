import { useLightbox } from '../modules/lightbox';
import { success, error } from '../modules/toast';

// Keep in sync with AKD_BLOG_COMMENT_MAX_LENGTH in blog-support.php.
const COMMENT_MAX_LENGTH = 500;

// Demonstrates the error path the brief asks for without a real
// backend to fail against yet. Remove this once comment submission is
// wired to an actual endpoint.
const SIMULATED_FAILURE_RATE = 0.12;

function readCurrentUser() {
    const node = document.getElementById('akdCurrentUser');
    if (!node) return null;

    try {
        return JSON.parse(node.textContent);
    } catch (err) {
        console.error('Could not parse current user', err);
        return null;
    }
}

function buildCommentElement(user, text) {
    const article = document.createElement('article');
    article.className = 'akd-comment';

    article.innerHTML = `
        <span class="akd-comment-avatar" style="background-color: ${user.avatarColor}" aria-hidden="true">${user.initials}</span>
        <div class="akd-comment__body">
            <div class="akd-comment__meta">
                <span class="akd-comment__author"></span>
                <span class="akd-comment__dot" aria-hidden="true">&bull;</span>
                <span class="akd-comment__time">Just now</span>
            </div>
            <p class="akd-comment__text"></p>
        </div>
    `;

    article.querySelector('.akd-comment__author').textContent = user.fullname;
    article.querySelector('.akd-comment__text').textContent = text;
    return article;
}

export function initCommentComposer() {
    const form = document.querySelector('[data-comment-form]');
    if (!form) return;

    const textarea = form.querySelector('[data-comment-input]');
    const countEl = form.querySelector('[data-comment-count]');
    const errorEl = form.querySelector('[data-comment-error]');
    const submitBtn = form.querySelector('[data-comment-submit]');
    const submitLabel = form.querySelector('[data-comment-submit-label]');
    const list = document.querySelector('[data-comment-list]');
    const emptyState = document.querySelector('[data-comment-empty]');
    const user = readCurrentUser();

    if (!textarea || !list || !user) return;

    const updateCount = () => {
        countEl.textContent = `${textarea.value.length}/${COMMENT_MAX_LENGTH}`;
    };

    const showError = (message) => {
        errorEl.textContent = message;
        errorEl.hidden = false;
    };

    const clearError = () => {
        errorEl.hidden = true;
        errorEl.textContent = '';
    };

    textarea.addEventListener('input', () => {
        updateCount();
        clearError();
    });

    updateCount();

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        clearError();

        const text = textarea.value.trim();

        if (!text) {
            showError('Write something before posting.');
            return;
        }

        if (text.length > COMMENT_MAX_LENGTH) {
            showError(`Comments are limited to ${COMMENT_MAX_LENGTH} characters.`);
            return;
        }

        submitBtn.disabled = true;
        submitLabel.textContent = 'Posting...';

        window.setTimeout(() => {
            if (Math.random() < SIMULATED_FAILURE_RATE) {
                submitBtn.disabled = false;
                submitLabel.textContent = 'Post comment';
                showError('Something went wrong. Please try again.');
                return;
            }

            list.appendChild(buildCommentElement(user, text));
            emptyState?.remove();

            textarea.value = '';
            updateCount();
            submitBtn.disabled = false;
            submitLabel.textContent = 'Post comment';
            success('Comment posted');
        }, 500);
    });
}

export function initTableOfContents() {
    const toc = document.querySelector('[data-post-toc]');
    if (!toc) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    toc.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', (event) => {
            const targetId = link.getAttribute('href')?.slice(1);
            const target = targetId ? document.getElementById(targetId) : null;
            if (!target) return;

            event.preventDefault();
            target.scrollIntoView({
                behavior: prefersReducedMotion ? 'auto' : 'smooth',
                block: 'start',
            });
        });
    });
}

export function initCopyLink() {
    const button = document.querySelector('[data-copy-link]');
    if (!button) return;

    button.addEventListener('click', async () => {
        const url = button.getAttribute('data-copy-link');
        if (!url) return;

        try {
            await navigator.clipboard.writeText(url);
            success('Link copied');
        } catch (err) {
            const fallback = document.createElement('textarea');
            fallback.value = url;
            fallback.style.position = 'fixed';
            fallback.style.opacity = '0';
            document.body.appendChild(fallback);
            fallback.select();

            try {
                document.execCommand('copy');
                success('Link copied');
            } catch (fallbackErr) {
                error('Could not copy link');
            } finally {
                fallback.remove();
            }
        }
    });
}

export function initArticleLightbox() {
    const images = document.querySelectorAll('[data-post-lightbox-image]');
    if (!images.length) return;

    const lightbox = useLightbox();

    images.forEach((img) => {
        img.addEventListener('click', () => {
            lightbox.open({
                src: img.currentSrc || img.src,
                alt: img.alt,
                caption: img.getAttribute('data-lightbox-caption') || '',
                triggerEl: img,
            });
        });
    });
}