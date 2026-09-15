import { useLightbox } from '../modules/lightbox';
import { success, error } from '../modules/toast';

// Keep in sync with AKD_BLOG_COMMENT_MAX_LENGTH in blog-support.php.
const COMMENT_MAX_LENGTH = 500;

// Demonstrates the error path the brief asks for without a real
// backend to fail against yet. Remove this once comment submission is
// wired to an actual endpoint.
const SIMULATED_FAILURE_RATE = 0.12;
const REPLY_INITIAL_VISIBLE = 2;
const REPLY_BATCH_SIZE = 10;

// Matches modules/lightbox.js's own MOBILE_BP, the established split
// for touch-gesture purposes in this codebase.
const MOBILE_TAP_REPLY_BP = 1024;

function autoGrowTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = `${textarea.scrollHeight}px`;
}

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

function updateCommentListScrollState() {
    const list = document.querySelector('[data-comment-list]');
    if (!list) return;

    const count = list.querySelectorAll(':scope > .akd-comment').length;
    list.classList.toggle('is-scrollable', count > 10);
}

function updateReplyExpandControl(list) {
    const toggle = list.parentElement?.querySelector('[data-reply-expand]');
    if (!toggle) return;

    const total = parseInt(list.dataset.totalReplies, 10) || 0;
    const visible = parseInt(list.dataset.visibleReplies, 10) || 0;
    const hidden = total - visible;
    const label = toggle.querySelector('[data-reply-expand-label]');
    const icon = toggle.querySelector('i');

    if (hidden <= 0) {
        if (label) label.textContent = 'Hide replies';
        if (icon) icon.className = 'fa-solid fa-chevron-up';
        toggle.setAttribute('aria-expanded', 'true');
    } else {
        if (label) label.textContent = `View ${hidden} ${hidden === 1 ? 'reply' : 'replies'}`;
        if (icon) icon.className = 'fa-solid fa-chevron-down';
        toggle.setAttribute('aria-expanded', 'false');
    }
}

function setReplyVisibility(list, visibleCount) {
    const total = parseInt(list.dataset.totalReplies, 10) || 0;
    const clamped = Math.max(0, Math.min(visibleCount, total));
    list.dataset.visibleReplies = String(clamped);

    list.querySelectorAll('[data-reply-index]').forEach((reply) => {
        const index = parseInt(reply.dataset.replyIndex, 10);
        reply.classList.toggle('is-hidden-reply', index >= clamped);
    });
}

export function initReplyExpansion() {
    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-reply-expand]');
        if (!toggle) return;

        const list = toggle.parentElement?.querySelector('[data-reply-list]');
        if (!list) return;

        const total = parseInt(list.dataset.totalReplies, 10) || 0;
        const visible = parseInt(list.dataset.visibleReplies, 10) || 0;

        setReplyVisibility(list, visible >= total ? REPLY_INITIAL_VISIBLE : visible + REPLY_BATCH_SIZE);
        updateReplyExpandControl(list);
    });
}

function openReplyComposer(replyBtn) {
    const body = replyBtn.closest('.akd-comment__body');
    const composer = body?.querySelector('[data-reply-composer]');
    if (!composer) return;

    const willOpen = composer.hidden;

    document.querySelectorAll('[data-reply-composer]:not([hidden])').forEach((open) => {
        if (open !== composer) open.hidden = true;
    });

    composer.hidden = !willOpen;
    if (!willOpen) return;

    const name = replyBtn.dataset.replyName || 'this comment';
    const label = composer.querySelector('[data-reply-label]');
    const textarea = composer.querySelector('[data-reply-input]');
    const avatar = composer.querySelector('[data-reply-avatar]');
    const user = readCurrentUser();

    if (label) label.textContent = `Reply to ${name}`;
    if (textarea) textarea.setAttribute('placeholder', `Reply to ${name}...`);

    if (avatar && user) {
        avatar.style.backgroundColor = user.avatarColor;
        avatar.textContent = user.initials;
    }

    textarea?.focus();
}

function closeReplyComposer(composer) {
    composer.hidden = true;

    const textarea = composer.querySelector('[data-reply-input]');
    if (textarea) {
        textarea.value = '';
        autoGrowTextarea(textarea);
    }

    const errorEl = composer.querySelector('[data-reply-error]');
    if (errorEl) {
        errorEl.hidden = true;
        errorEl.textContent = '';
    }

    const countEl = composer.querySelector('[data-reply-count]');
    if (countEl) countEl.textContent = `0/${COMMENT_MAX_LENGTH}`;
}

function ensureReplyList(commentArticle, composer) {
    let list = commentArticle.querySelector('[data-reply-list]');

    if (!list) {
        list = document.createElement('div');
        list.className = 'akd-comment-replies';
        list.setAttribute('data-reply-list', '');
        list.dataset.totalReplies = '0';
        list.dataset.visibleReplies = '0';
        composer.insertAdjacentElement('afterend', list);
    }

    return list;
}

function ensureReplyToggle(list) {
    let toggle = list.parentElement?.querySelector('[data-reply-expand]');
    const total = parseInt(list.dataset.totalReplies, 10) || 0;

    if (!toggle && total > 2) {
        toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'akd-comment-replies__toggle';
        toggle.setAttribute('data-reply-expand', '');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.innerHTML = '<i class="fa-solid fa-chevron-down" aria-hidden="true"></i><span data-reply-expand-label></span>';
        list.insertAdjacentElement('afterend', toggle);
    }

    return toggle;
}

function submitReply(submitBtn) {
    const composer = submitBtn.closest('[data-reply-composer]');
    const commentArticle = composer?.closest('.akd-comment');
    const textarea = composer?.querySelector('[data-reply-input]');
    const errorEl = composer?.querySelector('[data-reply-error]');
    const submitLabel = composer?.querySelector('[data-reply-submit-label]');
    const user = readCurrentUser();

    if (!composer || !commentArticle || !textarea || !user) return;

    const text = textarea.value.trim();
    if (errorEl) {
        errorEl.hidden = true;
        errorEl.textContent = '';
    }

    if (!text) {
        if (errorEl) {
            errorEl.textContent = 'Write something before replying.';
            errorEl.hidden = false;
        }
        return;
    }

    if (text.length > COMMENT_MAX_LENGTH) {
        if (errorEl) {
            errorEl.textContent = `Replies are limited to ${COMMENT_MAX_LENGTH} characters.`;
            errorEl.hidden = false;
        }
        return;
    }

    submitBtn.disabled = true;
    if (submitLabel) submitLabel.textContent = 'Posting...';

    window.setTimeout(() => {
        submitBtn.disabled = false;
        if (submitLabel) submitLabel.textContent = 'Reply';

        const list = ensureReplyList(commentArticle, composer);
        const total = parseInt(list.dataset.totalReplies, 10) || 0;

        const reply = document.createElement('article');
        reply.className = 'akd-comment akd-comment--reply';
        reply.setAttribute('data-reply-index', String(total));
        reply.innerHTML = `
            <span class="akd-comment-avatar akd-comment-avatar--sm" style="background-color: ${user.avatarColor}" aria-hidden="true">${user.initials}</span>
            <div class="akd-comment__body">
                <div class="akd-comment__meta">
                    <span class="akd-comment__author"></span>
                </div>
                <p class="akd-comment__text"></p>
                <span class="akd-comment__time">Just now</span>
            </div>
        `;
        reply.querySelector('.akd-comment__author').textContent = user.fullname;
        reply.querySelector('.akd-comment__text').textContent = text;
        list.appendChild(reply);

        list.dataset.totalReplies = String(total + 1);

        const toggle = ensureReplyToggle(list);
        const visible = parseInt(list.dataset.visibleReplies, 10) || 0;
        setReplyVisibility(list, visible + 1);
        if (toggle) updateReplyExpandControl(list);

        closeReplyComposer(composer);
        success('Reply posted');
    }, 500);
}

export function initReplyComposers() {
    document.addEventListener('click', (event) => {
        const replyBtn = event.target.closest('[data-reply-toggle]');
        if (replyBtn) {
            openReplyComposer(replyBtn);
            return;
        }

        const cancelBtn = event.target.closest('[data-reply-cancel]');
        if (cancelBtn) {
            const composer = cancelBtn.closest('[data-reply-composer]');
            if (composer) closeReplyComposer(composer);
            return;
        }

        const submitBtn = event.target.closest('[data-reply-submit]');
        if (submitBtn) submitReply(submitBtn);
    });

    document.addEventListener('input', (event) => {
        const textarea = event.target.closest('[data-reply-input]');
        if (!textarea) return;

        autoGrowTextarea(textarea);

        const composer = textarea.closest('[data-reply-composer]');
        const countEl = composer?.querySelector('[data-reply-count]');
        if (countEl) countEl.textContent = `${textarea.value.length}/${COMMENT_MAX_LENGTH}`;

        const errorEl = composer?.querySelector('[data-reply-error]');
        if (errorEl) {
            errorEl.hidden = true;
            errorEl.textContent = '';
        }
    });
}

export function initMobileCommentReply() {
    document.addEventListener('click', (event) => {
        if (window.innerWidth >= MOBILE_TAP_REPLY_BP) return;

        const target = event.target.closest('[data-comment-tap-target]');
        if (!target) return;

        if (event.target.closest('a, button, textarea, input, [data-reply-list], [data-reply-composer]')) return;

        target.querySelector('[data-reply-toggle]')?.click();
    });
}

function buildCommentElement(user, text) {
    const article = document.createElement('article');
    article.className = 'akd-comment';

    article.innerHTML = `
        <span class="akd-comment-avatar" style="background-color: ${user.avatarColor}" aria-hidden="true">${user.initials}</span>
        <div class="akd-comment__body" data-comment-tap-target>
            <div class="akd-comment__meta">
                <span class="akd-comment__author"></span>
                <span class="akd-comment__username"></span>
            </div>
            <p class="akd-comment__text"></p>
            <div class="akd-comment__actions">
                <span class="akd-comment__time">Just now</span>
            </div>
        </div>
    `;

    article.querySelector('.akd-comment__author').textContent = user.fullname;
    article.querySelector('.akd-comment__username').textContent = `@${user.username || ''}`;
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
        autoGrowTextarea(textarea);
    });

    updateCount();
    autoGrowTextarea(textarea);

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
            updateCommentListScrollState();

            textarea.value = '';
            updateCount();
            autoGrowTextarea(textarea);
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

    const icon = button.querySelector('i');
    const originalIconClass = icon ? icon.className : '';
    let resetTimer = null;

    const showCopiedState = () => {
        if (!icon) return;

        button.classList.add('is-copied');
        button.disabled = true;
        icon.className = 'fa-solid fa-check';

        clearTimeout(resetTimer);
        resetTimer = window.setTimeout(() => {
            icon.className = originalIconClass;
            button.classList.remove('is-copied');
            button.disabled = false;
        }, 2500);
    };

    button.addEventListener('click', async () => {
        const url = button.getAttribute('data-copy-link');
        if (!url) return;

        try {
            await navigator.clipboard.writeText(url);
            success('Link copied');
            showCopiedState();
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
                showCopiedState();
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