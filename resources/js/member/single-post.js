import { initLightbox } from '../modules/lightbox';
import { useModal } from '../modules/modal';
import { useConfirmDialog } from '../modules/confirm-dialog';
import { success, error } from '../modules/toast';
import { api } from '../modules/api';
import { optimistic } from '../modules/optimistic';
import { settleImage } from './blog';

const API = '/member/api/blog/comments';
const COMMENT_MAX_LENGTH = 500;
const REPLY_INITIAL_VISIBLE = 2;
const REPLY_BATCH_SIZE = 10;
const LONG_PRESS_MS = 2500;
const MOBILE_BP = 1024;
const TAP_EXCLUDE_SELECTOR = 'a, button, textarea, input, [data-reply-list], [data-reply-composer], .akd-comment__profile-link';
let longPressTimer = null;
let longPressTriggered = false;
let tempCounter = 0;
let lastReplySubmit = 0;

// ---- Small helpers ----
const nextTempId = () => `tmp-${++tempCounter}`;
const isTemp = (id) => String(id).startsWith('tmp-');
const num = (value) => Number(value) || 0;
const sameUser = (a, b) => Boolean(a) && Boolean(b) && String(a).toLowerCase() === String(b).toLowerCase();
const safeColor = (value) => (/^#[0-9a-f]{6}$/i.test(value || '') ? value : '#4A4E69');

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

function relativeTime(iso) {
    const time = Date.parse(iso || '');
    if (Number.isNaN(time)) return 'Just now';
    const diff = Math.max(0, Math.floor((Date.now() - time) / 1000));
    if (diff < 60) return 'Just now';
    if (diff < 3600) { const m = Math.floor(diff / 60); return `${m} minute${m === 1 ? '' : 's'} ago`; }
    if (diff < 86400) { const h = Math.floor(diff / 3600); return `${h} hour${h === 1 ? '' : 's'} ago`; }
    if (diff < 2592000) { const d = Math.floor(diff / 86400); return `${d} day${d === 1 ? '' : 's'} ago`; }
    return new Date(time).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

/** Plain text with line breaks, matching what nl2br(htmlspecialchars()) renders on the server. */
function setMultiline(element, text) {
    element.replaceChildren();
    String(text).split('\n').forEach((line, index) => {
        if (index) element.appendChild(document.createElement('br'));
        element.appendChild(document.createTextNode(line));
    });
}

function failureText(e) {
    if (e?.status === 0) return "You're offline. Check your connection and try again.";
    const first = e?.errors ? Object.values(e.errors)[0] : null;
    if (typeof first === 'string') return first;
    const message = e?.data?.message || (e?.code === 'INVALID_JSON' ? '' : e?.message) || '';
    return message || 'Something went wrong. Please try again.';
}

// ---- Counts (the server owns the number; the page adjusts it optimistically) ----

const commentsEl = () => document.getElementById('comments');
const readTotal = () => num(commentsEl()?.dataset.total);
const articleId = () => commentsEl()?.dataset.article || '';

function setCommentTotal(value) {
    const total = Math.max(0, num(value));
    const root = commentsEl();
    if (root) root.dataset.total = String(total);

    const triggerLabel = document.querySelector('[data-comments-trigger-count]');
    if (triggerLabel) triggerLabel.textContent = total;

    const headingCount = document.querySelector('[data-comments-heading-count]');
    if (headingCount) headingCount.textContent = `\u00b7 ${total}`;
}

const adjustTotal = (delta) => setCommentTotal(readTotal() + delta);

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

// ---- Building elements from comment data (the server's JSON shape, or an optimistic stand-in) ----

/** Fills (or refreshes) a comment element. Used for new elements and for pending ones after the server answers. */
function populate(element, data, current, { pending = false } = {}) {
    const own = sameUser(data.username, current?.username);
    const isReply = element.classList.contains('akd-comment--reply');
    element.dataset.commentId = String(data.id);

    if (isReply) {
        element.dataset.parentId = data.parent_id == null ? '' : String(data.parent_id);
        element.dataset.replyToUsername = data.reply_to_username || '';
        element.dataset.replyToId = data.reply_to_id == null ? '' : String(data.reply_to_id);
    }

    const avatar = element.querySelector(':scope > [data-avatar]');
    // Your own avatar always uses the header's values, so it never changes after posting.
    avatar.style.backgroundColor = safeColor(own ? current.avatarColor : data.avatar_color);
    avatar.textContent = own ? current.initials : (data.initials || '');
    if (!own) avatar.setAttribute('href', `/member/player/${encodeURIComponent(data.username)}`);
    const profile = element.querySelector('[data-profile]');
    if (!own) profile.setAttribute('href', `/member/player/${encodeURIComponent(data.username)}`);
    element.querySelector('.akd-comment__author').textContent = data.fullname;
    element.querySelector('.akd-comment__username').textContent = `@${data.username}`;
    setMultiline(element.querySelector('.akd-comment__text'), data.content);
    const timeEl = element.querySelector('.akd-comment__time');
    timeEl.textContent = pending ? 'Posting...' : relativeTime(data.created_at);
    if (pending) timeEl.removeAttribute('data-time');
    else timeEl.dataset.time = data.created_at;
    const replyBtn = element.querySelector('[data-reply-toggle]');
    replyBtn.dataset.replyName = data.fullname;
    replyBtn.dataset.replyUsername = data.username;

    if (isReply) {
        element.querySelector('.akd-comment__reply-target')?.remove();

        if (data.reply_to_username) {
            const target = document.createElement('span');
            target.className = 'akd-comment__reply-target';
            target.innerHTML = '<i class="fa-solid fa-caret-right" aria-hidden="true"></i> ';
            target.appendChild(document.createTextNode(`@${data.reply_to_username}`));
            element.querySelector('.akd-comment__meta').appendChild(target);
        }
    }
}

function identityMarkup(own, small) {
    const size = small ? ' akd-comment-avatar--sm' : '';

    return {
        avatar: own
            ? `<span class="akd-comment-avatar${size}" data-avatar aria-hidden="true"></span>`
            : `<a class="akd-comment-avatar${size}" data-avatar aria-hidden="true"></a>`,
        open: own
            ? '<span class="akd-comment__profile-link akd-comment__profile-link--self" data-profile>'
            : '<a class="akd-comment__profile-link" data-profile>',
        close: own ? '</span>' : '</a>',
    };
}

function actionsMarkup(own, target) {
    return `
        <div class="akd-comment__actions">
            <span class="akd-comment__time"></span>
            <span class="akd-comment__dot" aria-hidden="true">&middot;</span>
            <button type="button" class="akd-comment__reply-btn" data-reply-toggle>Reply</button>
            ${own ? `<span class="akd-comment__dot" aria-hidden="true">&middot;</span>
            <button type="button" class="akd-comment__delete-btn" data-comment-delete data-delete-target="${target}">Delete</button>` : ''}
        </div>`;
}

function buildReplyElement(data, current, { pending = false } = {}) {
    const own = sameUser(data.username, current?.username);
    const ui = identityMarkup(own, true);
    const reply = document.createElement('article');
    reply.className = `akd-comment akd-comment--reply is-hidden-reply${pending ? ' is-pending' : ''}`;
    reply.innerHTML = `
        ${ui.avatar}
        <div class="akd-comment__body">
            <div class="akd-comment__meta">
                ${ui.open}<span class="akd-comment__author"></span><span class="akd-comment__username"></span>${ui.close}
            </div>
            <p class="akd-comment__text"></p>
            ${actionsMarkup(own, 'reply')}
        </div>`;
    populate(reply, data, current, { pending });
    return reply;
}

function buildCommentElement(data, current, { pending = false } = {}) {
    const own = sameUser(data.username, current?.username);
    const ui = identityMarkup(own, false);
    const article = document.createElement('article');
    article.className = `akd-comment akd-comment-thread${pending ? ' is-pending' : ''}`;
    article.innerHTML = `
        ${ui.avatar}
        <div class="akd-comment__body" data-comment-tap-target>
            <div class="akd-comment__meta">
                ${ui.open}<span class="akd-comment__author"></span><span class="akd-comment__username"></span>${ui.close}
            </div>
            <p class="akd-comment__text"></p>
            ${actionsMarkup(own, 'comment')}

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
        </div>`;
    populate(article, data, current, { pending });

    if ((data.replies && data.replies.length) || num(data.reply_count) > 0) hydrateReplies(article, data);
    return article;
}

/** Builds a thread's reply list from a fetched page (the first batch plus the database's total). */
function hydrateReplies(thread, data) {
    const composer = thread.querySelector('[data-reply-composer]');
    const list = ensureReplyList(thread, composer);
    const current = readCurrentUser();
    const replies = data.replies || [];

    replies.forEach((reply) => list.appendChild(buildReplyElement(reply, current)));

    const total = Math.max(num(data.reply_count), replies.length);
    list.dataset.totalReplies = String(total);
    list.dataset.visibleReplies = String(Math.min(REPLY_INITIAL_VISIBLE, replies.length));
    list.dataset.repliesAfter = replies.length ? String(replies[replies.length - 1].id) : '';
    list.dataset.repliesMore = total > replies.length ? '1' : '0';

    Array.from(list.children).forEach((item, index) => {
        item.classList.toggle('is-hidden-reply', index >= REPLY_INITIAL_VISIBLE);
    });

    reindexReplyList(list);
    ensureReplyControls(list);
    updateReplyExpandControl(list);
}

// ---- Reply connectors ----
// Replies are ONE flat level; depth is never expressed by indentation. A reply to another reply
// draws a line (::before) up to the bottom of that reply's avatar, following reply_to_id exactly,
// even when other replies sit in between. The geometry is measured here and handed to CSS.

const linkFrames = new WeakMap();
const observedLists = new WeakSet();
let linkObserver = null;

function layoutReplyLinks(list) {
    const items = Array.from(list.querySelectorAll(':scope > [data-comment-id]'));
    const byId = new Map(items.map((el) => [el.dataset.commentId, el]));

    items.forEach((el) => {
        const target = byId.get(el.dataset.replyToId || '');
        const shown = Boolean(target)
            && !el.classList.contains('is-hidden-reply') && !target.classList.contains('is-hidden-reply');
        let top = 0;
        let height = 0;

        if (shown) {
            // 30px avatar, with 4px breathing room above and below the line.
            const distance = el.getBoundingClientRect().top - target.getBoundingClientRect().top;
            height = distance - 38;
            top = -(distance - 34);
        }

        const linked = shown && height >= 6;
        el.classList.toggle('akd-comment--nested-reply', linked);

        if (linked) {
            el.style.setProperty('--link-top', `${top}px`);
            el.style.setProperty('--link-height', `${height}px`);
        } else {
            el.style.removeProperty('--link-top');
            el.style.removeProperty('--link-height');
        }
    });
}

function scheduleReplyLinks(list) {
    if (linkFrames.has(list)) return;

    linkFrames.set(list, requestAnimationFrame(() => {
        linkFrames.delete(list);
        if (list.isConnected) layoutReplyLinks(list);
    }));
}

/** Re-measure whenever the list changes size: expand, hide, new reply, resize, comments sheet opening. */
function observeReplyList(list) {
    if (observedLists.has(list) || !('ResizeObserver' in window)) return;
    observedLists.add(list);
    linkObserver ||= new ResizeObserver((entries) => entries.forEach((entry) => scheduleReplyLinks(entry.target)));
    linkObserver.observe(list);
}

export function initReplyLinks() {
    document.querySelectorAll('[data-reply-list]').forEach((list) => {
        observeReplyList(list);
        scheduleReplyLinks(list);
    });

    document.fonts?.ready.then(() => document.querySelectorAll('[data-reply-list]').forEach(scheduleReplyLinks));
}

// ---- Reply list state ----
function loadedCount(list) {
    return list.querySelectorAll(':scope > [data-comment-id]').length;
}

/** Re-numbers the replies and redraws the connector lines. Returns how many replies are loaded. */
function reindexReplyList(list) {
    const items = Array.from(list.querySelectorAll(':scope > [data-comment-id]'));
    items.forEach((el, index) => { el.dataset.replyIndex = String(index); });
    scheduleReplyLinks(list);
    return items.length;
}

function numericId(node) {
    const id = Number(node.dataset.commentId);
    return Number.isFinite(id) ? id : Infinity; // pending (tmp-) comments sort last
}

/** Replies stay in id order, so one fetched later slots in where the database would put it. */
function insertReplySorted(list, element) {
    const id = numericId(element);
    const before = Array.from(list.querySelectorAll(':scope > [data-comment-id]')).find((item) => numericId(item) > id);
    if (before) list.insertBefore(element, before);
    else list.appendChild(element);
}

const listState = (list) => (list ? {
    total: list.dataset.totalReplies, visible: list.dataset.visibleReplies,
    more: list.dataset.repliesMore, after: list.dataset.repliesAfter,
} : null);

function applyListState(list, state) {
    list.dataset.totalReplies = state.total;
    list.dataset.visibleReplies = state.visible;
    list.dataset.repliesMore = state.more;
    list.dataset.repliesAfter = state.after;
}

function ensureReplyList(thread, composer) {
    let list = thread.querySelector('[data-reply-list]');

    if (!list) {
        list = document.createElement('div');
        list.className = 'akd-comment-replies';
        list.setAttribute('data-reply-list', '');
        list.dataset.totalReplies = '0';
        list.dataset.visibleReplies = '0';
        list.dataset.repliesAfter = '';
        list.dataset.repliesMore = '0';
        composer.insertAdjacentElement('afterend', list);
    }

    observeReplyList(list);
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

function removeReplyListIfEmpty(list) {
    if (loadedCount(list) > 0) return;
    const controls = list.nextElementSibling;
    if (controls?.matches('[data-reply-controls]')) controls.remove();
    list.remove();
}

// ---- Reply reveal/hide animation ----
const settleTimers = new WeakMap();

function animateReplyVisibility(reply, shouldShow) {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        reply.classList.toggle('is-hidden-reply', !shouldShow);
        reply.style.maxHeight = '';
        return;
    }

    // While animating, the reply clips its content; once settled it must not, because its
    // connector line reaches above its own box (see _single-post.scss).
    reply.classList.add('is-animating');

    const finish = () => {
        clearTimeout(settleTimers.get(reply));
        reply.removeEventListener('transitionend', onEnd);
        reply.style.maxHeight = '';
        reply.classList.remove('is-animating');
    };
    const onEnd = (event) => { if (event.propertyName === 'max-height') finish(); };

    reply.addEventListener('transitionend', onEnd);
    clearTimeout(settleTimers.get(reply));
    settleTimers.set(reply, setTimeout(finish, 450)); // safety net if transitionend never fires

    if (shouldShow) {
        reply.classList.remove('is-hidden-reply');
        const targetHeight = reply.scrollHeight;
        reply.style.maxHeight = '0px';

        requestAnimationFrame(() => {
            reply.style.maxHeight = `${targetHeight}px`;
        });
    } else {
        const currentHeight = reply.scrollHeight;
        reply.style.maxHeight = `${currentHeight}px`;
        reply.classList.add('is-hidden-reply');

        requestAnimationFrame(() => {
            reply.style.maxHeight = '0px';
        });
    }
}

function setReplyVisibility(list, visibleCount) {
    const total = num(list.dataset.totalReplies);
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

    scheduleReplyLinks(list);
}

function updateReplyExpandControl(list) {
    const controls = list.parentElement?.querySelector('[data-reply-controls]');
    if (!controls) return;

    const viewBtn = controls.querySelector('[data-reply-view]');
    const viewLabel = viewBtn?.querySelector('[data-reply-view-label]');
    const viewIcon = viewBtn?.querySelector('i');
    const hideBtn = controls.querySelector('[data-reply-hide]');

    const total = num(list.dataset.totalReplies);
    const visible = num(list.dataset.visibleReplies);
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

// ---- Loading more (database-backed: N + 1 look-ahead on the server) ----

async function fetchMoreReplies(list) {
    const thread = list.closest('.akd-comment-thread');
    const parentId = thread?.dataset.commentId;
    if (!parentId || isTemp(parentId)) return;

    const after = list.dataset.repliesAfter;
    const result = await api(`${API}/${encodeURIComponent(parentId)}/replies${after ? `?after=${encodeURIComponent(after)}` : ''}`);
    if (result.success === false) throw new Error(result.message || 'Could not load replies.');

    const current = readCurrentUser();
    const items = result.items || [];

    items.forEach((item) => {
        if (list.querySelector(`:scope > [data-comment-id="${item.id}"]`)) return; // already shown (e.g. posted here)
        insertReplySorted(list, buildReplyElement(item, current));
    });

    list.dataset.repliesMore = result.has_more ? '1' : '0';
    if (result.has_more && result.next_cursor) list.dataset.repliesAfter = String(result.next_cursor);
    else if (items.length) list.dataset.repliesAfter = String(items[items.length - 1].id);
    const loaded = reindexReplyList(list);
    list.dataset.totalReplies = String(Math.max(num(result.reply_count), loaded));
}

async function expandReplies(list, button) {
    if (list.dataset.loading === '1') return;
    const want = num(list.dataset.visibleReplies) + REPLY_BATCH_SIZE;
    const label = button.querySelector('[data-reply-view-label]');

    if (want > loadedCount(list) && list.dataset.repliesMore === '1') {
        list.dataset.loading = '1';
        button.disabled = true;
        if (label) label.textContent = 'Loading...';

        try {
            await fetchMoreReplies(list);
        } catch (err) {
            error('Could not load replies. Please try again.');
        } finally {
            list.dataset.loading = '';
            button.disabled = false;
        }
    }

    setReplyVisibility(list, Math.min(want, loadedCount(list)));
    updateReplyExpandControl(list);
}

async function loadMoreComments(button) {
    const wrap = button.closest('[data-comments-more]');
    const list = document.querySelector('[data-comment-list]');
    const cursor = wrap?.dataset.nextCursor;
    if (!wrap || !list || !cursor || wrap.dataset.loading === '1') return;

    const label = wrap.querySelector('[data-comments-more-label]');
    wrap.dataset.loading = '1';
    button.disabled = true;
    if (label) label.textContent = 'Loading...';

    try {
        const result = await api(`${API}?article=${encodeURIComponent(articleId())}&before=${encodeURIComponent(cursor)}`);
        if (result.success === false) throw new Error(result.message || 'Could not load comments.');

        const current = readCurrentUser();

        (result.items || []).forEach((item) => {
            if (list.querySelector(`:scope > [data-comment-id="${item.id}"]`)) return;
            list.insertBefore(buildCommentElement(item, current), wrap);
        });

        if (result.has_more && result.next_cursor) wrap.dataset.nextCursor = String(result.next_cursor);
        else wrap.remove();

        if (typeof result.total === 'number') setCommentTotal(result.total);
    } catch (err) {
        error('Could not load more comments. Please try again.');
    } finally {
        wrap.dataset.loading = '';
        button.disabled = false;
        if (label) label.textContent = 'Show more comments';
        updateCommentListScrollState();
    }
}

export function initReplyExpansion() {
    initReplyLinks();
    initLiveTimestamps();

    // One delegated handler: reply expand/hide, plus the "Show more comments" button.
    document.addEventListener('click', (event) => {
        const moreBtn = event.target.closest('[data-comments-more-btn]');
        if (moreBtn) {
            loadMoreComments(moreBtn);
            return;
        }

        const viewBtn = event.target.closest('[data-reply-view]');
        if (viewBtn) {
            const list = viewBtn.closest('.akd-comment__body')?.querySelector('[data-reply-list]');
            if (!list) return;

            if (num(list.dataset.visibleReplies) >= num(list.dataset.totalReplies)) {
                setReplyVisibility(list, REPLY_INITIAL_VISIBLE);
                updateReplyExpandControl(list);
            } else {
                expandReplies(list, viewBtn);
            }
            return;
        }

        const hideBtn = event.target.closest('[data-reply-hide]');
        if (hideBtn) {
            const list = hideBtn.closest('.akd-comment__body')?.querySelector('[data-reply-list]');
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
    const targetId = targetEl?.dataset.commentId || '';

    // A comment that has not been saved yet cannot be replied to.
    if (isTemp(targetId) || isTemp(thread.dataset.commentId)) return;

    const targetUsername = replyBtn.dataset.replyUsername || '';
    const targetName = replyBtn.dataset.replyName || 'this comment';
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

/** A reply the server refused: put the composer back exactly as it was, with the reason. */
function reopenReplyComposer(composer, text, message) {
    document.querySelectorAll('[data-reply-composer]:not([hidden])').forEach((open) => {
        if (open !== composer) open.hidden = true;
    });

    composer.hidden = false;
    const textarea = composer.querySelector('[data-reply-input]');
    const errorEl = composer.querySelector('[data-reply-error]');
    const countEl = composer.querySelector('[data-reply-count]');

    if (textarea) {
        textarea.value = text;
        autoGrowTextarea(textarea);
        textarea.focus();
    }

    if (countEl) countEl.textContent = `${text.length}/${COMMENT_MAX_LENGTH}`;

    if (errorEl) {
        errorEl.textContent = message;
        errorEl.hidden = false;
    }
}

function submitReply(submitBtn) {
    if (Date.now() - lastReplySubmit < 600) return; // a double tap is one request
    lastReplySubmit = Date.now();
    const composer = submitBtn.closest('[data-reply-composer]');
    const thread = composer?.closest('.akd-comment-thread');
    const textarea = composer?.querySelector('[data-reply-input]');
    const errorEl = composer?.querySelector('[data-reply-error]');
    const current = readCurrentUser();
    if (!composer || !thread || !textarea || !current) return;
    const text = textarea.value.trim();
    
    if (errorEl) {
        errorEl.hidden = true;
        errorEl.textContent = '';
    }

    const fail = (message) => {
        if (!errorEl) return;
        errorEl.textContent = message;
        errorEl.hidden = false;
    };

    if (!text) { fail('Write something before replying.'); return; }
    if (text.length > COMMENT_MAX_LENGTH) { fail(`Replies are limited to ${COMMENT_MAX_LENGTH} characters.`); return; }

    const targetId = composer.dataset.replyToId || '';
    if (!targetId || isTemp(targetId) || isTemp(thread.dataset.commentId)) {
        fail('That comment is still posting. Try again in a moment.');
        return;
    }

    const isTopLevelTarget = composer.dataset.replyToIsTopLevel === '1';
    const targetUsername = isTopLevelTarget ? null : composer.dataset.replyToUsername;
    const list = ensureReplyList(thread, composer);
    let pending = null;

    optimistic({
        apply: () => {
            pending = buildReplyElement({
                id: nextTempId(), parent_id: thread.dataset.commentId,
                reply_to_id: isTopLevelTarget ? null : targetId, reply_to_username: targetUsername,
                username: current.username, fullname: current.fullname, initials: current.initials,
                avatar_color: current.avatarColor, content: text, created_at: null,
            }, current, { pending: true });

            insertReplySorted(list, pending);
            list.dataset.totalReplies = String(num(list.dataset.totalReplies) + 1);
            ensureReplyControls(list);
            reindexReplyList(list);
            setReplyVisibility(list, loadedCount(list)); // make sure the new reply is showing
            updateReplyExpandControl(list);
            closeReplyComposer(composer);
            adjustTotal(1);
            requestAnimationFrame(() => pending.scrollIntoView({ block: 'nearest', behavior: 'smooth' }));
        },
        request: () => api(API, {
            method: 'POST',
            body: { article: articleId(), content: text, target_id: Number(targetId) },
        }),
        commit: (result) => {
            populate(pending, result.comment, current);
            pending.classList.remove('is-pending');
            reindexReplyList(list);
            setCommentTotal(result.total);
            success('Reply posted');
        },
        rollback: () => {
            pending.remove();
            list.dataset.totalReplies = String(Math.max(0, num(list.dataset.totalReplies) - 1));
            const loaded = reindexReplyList(list);
            setReplyVisibility(list, Math.min(num(list.dataset.visibleReplies), loaded));
            updateReplyExpandControl(list);
            removeReplyListIfEmpty(list);
            adjustTotal(-1);
        },
        onError: (e) => reopenReplyComposer(composer, text, failureText(e)),
    });
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

// ---- Delete (a comment, and everything beneath it) ----

/** The reply and every loaded reply beneath it, found through reply_to_id. Replies always come after their target. */
function subtreeNodes(list, root) {
    const ids = new Set([root.dataset.commentId]);
    const nodes = [root];

    list.querySelectorAll(':scope > [data-comment-id]').forEach((item) => {
        if (item !== root && ids.has(item.dataset.replyToId)) {
            ids.add(item.dataset.commentId);
            nodes.push(item);
        }
    });

    return nodes;
}

/** Removes the comment (and its subtree) from the page now. Returns what restoreComment() needs. */
function detachComment(commentEl, list, isReply) {
    const nodes = isReply && list ? subtreeNodes(list, commentEl) : [commentEl];
    const snapshot = { nodes: [], list, listState: listState(list), total: readTotal() };

    nodes.forEach((node) => {
        snapshot.nodes.push({ node, parent: node.parentNode, next: node.nextSibling });
        node.remove();
    });

    if (isReply && list) {
        list.dataset.totalReplies = String(Math.max(0, num(snapshot.listState.total) - nodes.length));
        const loaded = reindexReplyList(list);
        setReplyVisibility(list, Math.min(num(snapshot.listState.visible), loaded));
        updateReplyExpandControl(list);
        adjustTotal(-nodes.length);
    } else {
        const ownReplies = commentEl.querySelector('[data-reply-list]');
        adjustTotal(-(1 + (ownReplies ? num(ownReplies.dataset.totalReplies) : 0)));
        checkCommentListEmpty();
    }

    updateCommentListScrollState();
    return snapshot;
}

function restoreComment(snapshot) {
    snapshot.nodes.slice().reverse().forEach(({ node, parent, next }) => {
        parent.insertBefore(node, next && next.parentNode === parent ? next : null);
    });

    document.querySelector('[data-comment-empty]')?.remove();

    if (snapshot.list?.isConnected) {
        applyListState(snapshot.list, snapshot.listState);
        reindexReplyList(snapshot.list);
        setReplyVisibility(snapshot.list, num(snapshot.listState.visible));
        updateReplyExpandControl(snapshot.list);
    }

    setCommentTotal(snapshot.total);
    updateCommentListScrollState();
}

/** The server's list of deleted ids is the final word: drop anything we still show, then take its total. */
function reconcileDeleted(result) {
    (result.deleted_ids || []).forEach((id) => {
        document.querySelectorAll(`[data-comment-id="${id}"]`).forEach((node) => {
            const list = node.closest('[data-reply-list]');
            node.remove();
            if (list) reindexReplyList(list);
        });
    });

    if (typeof result.total === 'number') setCommentTotal(result.total);
    updateCommentListScrollState();
}

export function initCommentDeletion() {
    document.addEventListener('click', async (event) => {
        const deleteBtn = event.target.closest('[data-comment-delete]');
        if (!deleteBtn) return;
        const commentEl = deleteBtn.closest('.akd-comment');
        if (!commentEl || isTemp(commentEl.dataset.commentId)) return;
        const isReply = deleteBtn.dataset.deleteTarget === 'reply';

        const confirmed = await useConfirmDialog().ask({
            title: isReply ? 'Delete reply' : 'Delete comment',
            message: `This will remove the ${isReply ? 'reply and any replies to it' : 'comment and all of its replies'}. This cannot be undone.`,
            confirmLabel: 'Delete',
            cancelLabel: 'Cancel',
            destructive: true,
        });

        if (!confirmed || !commentEl.isConnected) return;

        const id = commentEl.dataset.commentId;
        const list = commentEl.closest('[data-reply-list]');

        optimistic({
            apply: () => detachComment(commentEl, list, isReply),
            request: () => api(`${API}/${encodeURIComponent(id)}`, { method: 'DELETE' }),
            commit: reconcileDeleted,
            rollback: restoreComment,
            errorMessage: 'Could not delete the comment.',
        });
    });
}

// ---- Top-level comment composer ----

export function initCommentComposer() {
    const form = document.querySelector('[data-comment-form]');
    if (!form) return;
    const textarea = form.querySelector('[data-comment-input]');
    const countEl = form.querySelector('[data-comment-count]');
    const errorEl = form.querySelector('[data-comment-error]');
    const list = document.querySelector('[data-comment-list]');
    const current = readCurrentUser();
    if (!textarea || !list || !current) return;

    let lastSubmit = 0;

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
        if (Date.now() - lastSubmit < 600) return; // a double tap must not look like an empty submit
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

        lastSubmit = Date.now();
        let pending = null;

        optimistic({
            apply: () => {
                document.querySelector('[data-comment-empty]')?.remove();
                pending = buildCommentElement({
                    id: nextTempId(), parent_id: null, reply_to_id: null, reply_to_username: null,
                    username: current.username, fullname: current.fullname, initials: current.initials,
                    avatar_color: current.avatarColor, content: text, created_at: null,
                    replies: [], reply_count: 0,
                }, current, { pending: true });

                list.prepend(pending);
                textarea.value = '';
                updateCount();
                autoGrowTextarea(textarea);
                adjustTotal(1);
                updateCommentListScrollState();
            },
            request: () => api(API, {
                method: 'POST',
                body: { article: articleId(), content: text, target_id: null },
            }),
            commit: (result) => {
                populate(pending, result.comment, current);
                pending.classList.remove('is-pending');
                setCommentTotal(result.total);
                updateCommentListScrollState();
                success('Comment posted');
            },
            rollback: () => {
                pending.remove();
                adjustTotal(-1);
                checkCommentListEmpty();
                updateCommentListScrollState();

                // Give the member their words back.
                if (textarea.value === '') {
                    textarea.value = text;
                    updateCount();
                    autoGrowTextarea(textarea);
                }
            },
            onError: (e) => showError(failureText(e)),
        });
    });
}

// ---- Table of contents, copy link, lightbox ----

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

// ---- Live timestamps (SSE, single-post page only) ----
// The server's "clock" event changes every 3 minutes. It only tells the page to re-render the
// relative times it already shows; it never refetches comments.

const CLOCK_STREAM_URL = '/member/stream/events?topics=clock';
const FALLBACK_REFRESH_MS = 3 * 60 * 1000;
let liveTimestampsStarted = false;

function refreshTimestamps() {
    document.querySelectorAll('.akd-comment__time[data-time]').forEach((el) => {
        const next = relativeTime(el.dataset.time);
        if (el.textContent !== next) el.textContent = next;
    });
}

export function initLiveTimestamps() {
    // Only where comments exist: never a site-wide connection.
    if (liveTimestampsStarted || !document.getElementById('comments')) return;
    liveTimestampsStarted = true;

    let source = null;
    let lastBucket = null;
    let fallbackTimer = 0;

    const startFallback = () => { if (!fallbackTimer) fallbackTimer = setInterval(refreshTimestamps, FALLBACK_REFRESH_MS); };
    const stopFallback = () => { clearInterval(fallbackTimer); fallbackTimer = 0; };
    const disconnect = () => { source?.close(); source = null; };

    function connect() {
        if (source) return;

        if (!('EventSource' in window)) {
            startFallback();
            return;
        }

        source = new EventSource(CLOCK_STREAM_URL);

        source.addEventListener('clock', (event) => {
            let bucket;

            try {
                bucket = JSON.parse(event.data).bucket;
            } catch {
                return;
            }

            // The first message only sets the baseline; later ones fire when the 3-minute bucket moves.
            if (lastBucket !== null && bucket !== lastBucket) refreshTimestamps();
            lastBucket = bucket;
            stopFallback();
        });

        source.addEventListener('error', () => {
            // EventSource reconnects on its own. CLOSED means the server refused the stream
            // (switched off or signed out), so fall back to a plain timer.
            if (source && source.readyState === EventSource.CLOSED) {
                disconnect();
                startFallback();
            }
        });
    }

    // A hidden tab cannot show anything: release the connection, and catch up when it returns.
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            disconnect();
            stopFallback();
        } else {
            refreshTimestamps();
            lastBucket = null;
            connect();
        }
    });

    window.addEventListener('pagehide', () => { disconnect(); stopFallback(); });
    connect();
}

// ---- Hero and in-article images: the same icon fallback the cards use ----
export function initArticleImages() {
    document.querySelectorAll('.akd-post-hero__media').forEach((media) => {
        const img = media.querySelector('.akd-post-hero__image');
        if (!img) return;

        settleImage(img, () => {}, () => {
            const icon = document.createElement('span');
            icon.className = 'akd-post-hero__placeholder';
            icon.setAttribute('aria-hidden', 'true');
            icon.innerHTML = '<i class="fa-solid fa-newspaper"></i>';
            media.replaceChildren(icon); // also drops the lightbox link around the dead image
        });
    });

    // A broken in-article image is removed with its caption and lightbox link, never shown broken.
    document.querySelectorAll('.akd-post-content__figure').forEach((figure) => {
        const img = figure.querySelector('.akd-post-content__image');
        if (img) settleImage(img, () => {}, () => figure.remove());
    });
}

export function initArticleLightbox() {
    initLightbox();
}