import { initLightbox } from '../modules/lightbox';
import { useModal } from '../modules/modal';
import { useConfirmDialog } from '../modules/confirm-dialog';
import { success, error } from '../modules/toast';

const COMMENT_MAX_LENGTH = 500;
const SIMULATED_FAILURE_RATE = 0.12;
const REPLY_INITIAL_VISIBLE = 2;
const REPLY_BATCH_SIZE = 10;
const LONG_PRESS_MS = 2500;
const MOBILE_BP = 1024;
const TAP_EXCLUDE_SELECTOR = 'a, button, textarea, input, [data-reply-list], [data-reply-composer], .akd-comment__profile-link';
let longPressTimer = null;
let longPressTriggered = false;
let localIdCounter = 900000;

function nextLocalId() {
    return localIdCounter++;
}

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
    list.classList.toggle('is-scrollable', list.scrollHeight > list.clientHeight);
}

function checkCommentListEmpty() {
    const list = document.querySelector('[data-comment-list]');
    if (!list) return;
    if (list.querySelector('.akd-comment-thread')) return;
    if (list.querySelector('[data-comment-empty]')) return;

    const empty = document.createElement('div');
    empty.className = 'akd-blog-empty akd-comment-empty';
    empty.setAttribute('data-comment-empty', '');
    empty.innerHTML = `
        <span class="akd-comment-empty__icon" aria-hidden="true"><i class="fa-regular fa-comments"></i></span>
        <p class="akd-blog-empty__title">No comments yet.</p>
        <p class="akd-blog-empty__body">Be the first to share your thoughts.</p>
    `;
    list.appendChild(empty);
}

function updateCommentCounts() {
    const total = document.querySelectorAll('.akd-comment-thread, .akd-comment--reply').length;

    const triggerLabel = document.querySelector('[data-comments-trigger-count]');
    if (triggerLabel) triggerLabel.textContent = total;

    const headingCount = document.querySelector('[data-comments-heading-count]');
    if (headingCount) headingCount.textContent = `\u00b7 ${total}`;
}

function computeCommentUnitHeight(list) {
    const first = list.querySelector(':scope > .akd-comment-thread');
    const body = first?.querySelector(':scope > .akd-comment__body');
    const meta = body?.querySelector(':scope > .akd-comment__meta');
    const text = body?.querySelector(':scope > .akd-comment__text');
    const actions = body?.querySelector(':scope > .akd-comment__actions');
    if (!meta || !text || !actions) return null;

    const rowGap = parseFloat(getComputedStyle(list).rowGap) || 0;
    const baseHeight = meta.getBoundingClientRect().height
        + text.getBoundingClientRect().height
        + actions.getBoundingClientRect().height;

    const sampleReply = body.querySelector('.akd-comment--reply:not(.is-hidden-reply)');
    const replyHeight = sampleReply ? sampleReply.getBoundingClientRect().height : baseHeight * 0.85;
    return baseHeight + rowGap + (replyHeight + rowGap) * 2;
}

export function initCommentListHeight() {
    const list = document.querySelector('[data-comment-list]');
    if (!list || window.innerWidth < MOBILE_BP) return;

    requestAnimationFrame(() => {
        const unitHeight = computeCommentUnitHeight(list);
        if (!unitHeight) return;
        list.style.maxHeight = `${Math.round(unitHeight * 10)}px`;
        updateCommentListScrollState();
    });
}

export function initMobileCommentsSheet() {
    const trigger = document.querySelector('[data-comments-trigger]');
    const panel = document.getElementById('comments');
    if (!trigger || !panel) return;
    const anchor = document.createComment('comments-anchor');
    panel.parentElement.insertBefore(anchor, panel);

    trigger.addEventListener('click', () => {
        const modal = useModal();
        panel.classList.add('akd-post-comments--in-modal');

        modal.open({
            title: 'Comments',
            content: panel,
            size: 'lg',
            className: 'akd-comments-sheet',
            beforeClose: () => {
                anchor.parentNode.insertBefore(panel, anchor.nextSibling);
                panel.classList.remove('akd-post-comments--in-modal');
            },
        });
    });
}

// ---- Reply reveal/hide animation ----

function animateReplyVisibility(reply, shouldShow) {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        reply.classList.toggle('is-hidden-reply', !shouldShow);
        reply.style.maxHeight = '';
        return;
    }

    if (shouldShow) {
        reply.classList.remove('is-hidden-reply');
        const targetHeight = reply.scrollHeight;
        reply.style.maxHeight = '0px';
        
        requestAnimationFrame(() => {
            reply.style.maxHeight = `${targetHeight}px`;
        });

        reply.addEventListener('transitionend', function handler(event) {
            if (event.propertyName !== 'max-height') return;
            reply.style.maxHeight = '';
            reply.removeEventListener('transitionend', handler);
        });
    } else {
        const currentHeight = reply.scrollHeight;
        reply.style.maxHeight = `${currentHeight}px`;
        reply.classList.add('is-hidden-reply');

        requestAnimationFrame(() => {
            reply.style.maxHeight = '0px';
        });

        reply.addEventListener('transitionend', function handler(event) {
            if (event.propertyName !== 'max-height') return;
            reply.style.maxHeight = '';
            reply.removeEventListener('transitionend', handler);
        });
    }
}

function setReplyVisibility(list, visibleCount) {
    const total = parseInt(list.dataset.totalReplies, 10) || 0;
    const clamped = Math.max(0, Math.min(visibleCount, total));
    list.dataset.visibleReplies = String(clamped);

    list.querySelectorAll('[data-reply-index]').forEach((reply) => {
        const index = parseInt(reply.dataset.replyIndex, 10);
        const shouldShow = index < clamped;
        const isCurrentlyHidden = reply.classList.contains('is-hidden-reply');

        if (shouldShow === isCurrentlyHidden) {
            animateReplyVisibility(reply, shouldShow);
        }
    });
}

function updateReplyExpandControl(list) {
    const controls = list.parentElement?.querySelector('[data-reply-controls]');
    if (!controls) return;

    const viewBtn = controls.querySelector('[data-reply-view]');
    const viewLabel = viewBtn?.querySelector('[data-reply-view-label]');
    const viewIcon = viewBtn?.querySelector('i');
    const hideBtn = controls.querySelector('[data-reply-hide]');

    const total = parseInt(list.dataset.totalReplies, 10) || 0;
    const visible = parseInt(list.dataset.visibleReplies, 10) || 0;
    const hidden = total - visible;
    const hasExpandedAtLeastOnce = visible > REPLY_INITIAL_VISIBLE;

    controls.hidden = total <= 2;

    if (hidden <= 0) {
        if (viewLabel) viewLabel.textContent = 'Hide all replies';
        if (viewIcon) viewIcon.className = 'fa-solid fa-chevron-up';
        viewBtn?.setAttribute('aria-expanded', 'true');
        if (hideBtn) hideBtn.hidden = true;
    } else {
        if (viewLabel) viewLabel.textContent = `View ${hidden} ${hidden === 1 ? 'reply' : 'replies'}`;
        if (viewIcon) viewIcon.className = 'fa-solid fa-chevron-down';
        viewBtn?.setAttribute('aria-expanded', 'false');
        if (hideBtn) hideBtn.hidden = !hasExpandedAtLeastOnce;
    }
}

export function initReplyExpansion() {
    document.addEventListener('click', (event) => {
        const viewBtn = event.target.closest('[data-reply-view]');
        if (viewBtn) {
            const body = viewBtn.closest('.akd-comment__body');
            const list = body?.querySelector('[data-reply-list]');
            if (!list) return;
            const total = parseInt(list.dataset.totalReplies, 10) || 0;
            const visible = parseInt(list.dataset.visibleReplies, 10) || 0;
            setReplyVisibility(list, visible >= total ? REPLY_INITIAL_VISIBLE : visible + REPLY_BATCH_SIZE);
            updateReplyExpandControl(list);
            return;
        }

        const hideBtn = event.target.closest('[data-reply-hide]');
        if (hideBtn) {
            const body = hideBtn.closest('.akd-comment__body');
            const list = body?.querySelector('[data-reply-list]');
            if (!list) return;
            setReplyVisibility(list, REPLY_INITIAL_VISIBLE);
            updateReplyExpandControl(list);
        }
    });
}

// ---- Reply composer ----

function openReplyComposer(replyBtn) {
    const thread = replyBtn.closest('.akd-comment-thread');
    const composer = thread?.querySelector('[data-reply-composer]');
    if (!composer) return;
    const targetEl = replyBtn.closest('.akd-comment');
    const targetUsername = replyBtn.dataset.replyUsername || '';
    const targetName = replyBtn.dataset.replyName || 'this comment';
    const targetId = targetEl?.dataset.commentId || '';
    const isTopLevelTarget = targetEl === thread;
    const sameTargetOpen = !composer.hidden && composer.dataset.replyToId === targetId;

    document.querySelectorAll('[data-reply-composer]:not([hidden])').forEach((open) => {
        if (open !== composer) open.hidden = true;
    });

    if (sameTargetOpen) {
        composer.hidden = true;
        return;
    }

    composer.hidden = false;
    composer.dataset.replyToUsername = targetUsername;
    composer.dataset.replyToId = targetId;
    composer.dataset.replyToIsTopLevel = isTopLevelTarget ? '1' : '0';
    const label = composer.querySelector('[data-reply-label]');
    const textarea = composer.querySelector('[data-reply-input]');
    const avatar = composer.querySelector('[data-reply-avatar]');
    const user = readCurrentUser();

    if (label) label.textContent = `Reply to ${targetName}`;
    if (textarea) textarea.setAttribute('placeholder', `Reply to ${targetName}...`);

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

function ensureReplyList(thread, composer) {
    let list = thread.querySelector('[data-reply-list]');

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

function ensureReplyControls(list) {
    let controls = list.parentElement?.querySelector('[data-reply-controls]');

    if (!controls) {
        controls = document.createElement('div');
        controls.className = 'akd-comment-replies__controls';
        controls.setAttribute('data-reply-controls', '');
        controls.innerHTML = `
            <button type="button" class="akd-comment-replies__toggle" data-reply-view aria-expanded="false">
                <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                <span data-reply-view-label></span>
            </button>
            <button type="button" class="akd-comment-replies__hide" data-reply-hide hidden>Hide</button>
        `;
        list.insertAdjacentElement('afterend', controls);
    }

    return controls;
}

function buildReplyElement(user, text, thread, replyToUsername, replyToId) {
    const profileHref = `/member/player/${encodeURIComponent(user.username || '')}`;
    const reply = document.createElement('article');
    reply.className = 'akd-comment akd-comment--reply is-hidden-reply';
    reply.dataset.commentId = String(nextLocalId());
    reply.dataset.parentId = thread.dataset.commentId || '';
    reply.dataset.replyToUsername = replyToUsername || '';
    reply.dataset.replyToId = replyToId || '';
    reply.dataset.replyIndex = '0';

    reply.innerHTML = `
        <span class="akd-comment-avatar akd-comment-avatar--sm" style="background-color: ${user.avatarColor}" aria-hidden="true">${user.initials}</span>
        <div class="akd-comment__body">
            <div class="akd-comment__meta">
                <span class="akd-comment__profile-link akd-comment__profile-link--self">
                    <span class="akd-comment__author"></span>
                    <span class="akd-comment__username"></span>
                </span>
                ${replyToUsername ? `<span class="akd-comment__reply-target"><i class="fa-solid fa-caret-right" aria-hidden="true"></i> @${replyToUsername}</span>` : ''}
            </div>
            <p class="akd-comment__text"></p>
            <div class="akd-comment__actions">
                <span class="akd-comment__time">Just now</span>
                <span class="akd-comment__dot" aria-hidden="true">&middot;</span>
                <button type="button" class="akd-comment__reply-btn" data-reply-toggle data-reply-name="${user.fullname}" data-reply-username="${user.username || ''}">Reply</button>
                <span class="akd-comment__dot" aria-hidden="true">&middot;</span>
                <button type="button" class="akd-comment__delete-btn" data-comment-delete data-delete-target="reply">Delete</button>
            </div>
        </div>
    `;

    reply.querySelector('.akd-comment__author').textContent = user.fullname;
    reply.querySelector('.akd-comment__username').textContent = `@${user.username || ''}`;
    reply.querySelector('.akd-comment__text').textContent = text;
    return reply;
}

function findReplyInsertionAnchor(list, targetId) {
    const items = Array.from(list.querySelectorAll(':scope > [data-comment-id]'));
    const targetIndex = items.findIndex((el) => el.dataset.commentId === targetId);
    if (targetIndex === -1) return null;

    // A target's descendants sit contiguously right after it in this
    // flat list (the PHP renderer builds it depth-first), so walking
    // forward until an item's reply-to-id falls outside the growing
    // descendant set finds exactly where that target's chain ends.
    const descendantIds = new Set([targetId]);
    let anchor = items[targetIndex];

    for (let i = targetIndex + 1; i < items.length; i++) {
        const el = items[i];
        const parentRef = el.dataset.replyToId || '';
        if (parentRef && descendantIds.has(parentRef)) {
            descendantIds.add(el.dataset.commentId);
            anchor = el;
        } else {
            break;
        }
    }

    return anchor;
}

function reindexReplyList(list) {
    const items = Array.from(list.querySelectorAll(':scope > [data-comment-id]'));

    items.forEach((el, index) => {
        el.dataset.replyIndex = String(index);
    });

    return items.length;
}

function submitReply(submitBtn) {
    const composer = submitBtn.closest('[data-reply-composer]');
    const thread = composer?.closest('.akd-comment-thread');
    const textarea = composer?.querySelector('[data-reply-input]');
    const errorEl = composer?.querySelector('[data-reply-error]');
    const submitLabel = composer?.querySelector('[data-reply-submit-label]');
    const user = readCurrentUser();
    if (!composer || !thread || !textarea || !user) return;

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
        const list = ensureReplyList(thread, composer);
        const replyToIsTopLevel = composer.dataset.replyToIsTopLevel === '1';
        const replyToId = composer.dataset.replyToId || '';
        const replyToUsername = replyToIsTopLevel ? null : composer.dataset.replyToUsername;
        const reply = buildReplyElement(user, text, thread, replyToUsername, replyToId);
        const anchor = replyToIsTopLevel ? null : findReplyInsertionAnchor(list, replyToId);

        if (anchor) {
            anchor.classList.add('akd-comment--nested-reply');
            anchor.insertAdjacentElement('afterend', reply);
        } else {
            list.appendChild(reply);
        }

        const previousVisible = parseInt(list.dataset.visibleReplies, 10) || 0;
        list.dataset.totalReplies = String(reindexReplyList(list));
        ensureReplyControls(list);
        const newIndex = parseInt(reply.dataset.replyIndex, 10);
        setReplyVisibility(list, Math.max(previousVisible + 1, newIndex + 1));
        updateReplyExpandControl(list);
        closeReplyComposer(composer);
        updateCommentCounts();
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

// ---- Mobile tap-to-reply / long-press-to-delete ----

export function initMobileCommentReply() {
    document.addEventListener('click', (event) => {
        if (window.innerWidth >= MOBILE_BP) return;
        if (longPressTriggered) {
            longPressTriggered = false;
            return;
        }

        const target = event.target.closest('[data-comment-tap-target]');
        if (!target) return;
        if (event.target.closest(TAP_EXCLUDE_SELECTOR)) return;
        target.querySelector('[data-reply-toggle]')?.click();
    });
}

export function initMobileCommentLongPressDelete() {
    document.addEventListener('touchstart', (event) => {
        if (window.innerWidth >= MOBILE_BP) return;
        const target = event.target.closest('[data-comment-tap-target]');
        if (!target) return;
        if (event.target.closest(TAP_EXCLUDE_SELECTOR)) return;

        longPressTriggered = false;
        longPressTimer = window.setTimeout(() => {
            longPressTriggered = true;
            target.closest('.akd-comment')?.querySelector('[data-comment-delete]')?.click();
        }, LONG_PRESS_MS);
    }, { passive: true });

    document.addEventListener('touchend', () => clearTimeout(longPressTimer), { passive: true });
    document.addEventListener('touchmove', () => clearTimeout(longPressTimer), { passive: true });
}

// ---- Delete ----

export function initCommentDeletion() {
    document.addEventListener('click', async (event) => {
        const deleteBtn = event.target.closest('[data-comment-delete]');
        if (!deleteBtn) return;
        const commentArticle = deleteBtn.closest('.akd-comment');
        if (!commentArticle) return;
        const isReply = deleteBtn.dataset.deleteTarget === 'reply';

        const confirmed = await useConfirmDialog().ask({
            title: isReply ? 'Delete reply' : 'Delete comment',
            message: `This will remove the ${isReply ? 'reply' : 'comment'}. This cannot be undone.`,
            confirmLabel: 'Delete',
            cancelLabel: 'Cancel',
            destructive: true,
        });

        if (!confirmed) return;
        const parentList = commentArticle.closest('[data-reply-list]');
        commentArticle.remove();

        if (parentList) {
            const visible = parseInt(parentList.dataset.visibleReplies, 10) || 0;
            const remaining = reindexReplyList(parentList);
            parentList.dataset.totalReplies = String(remaining);
            setReplyVisibility(parentList, Math.min(visible, remaining));
            updateReplyExpandControl(parentList);

            parentList.querySelectorAll('.akd-comment--nested-reply').forEach((anchor) => {
                const id = anchor.dataset.commentId;
                const stillPointed = parentList.querySelector(`[data-reply-to-id="${id}"]`);
                if (!stillPointed) anchor.classList.remove('akd-comment--nested-reply');
            });
        } else {
            checkCommentListEmpty();
        }

        updateCommentListScrollState();
        updateCommentCounts();
    });
}

// ---- Top-level comment composer ----

function buildCommentElement(user, text) {
    const article = document.createElement('article');
    article.className = 'akd-comment akd-comment-thread';
    article.dataset.commentId = String(nextLocalId());
    const profileHref = `/member/player/${encodeURIComponent(user.username || '')}`;

    article.innerHTML = `
        <span class="akd-comment-avatar" style="background-color: ${user.avatarColor}" aria-hidden="true">${user.initials}</span>
        <div class="akd-comment__body" data-comment-tap-target>
            <div class="akd-comment__meta">
                <span class="akd-comment__profile-link akd-comment__profile-link--self">
                    <span class="akd-comment__author"></span>
                    <span class="akd-comment__username"></span>
                </span>
            </div>
            <p class="akd-comment__text"></p>
            <div class="akd-comment__actions">
                <span class="akd-comment__time">Just now</span>
                <span class="akd-comment__dot" aria-hidden="true">&middot;</span>
                <button type="button" class="akd-comment__reply-btn" data-reply-toggle data-reply-name="${user.fullname}" data-reply-username="${user.username || ''}">Reply</button>
                <span class="akd-comment__dot" aria-hidden="true">&middot;</span>
                <button type="button" class="akd-comment__delete-btn" data-comment-delete data-delete-target="comment">Delete</button>
            </div>

            <div class="akd-reply-composer" data-reply-composer hidden>
                <div class="akd-comment-composer__row">
                    <span class="akd-comment-avatar akd-comment-avatar--sm" data-reply-avatar aria-hidden="true"></span>
                    <div class="akd-comment-composer__field">
                        <label class="visually-hidden" data-reply-label>Reply</label>
                        <textarea class="akd-comment-composer__textarea akd-reply-composer__textarea" data-reply-input maxlength="${COMMENT_MAX_LENGTH}" placeholder="Write a reply..." rows="1"></textarea>
                        <div class="akd-comment-composer__meta">
                            <span class="akd-comment-composer__error" data-reply-error hidden></span>
                            <span class="akd-comment-composer__count" data-reply-count>0/${COMMENT_MAX_LENGTH}</span>
                        </div>
                    </div>
                </div>
                <div class="akd-comment-composer__actions">
                    <button type="button" class="akd-comment-composer__cancel" data-reply-cancel>Cancel</button>
                    <button type="button" class="akd-comment-composer__submit" data-reply-submit>
                        <span data-reply-submit-label>Reply</span>
                    </button>
                </div>
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

            document.querySelector('[data-comment-empty]')?.remove();
            list.prepend(buildCommentElement(user, text));
            updateCommentListScrollState();
            updateCommentCounts();
            textarea.value = '';
            updateCount();
            autoGrowTextarea(textarea);
            submitBtn.disabled = false;
            submitLabel.textContent = 'Post comment';
            success('Comment posted');
        }, 500);
    });
}

// ---- Table of contents, copy link, lightbox (unchanged) ----

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
    initLightbox();
}