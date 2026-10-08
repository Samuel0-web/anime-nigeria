// resources/js/admin/blog-editor.js
// Article editor modal: Tiptap body, plain-text title with _italic_, autosave for drafts and
// scheduled articles (never published ones), scheduling, live word count and reading time.
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import { api, handleApiError } from '../modules/api';
import { success, error as notifyError } from '../modules/toast';
import { setLoading, clearLoading } from '../modules/loading-state';
import { useModal } from '../modules/modal';
import { useConfirmDialog } from '../modules/confirm-dialog';
import { initSelect } from '../modules/select-menu';
import { optimistic } from '../modules/optimistic';
import { collapseSpaces, escapeHtml, setFieldError, clearFieldError, focusFirstError } from './form-utils';
import { BlogFigure } from './blog-figure';
import { featuredQueue } from './blog-featured';

export const BLOG_CHANGED_EVENT = 'akd:blog-changed';

const API = '/admin/api/blog';
const AUTOSAVE_DELAY = 1500;
const AUTOSAVE_MAX_WAIT = 12000;
const WORDS_PER_MINUTE = 230;
const IMAGE_TYPES = ['image/png', 'image/jpeg', 'image/webp'];
const IMAGE_MAX_BYTES = 5 * 1024 * 1024;
const MAX_TAGS = 8;
const TAG_PATTERN = /^[\p{L}\p{N}][\p{L}\p{N} &+#.\-]*$/u;
// Keep in step with BlogTitle::html(): _text_ renders as <em>text</em>.
const ITALIC = /_([^_\s](?:[^_]*[^_\s])?)_/g;
const SIMPLE_KEYS = ['title', 'excerpt', 'category_id', 'tags', 'cover_image', 'cover_image_alt', 'slug'];

const modal = useModal();
const confirmDialog = useConfirmDialog();

const countWords = (text) => (text.trim() === '' ? 0 : text.trim().split(/\s+/u).length);
const readingMinutes = (words) => Math.max(1, Math.ceil(words / WORDS_PER_MINUTE));
const same = (a, b) => JSON.stringify(a) === JSON.stringify(b);
const pad = (n) => String(n).padStart(2, '0');

function toLocalInput(iso) {
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '';
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function localInputToIso(value) {
    const d = new Date(value);
    return Number.isNaN(d.getTime()) ? null : d.toISOString();
}

function localSlug(value) {
    return value.toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 150);
}

function normalizeHref(value) {
    const href = value.trim();
    if (!href || /[\s]/.test(href)) return null;
    if (/^(https?:\/\/|mailto:|\/(?!\/)|#)/i.test(href)) return href;
    if (/^[\w-]+(\.[\w-]+)+(\/\S*)?$/.test(href)) return `https://${href}`;
    return null;
}

function pickArticle(a) {
    return {
        id: a.id, public_id: a.public_id, slug: a.slug || '', status: a.status,
        published_at: a.published_at, featured_position: a.featured_position,
        updated_at: a.updated_at, url: a.url,
    };
}

async function uploadImage(file) {
    if (!IMAGE_TYPES.includes(file.type)) throw new Error('Please choose a PNG, JPG or WebP image.');
    if (file.size > IMAGE_MAX_BYTES) throw new Error('Image must not exceed 5MB.');

    const data = new FormData();
    data.set('image', file);
    const res = await api(`${API}/upload`, { method: 'POST', body: data });
    if (res.success === false) throw new Error(res.errors?.image || res.message || 'Could not upload the image.');
    return res.url;
}

const TOOLS = [
    ['bold', 'fa-solid fa-bold', 'Bold'],
    ['italic', 'fa-solid fa-italic', 'Italic'],
    ['link', 'fa-solid fa-link', 'Link'],
    ['h2', null, 'Heading'],
    ['h3', null, 'Subheading'],
    ['bullet', 'fa-solid fa-list-ul', 'Bulleted list'],
    ['ordered', 'fa-solid fa-list-ol', 'Numbered list'],
    ['quote', 'fa-solid fa-quote-left', 'Quote'],
    ['image', 'fa-regular fa-image', 'Insert image'],
    ['undo', 'fa-solid fa-rotate-left', 'Undo'],
    ['redo', 'fa-solid fa-rotate-right', 'Redo'],
];

const TEMPLATE = `
<div class="akd-be__grid">
  <div class="akd-be__main">
    <div class="akd-admin-field" data-field="title">
      <label class="akd-admin-field__label" for="beTitle">Title</label>
      <div class="akd-be-title">
        <div class="akd-be-title__mirror" data-title-mirror aria-hidden="true"></div>
        <textarea id="beTitle" class="akd-be-title__input" rows="1" maxlength="160" placeholder="Article title"
          aria-describedby="beTitle-hint beTitle-error" data-title></textarea>
      </div>
      <p class="akd-admin-field__hint" id="beTitle-hint">Put underscores around a word for italics, like _this_.</p>
      <p class="akd-admin-field__error" id="beTitle-error" data-field-error aria-live="polite"></p>
    </div>

    <div class="akd-admin-field" data-field="excerpt">
      <div class="akd-ann-labelrow">
        <label class="akd-admin-field__label" for="beExcerpt">Excerpt</label>
        <span class="akd-ann-counter" data-excerpt-counter aria-hidden="true"></span>
      </div>
      <textarea id="beExcerpt" class="akd-admin-field__input akd-bl-textarea" maxlength="240" rows="2"
        aria-describedby="beExcerpt-error" data-excerpt></textarea>
      <p class="akd-admin-field__error" id="beExcerpt-error" data-field-error aria-live="polite"></p>
    </div>

    <div class="akd-admin-field" data-field="content_html">
      <span class="akd-admin-field__label" id="beBodyLabel">Article</span>
      <div class="akd-be-editor">
        <div class="akd-be-toolbar" role="toolbar" aria-label="Formatting" data-toolbar data-no-sidebar-swipe></div>
        <form class="akd-be-linkbar" data-linkbar hidden novalidate>
          <input type="text" class="akd-admin-field__input" data-link-input placeholder="https://example.com" aria-label="Link address" autocomplete="off">
          <button type="submit" class="akd-admin-btn akd-admin-btn--primary">Apply</button>
          <button type="button" class="akd-admin-btn" data-link-remove hidden>Remove</button>
          <button type="button" class="akd-admin-btn akd-admin-btn--ghost" data-link-cancel>Cancel</button>
          <p class="akd-admin-field__error" data-link-error aria-live="polite"></p>
        </form>
        <div class="akd-be-host" data-editor-host data-placeholder="Start writing..."></div>
        <div class="akd-be-stats"><span data-words>0 words</span><span class="akd-bl-dot" aria-hidden="true">&middot;</span><span data-reading>No content yet</span></div>
        <input type="file" accept="image/png,image/jpeg,image/webp" hidden data-image-input aria-label="Choose an image">
      </div>
      <p class="akd-admin-field__error" data-field-error aria-live="polite"></p>
    </div>
  </div>

  <aside class="akd-be__side" aria-label="Article settings">
    <section class="akd-be-card">
      <h3 class="akd-be-card__title">Publishing</h3>
      <div class="akd-be-pub"><span class="akd-bl-status" data-status-pill></span><span class="akd-be-pub__note" data-status-note></span></div>
      <div class="akd-admin-field" data-field="scheduled_at">
        <label class="akd-admin-field__label" for="beSchedule">Schedule for</label>
        <input type="datetime-local" id="beSchedule" class="akd-admin-field__input" data-schedule aria-describedby="beSchedule-hint beSchedule-error">
        <p class="akd-admin-field__hint" id="beSchedule-hint" data-schedule-hint></p>
        <p class="akd-admin-field__error" id="beSchedule-error" data-field-error aria-live="polite"></p>
      </div>
      <ul class="akd-be-check" data-checklist></ul>
    </section>

    <section class="akd-be-card">
      <h3 class="akd-be-card__title">Details</h3>
      <div class="akd-admin-field" data-field="category_id">
        <span class="akd-admin-field__label" id="beCatLabel">Category</span>
        <div class="akd-select" data-category>
          <button type="button" class="akd-admin-field__input akd-select__trigger" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="beCatLabel">
            <span class="akd-select__label" data-select-label></span>
            <i class="fa-solid fa-chevron-down akd-select__chevron" aria-hidden="true"></i>
          </button>
        </div>
        <p class="akd-admin-field__error" data-field-error aria-live="polite"></p>
      </div>
      <div class="akd-admin-field" data-field="tags">
        <label class="akd-admin-field__label" for="beTagInput">Tags</label>
        <div class="akd-be-tags" data-tags>
          <ul class="akd-be-tags__list" data-tag-list></ul>
          <input type="text" id="beTagInput" class="akd-be-tags__input" maxlength="30" autocomplete="off" placeholder="Add a tag" data-tag-input>
        </div>
        <p class="akd-admin-field__hint">Up to ${MAX_TAGS}. Press Enter to add.</p>
        <p class="akd-admin-field__error" data-field-error aria-live="polite"></p>
      </div>
      <div class="akd-admin-field" data-field="slug">
        <label class="akd-admin-field__label" for="beSlug">Address</label>
        <div class="akd-be-slug"><span class="akd-be-slug__prefix">/member/blog/post/</span>
          <input type="text" id="beSlug" class="akd-admin-field__input" maxlength="150" autocomplete="off" autocapitalize="none" spellcheck="false" data-slug aria-describedby="beSlug-hint beSlug-error">
          <span class="akd-be-slug__suffix" data-slug-suffix></span></div>
        <p class="akd-admin-field__hint" id="beSlug-hint">Made from the title the first time you save. Changing it keeps old links working.</p>
        <p class="akd-admin-field__error" id="beSlug-error" data-field-error aria-live="polite"></p>
      </div>
    </section>

    <section class="akd-be-card">
      <h3 class="akd-be-card__title">Cover image</h3>
      <div class="akd-admin-field" data-field="cover_image">
        <div class="akd-ann-image">
          <div class="akd-ann-image__preview" data-cover-preview></div>
          <div class="akd-ann-image__controls">
            <input type="file" accept="image/png,image/jpeg,image/webp" hidden data-cover-input aria-label="Choose cover image">
            <div class="akd-ann-image__buttons">
              <button type="button" class="akd-admin-btn" data-cover-choose><i class="fa-regular fa-image" aria-hidden="true"></i> <span data-cover-label>Choose image</span></button>
              <button type="button" class="akd-admin-btn akd-admin-btn--ghost akd-admin-btn--danger" data-cover-remove hidden>Remove</button>
            </div>
            <p class="akd-admin-field__hint">PNG, JPG or WebP, up to 5MB.</p>
          </div>
        </div>
        <p class="akd-admin-field__error" data-field-error aria-live="polite"></p>
      </div>
      <div class="akd-admin-field" data-field="cover_image_alt" data-alt-field hidden>
        <label class="akd-admin-field__label" for="beCoverAlt">Image description</label>
        <input type="text" id="beCoverAlt" class="akd-admin-field__input" maxlength="200" autocomplete="off" data-cover-alt>
        <p class="akd-admin-field__error" data-field-error aria-live="polite"></p>
      </div>
    </section>

    <section class="akd-be-card">
      <h3 class="akd-be-card__title">Featured</h3>
      <label class="akd-ann-switch">
        <input type="checkbox" role="switch" data-featured-switch aria-describedby="beFeaturedHint">
        <span class="akd-ann-switch__track" aria-hidden="true"></span>
        <span class="akd-ann-switch__text">Feature on the Blog home</span>
      </label>
      <p class="akd-admin-field__hint" id="beFeaturedHint" data-featured-hint></p>
    </section>
  </aside>
</div>`;

const FOOTER = `
<div class="akd-be-footer">
  <div class="akd-be-save" data-save role="status" aria-live="polite" data-state="clean">
    <span class="akd-be-save__dot" aria-hidden="true"></span><span data-save-text></span>
    <button type="button" class="akd-be-save__retry" data-save-retry hidden>Retry</button>
  </div>
  <div class="akd-be-actions" data-actions></div>
</div>`;

export async function openArticleEditor(ctx = {}) {
    if (modal.isOpen()) return;
    let loaded = null;

    if (ctx.id) {
        try {
            const res = await api(`${API}/${ctx.id}`);

            if (res.success === false) {
                notifyError(res.message || 'Could not open the article.');
                return;
            }

            loaded = res.article;
        } catch (err) {
            handleApiError(err, 'Could not open the article.');
            return;
        }

        if (loaded.status === 'archived') {
            notifyError('Restore this article before editing it.');
            return;
        }
    }

    mount(loaded, ctx);
}

function mount(initial, ctx) {
    const categories = ctx.categories || [];
    let article = initial ? pickArticle(initial) : null;
    let tags = [...(initial?.tags || [])];
    let coverPath = initial?.cover_image || '';
    let words = 0;
    let editor = null;

    let changed = false;
    let committed = false;
    let busy = false;
    let fatal = false;
    let slugTouched = false;
    let contentVersion = 0;
    let savedContentVersion = 0;
    let saveState = 'clean';
    let savedAt = null;
    let saveChain = null;
    let saveAgain = false;
    let autosaveTimer = 0;
    let firstDirtyAt = 0;
    let statsFrame = 0;
    let uploading = 0;

    const saved = {
        title: initial?.title ?? '',
        excerpt: initial?.excerpt ?? '',
        category_id: initial?.category_id ? String(initial.category_id) : '',
        tags: [...tags],
        cover_image: coverPath,
        cover_image_alt: initial?.cover_image_alt ?? '',
        slug: initial?.slug ?? '',
    };

    const root = document.createElement('div');
    root.className = 'akd-be';
    root.innerHTML = TEMPLATE;
    const footer = document.createElement('div');
    footer.innerHTML = FOOTER;
    const footerRoot = footer.firstElementChild;

    const $ = (selector) => root.querySelector(selector);
    const fields = {};
    root.querySelectorAll('[data-field]').forEach((f) => { fields[f.dataset.field] = f; });

    const titleInput = $('[data-title]');
    const mirror = $('[data-title-mirror]');
    const excerptInput = $('[data-excerpt]');
    const excerptCounter = $('[data-excerpt-counter]');
    const host = $('[data-editor-host]');
    const toolbar = $('[data-toolbar]');
    const linkBar = $('[data-linkbar]');
    const linkInput = $('[data-link-input]');
    const linkError = $('[data-link-error]');
    const linkRemove = $('[data-link-remove]');
    const imageInput = $('[data-image-input]');
    const wordsEl = $('[data-words]');
    const readingEl = $('[data-reading]');
    const scheduleInput = $('[data-schedule]');
    const scheduleHint = $('[data-schedule-hint]');
    const statusPill = $('[data-status-pill]');
    const statusNote = $('[data-status-note]');
    const checklist = $('[data-checklist]');
    const tagList = $('[data-tag-list]');
    const tagInput = $('[data-tag-input]');
    const slugInput = $('[data-slug]');
    const slugSuffix = $('[data-slug-suffix]');
    const coverPreview = $('[data-cover-preview]');
    const coverInput = $('[data-cover-input]');
    const coverLabel = $('[data-cover-label]');
    const coverRemove = $('[data-cover-remove]');
    const coverAlt = $('[data-cover-alt]');
    const featuredSwitch = $('[data-featured-switch]');
    const featuredHint = $('[data-featured-hint]');
    const saveEl = footerRoot.querySelector('[data-save]');
    const saveText = footerRoot.querySelector('[data-save-text]');
    const saveRetry = footerRoot.querySelector('[data-save-retry]');
    const actions = footerRoot.querySelector('[data-actions]');

    // ---------------------------------------------------------------- state helpers
    const isAutosaveable = () => !article || article.status === 'draft' || article.status === 'scheduled';
    const status = () => article?.status ?? 'draft';

    function values() {
        return {
            title: collapseSpaces(titleInput.value),
            excerpt: collapseSpaces(excerptInput.value),
            category_id: categorySelect.getValue(),
            tags,
            cover_image: coverPath,
            cover_image_alt: collapseSpaces(coverAlt.value),
            slug: slugInput.value.trim(),
        };
    }

    function dirtyKeys() {
        const v = values();
        const keys = SIMPLE_KEYS.filter((k) => {
            if (k === 'slug' && v.slug === '') return false; // empty means "keep the generated address"
            return !same(v[k], saved[k]);
        });
        if (contentVersion !== savedContentVersion) keys.push('content_html');
        return keys;
    }

    const hasPending = () => dirtyKeys().length > 0;

    function buildPayload() {
        const v = values();
        const keys = dirtyKeys();
        const payload = {};
        const sent = {};

        keys.forEach((k) => {
            if (k === 'content_html') {
                payload.content_html = editor.getHTML();
                sent.content = contentVersion;
            } else if (k === 'category_id') {
                payload.category_id = v.category_id ? Number(v.category_id) : null;
                sent[k] = v[k];
            } else {
                payload[k] = v[k];
                sent[k] = v[k];
            }
        });

        return { payload, keys, sent };
    }

    function missingList() {
        const plainTitle = collapseSpaces(titleInput.value).replace(ITALIC, '$1');
        return [
            ['title', 'Add a title', plainTitle.length >= 3],
            ['excerpt', 'Write an excerpt (10+ characters)', collapseSpaces(excerptInput.value).length >= 10],
            ['category_id', 'Choose a category', Boolean(categorySelect.getValue())],
            ['content_html', 'Write the article', words >= 1],
        ];
    }

    const incomplete = () => missingList().some(([, , ok]) => !ok);

    // ---------------------------------------------------------------- save status
    function renderSaveStatus() {
        const published = status() === 'published';
        const time = savedAt ? savedAt.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' }) : '';
        const text = {
            clean: article ? (published ? 'Up to date' : 'All changes saved') : 'Not saved yet',
            dirty: 'Unsaved changes',
            saving: 'Saving...',
            saved: `Saved${time ? ` at ${time}` : ''}`,
            error: "Couldn't save. Your latest changes may not be stored.",
        }[saveState];

        saveEl.dataset.state = saveState;
        saveText.textContent = text;
        saveRetry.hidden = saveState !== 'error' || fatal;
    }

    function setSave(next) {
        saveState = next;
        renderSaveStatus();
    }

    // ---------------------------------------------------------------- UI refresh
    function renderStatus() {
        const s = status();
        statusPill.className = `akd-bl-status akd-bl-status--${s}`;
        statusPill.textContent = { draft: 'Draft', scheduled: 'Scheduled', published: 'Published' }[s];

        if (s === 'published') {
            statusNote.textContent = article.published_at
                ? `Live since ${new Date(article.published_at).toLocaleDateString(undefined, { dateStyle: 'medium' })}` : 'Live';
        } else if (s === 'scheduled') {
            const due = new Date(article.published_at);
            statusNote.textContent = due.getTime() < Date.now() && incomplete()
                ? 'Overdue: complete the missing items below to publish it'
                : `Goes live ${due.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' })}`;
        } else {
            statusNote.textContent = 'Not visible to members yet';
        }

        fields.scheduled_at.hidden = s === 'published';
        scheduleHint.textContent = `Your local time (${Intl.DateTimeFormat().resolvedOptions().timeZone}). Leave empty to publish right away.`;
    }

    function renderChecklist() {
        const list = missingList();
        const hide = status() === 'published' && list.every(([, , ok]) => ok);
        checklist.hidden = hide;
        checklist.replaceChildren(...list.map(([, label, ok]) => {
            const li = document.createElement('li');
            li.className = ok ? 'is-done' : '';
            const icon = document.createElement('i');
            icon.className = ok ? 'fa-solid fa-circle-check' : 'fa-regular fa-circle';
            icon.setAttribute('aria-hidden', 'true');
            li.append(icon, document.createTextNode(` ${label}`));
            return li;
        }));
    }

    function renderActions() {
        actions.replaceChildren();
        const s = status();
        const missing = incomplete();
        const hasTime = scheduleInput.value !== '';
        const scheduleChanged = s === 'scheduled' && toLocalInput(article.published_at) !== scheduleInput.value;

        const add = (label, cls, handler, disabled = false) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = `akd-admin-btn ${cls}`.trim();
            b.textContent = label;
            b.disabled = disabled || busy;
            b.addEventListener('click', () => handler(b));
            actions.appendChild(b);
        };

        if (s === 'published') {
            add('Save changes', 'akd-admin-btn--primary', savePublished, !hasPending() || missing);
        } else if (s === 'scheduled') {
            add('Unschedule', '', unschedule);
            add('Update schedule', '', (b) => schedule(b, 'Schedule updated.'), missing || !hasTime || !scheduleChanged);
            add('Publish now', 'akd-admin-btn--primary', publish, missing);
        } else {
            add(hasTime ? 'Schedule' : 'Schedule...', '', (b) => {
                if (hasTime) { schedule(b, 'Article scheduled.'); return; }
                scheduleInput.scrollIntoView({ block: 'center' });
                scheduleInput.focus();
            }, hasTime && missing);
            add('Publish', 'akd-admin-btn--primary', publish, missing);
        }
    }

    function updateUi() {
        if (busy) return;
        renderStatus();
        renderChecklist();
        renderActions();
    }

    function updateStats() {
        statsFrame = 0;
        const doc = editor.state.doc;
        let text = doc.textBetween(0, doc.content.size, ' ', ' ');
        doc.descendants((node) => {
            if (node.type.name === 'blogFigure' && node.attrs.caption) text += ` ${node.attrs.caption}`;
        });
        words = countWords(text);
        wordsEl.textContent = `${words.toLocaleString()} ${words === 1 ? 'word' : 'words'}`;
        readingEl.textContent = words ? `${readingMinutes(words)} min read` : 'No content yet';
        host.classList.toggle('is-empty', editor.isEmpty);
        updateUi();
    }

    function scheduleStats() {
        if (!statsFrame) statsFrame = requestAnimationFrame(updateStats);
    }

    function updateToolbar() {
        if (!editor) return;
        const active = {
            bold: editor.isActive('bold'), italic: editor.isActive('italic'), link: editor.isActive('link'),
            h2: editor.isActive('heading', { level: 2 }), h3: editor.isActive('heading', { level: 3 }),
            bullet: editor.isActive('bulletList'), ordered: editor.isActive('orderedList'), quote: editor.isActive('blockquote'),
        };

        toolbar.querySelectorAll('[data-cmd]').forEach((b) => {
            const cmd = b.dataset.cmd;
            if (cmd in active) b.setAttribute('aria-pressed', String(active[cmd]));
            if (cmd === 'undo') b.disabled = !editor.can().undo();
            if (cmd === 'redo') b.disabled = !editor.can().redo();
        });
    }

    // ---------------------------------------------------------------- autosave
    function touch() {
        updateUi();

        if (!isAutosaveable()) {
            setSave(hasPending() ? 'dirty' : 'clean');
            return;
        }

        if (fatal) return;
        setSave(hasPending() ? 'dirty' : (saveState === 'saved' ? 'saved' : 'clean'));
        if (!hasPending()) return;

        firstDirtyAt ||= Date.now();
        clearTimeout(autosaveTimer);
        const wait = Date.now() - firstDirtyAt > AUTOSAVE_MAX_WAIT ? 0 : AUTOSAVE_DELAY;
        autosaveTimer = setTimeout(() => { firstDirtyAt = 0; requestSave(); }, wait);
    }

    function showErrors(res) {
        const unmatched = [];

        Object.entries(res.errors || {}).forEach(([key, message]) => {
            if (fields[key]) setFieldError(fields[key], message);
            else unmatched.push(message);
        });

        if (!res.errors) unmatched.push(res.message || 'Could not save your changes.');
        if (unmatched.length) notifyError(unmatched[0]);
        focusFirstError(root);
    }

    function absorb(a, sent = {}) {
        article = pickArticle(a);
        savedAt = new Date();
        if (slugInput.value === (sent.slug ?? slugInput.value) || !slugTouched) slugInput.value = article.slug;
        saved.slug = article.slug;
        slugSuffix.textContent = `-${a.public_id}`;
        featuredSwitch.disabled = false;
        featuredHint.textContent = 'Shown on the Blog home once published. Up to 3 articles.';
    }

    async function saveOnce() {
        const { payload, keys, sent } = buildPayload();
        if (!keys.length) return;
        setSave('saving');

        try {
            const res = article
                ? await api(`${API}/${article.id}`, { method: 'PATCH', body: payload })
                : await api(API, { method: 'POST', body: payload });

            if (res.success === false) {
                showErrors(res);
                setSave('error');
                return;
            }

            keys.forEach((k) => { if (k === 'content_html') savedContentVersion = sent.content; else saved[k] = sent[k]; });
            absorb(res.article, sent);
            changed = true;
            setSave(hasPending() ? 'dirty' : 'saved');
            updateUi();
        } catch (err) {
            if (err.status === 404 || err.status === 409) {
                fatal = true;
                notifyError(err.data?.message || 'This article can no longer be saved here.');
            } else if (err.status !== 0) {
                handleApiError(err, 'Could not save the article.');
            }

            setSave('error');
        }
    }

    function requestSave() {
        if (saveChain) { saveAgain = true; return saveChain; }

        saveChain = (async () => {
            do {
                saveAgain = false;
                await saveOnce();
            } while (saveAgain && saveState !== 'error');
        })().finally(() => { saveChain = null; });

        return saveChain;
    }

    async function flush() {
        clearTimeout(autosaveTimer);
        firstDirtyAt = 0;
        if (!isAutosaveable() || fatal) return !hasPending();
        if (!hasPending() && !saveChain) return true;
        await requestSave();
        return saveState !== 'error' && !hasPending();
    }

    saveRetry.addEventListener('click', () => requestSave());
    const onOnline = () => { if (saveState === 'error' && !fatal) requestSave(); };

    // ---------------------------------------------------------------- lifecycle actions
    async function withBusy(task, button = null, label = 'Working...') {
        if (busy) return;
        busy = true;
        actions.querySelectorAll('button').forEach((b) => { b.disabled = true; });
        if (button) setLoading(button, label);

        try {
            await task();
        } catch (err) {
            handleApiError(err, 'Something went wrong.');
        } finally {
            busy = false;
            if (button?.isConnected) clearLoading(button);
            updateUi();
        }
    }

    function checkComplete() {
        const missing = missingList().filter(([, , ok]) => !ok);
        if (!missing.length) return true;
        missing.forEach(([key, label]) => fields[key] && setFieldError(fields[key], label));
        focusFirstError(root);
        return false;
    }

    async function savePublished(button) {
        if (!checkComplete()) return;

        await withBusy(async () => {
            const { payload, keys, sent } = buildPayload();
            if (!keys.length) return;
            const res = await api(`${API}/${article.id}`, { method: 'PUT', body: payload });

            if (res.success === false) { showErrors(res); setSave('error'); return; }

            keys.forEach((k) => { if (k === 'content_html') savedContentVersion = sent.content; else saved[k] = sent[k]; });
            absorb(res.article, sent);
            changed = true;
            committed = true;
            setSave('saved');
            success('Changes saved.');
            await modal.close();
        }, button, 'Saving...');
    }

    async function publish(button) {
        if (!checkComplete()) return;

        await withBusy(async () => {
            if (!(await flush()) || !article) { notifyError("Your latest changes couldn't be saved. Please try again."); return; }
            const res = await api(`${API}/${article.id}/publish`, { method: 'POST' });

            if (res.success === false) { showErrors(res); return; }

            article = pickArticle(res.article);
            changed = true;
            committed = true;
            success('Article published.');
            await modal.close();
        }, button, 'Publishing...');
    }

    async function schedule(button, message) {
        clearFieldError(fields.scheduled_at);
        const iso = localInputToIso(scheduleInput.value);

        if (!iso || new Date(iso).getTime() <= Date.now() + 60000) {
            setFieldError(fields.scheduled_at, 'Choose a time in the future.');
            scheduleInput.focus();
            return;
        }

        if (!checkComplete()) return;

        await withBusy(async () => {
            if (!(await flush()) || !article) { notifyError("Your latest changes couldn't be saved. Please try again."); return; }
            const res = await api(`${API}/${article.id}/schedule`, { method: 'POST', body: { scheduled_at: iso } });

            if (res.success === false) { showErrors(res); return; }

            article = pickArticle(res.article);
            changed = true;
            committed = true;
            success(message);
            await modal.close();
        }, button, 'Scheduling...');
    }

    async function unschedule(button) {
        await withBusy(async () => {
            clearTimeout(autosaveTimer);
            if (!(await flush())) { notifyError("Your latest changes couldn't be saved. Please try again."); return; }
            const res = await api(`${API}/${article.id}/unschedule`, { method: 'POST' });

            if (res.success === false) { showErrors(res); return; }

            article = pickArticle(res.article);
            scheduleInput.value = '';
            changed = true;
            committed = true;
            success('Moved back to drafts.');
            await modal.close();
        }, button, 'Working...');
    }

    // ---------------------------------------------------------------- featured (own endpoint, optimistic)
    featuredSwitch.checked = Boolean(article?.featured_position);
    featuredSwitch.disabled = !article;
    featuredHint.textContent = article
        ? 'Shown on the Blog home once published. Up to 3 articles.'
        : 'Available once the draft has been saved.';

    featuredSwitch.addEventListener('change', () => {
        const on = featuredSwitch.checked;

        optimistic({
            queue: featuredQueue,
            apply: () => !on,
            request: () => api(`${API}/${article.id}/featured`, { method: 'POST', body: { featured: on } }),
            commit: (res) => {
                const mine = (res.featured || []).find((a) => a.id === article.id);
                article.featured_position = mine ? mine.featured_position : null;
                changed = true;
            },
            rollback: (previous) => { featuredSwitch.checked = previous; },
            errorMessage: 'Could not change the featured state.',
        });
    });

    // ---------------------------------------------------------------- title, excerpt, slug
    function syncTitle() {
        const text = titleInput.value.replace(/\s*\n\s*/g, ' ');
        if (text !== titleInput.value) titleInput.value = text;
        mirror.innerHTML = `${escapeHtml(text).replace(ITALIC,
            '<span class="akd-be-mark">_</span><em>$1</em><span class="akd-be-mark">_</span>')}&#8203;`;
    }

    titleInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') e.preventDefault(); });
    titleInput.addEventListener('input', () => { syncTitle(); clearFieldError(fields.title); touch(); });

    function syncExcerptCounter() {
        const length = Array.from(excerptInput.value).length;
        excerptCounter.textContent = `${length} / 240`;
        excerptCounter.classList.toggle('is-over', length > 240);
    }

    excerptInput.addEventListener('input', () => { syncExcerptCounter(); clearFieldError(fields.excerpt); touch(); });
    coverAlt.addEventListener('input', () => { clearFieldError(fields.cover_image_alt); touch(); });
    scheduleInput.addEventListener('input', () => { clearFieldError(fields.scheduled_at); updateUi(); });

    slugInput.addEventListener('input', () => { slugTouched = true; clearFieldError(fields.slug); touch(); });
    slugInput.addEventListener('blur', () => {
        const raw = slugInput.value.trim();
        if (!raw) return;
        const clean = localSlug(raw);

        if (!clean) { setFieldError(fields.slug, 'Use letters, numbers and hyphens.'); return; }
        slugInput.value = clean;
        touch();
    });

    // ---------------------------------------------------------------- category + tags
    const categorySelect = initSelect($('[data-category]'), {
        options: categories.map((c) => ({ value: String(c.id), label: c.label })),
        value: saved.category_id,
        placeholder: 'Choose a category',
        title: 'Category',
        onChange: () => { clearFieldError(fields.category_id); touch(); },
    });

    function renderTags() {
        tagList.replaceChildren(...tags.map((name) => {
            const li = document.createElement('li');
            li.className = 'akd-be-tag';
            const label = document.createElement('span');
            label.textContent = name;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.setAttribute('aria-label', `Remove tag ${name}`);
            remove.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i>';
            remove.addEventListener('click', () => {
                tags = tags.filter((t) => t !== name);
                renderTags();
                touch();
            });
            li.append(label, remove);
            return li;
        }));
    }

    function addTag(raw) {
        const name = collapseSpaces(raw.replace(/,/g, ' '));
        clearFieldError(fields.tags);
        if (!name) return true;

        if (name.length < 2 || name.length > 30 || !TAG_PATTERN.test(name)) {
            setFieldError(fields.tags, 'Tags are 2 to 30 characters: letters, numbers, spaces and & + # . -');
            return false;
        }

        if (tags.some((t) => t.toLowerCase() === name.toLowerCase())) return true;

        if (tags.length >= MAX_TAGS) {
            setFieldError(fields.tags, `Use at most ${MAX_TAGS} tags.`);
            return false;
        }

        tags = [...tags, name];
        renderTags();
        touch();
        return true;
    }

    tagInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            if (addTag(tagInput.value)) tagInput.value = '';
        } else if (e.key === 'Backspace' && tagInput.value === '' && tags.length) {
            tags = tags.slice(0, -1);
            renderTags();
            touch();
        }
    });
    tagInput.addEventListener('blur', () => { if (addTag(tagInput.value)) tagInput.value = ''; });

    // ---------------------------------------------------------------- cover
    function renderCover() {
        coverPreview.replaceChildren();

        if (coverPath) {
            const img = document.createElement('img');
            img.alt = '';
            img.src = coverPath;
            img.addEventListener('error', () => {
                const icon = document.createElement('i');
                icon.className = 'fa-regular fa-image';
                icon.setAttribute('aria-hidden', 'true');
                img.replaceWith(icon);
            });
            coverPreview.appendChild(img);
        } else {
            coverPreview.innerHTML = '<i class="fa-regular fa-image" aria-hidden="true"></i>';
        }

        coverRemove.hidden = !coverPath;
        coverLabel.textContent = coverPath ? 'Replace image' : 'Choose image';
        fields.cover_image_alt.hidden = !coverPath;
    }

    $('[data-cover-choose]').addEventListener('click', () => coverInput.click());
    coverRemove.addEventListener('click', () => { coverPath = ''; coverAlt.value = ''; renderCover(); touch(); });

    coverInput.addEventListener('change', async () => {
        const file = coverInput.files?.[0];
        coverInput.value = '';
        if (!file) return;
        clearFieldError(fields.cover_image);
        const button = $('[data-cover-choose]');
        setLoading(button, 'Uploading...');

        try {
            coverPath = await uploadImage(file);
            renderCover();
            touch();
        } catch (err) {
            if (err.status !== undefined) handleApiError(err, 'Could not upload the image.');
            else setFieldError(fields.cover_image, err.message);
        } finally {
            clearLoading(button);
        }
    });

    // ---------------------------------------------------------------- Tiptap
    async function insertImageFile(file) {
        uploading += 1;
        const button = toolbar.querySelector('[data-cmd="image"]');
        button.disabled = true;

        try {
            const src = await uploadImage(file);
            editor.chain().focus().insertContent({ type: 'blogFigure', attrs: { src, alt: '', caption: '' } }).run();
        } catch (err) {
            if (err.status !== undefined) handleApiError(err, 'Could not upload the image.');
            else notifyError(err.message);
        } finally {
            uploading -= 1;
            if (!uploading) button.disabled = false;
        }
    }

    const imageFiles = (list) => Array.from(list || []).filter((f) => f.type.startsWith('image/'));

    editor = new Editor({
        element: host,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3] },
                link: { openOnClick: false, autolink: false, linkOnPaste: true, defaultProtocol: 'https',
                    HTMLAttributes: { rel: 'noopener noreferrer', target: '_blank' } },
                underline: false, strike: false, code: false, codeBlock: false, horizontalRule: false,
            }),
            BlogFigure,
        ],
        content: initial?.content_html || '',
        editorProps: {
            attributes: { class: 'akd-be-prose', role: 'textbox', 'aria-multiline': 'true', 'aria-labelledby': 'beBodyLabel' },
            handlePaste: (view, event) => {
                const files = imageFiles(event.clipboardData?.files);
                if (!files.length) return false;
                event.preventDefault();
                files.forEach(insertImageFile);
                return true;
            },
            handleDrop: (view, event, slice, moved) => {
                const files = moved ? [] : imageFiles(event.dataTransfer?.files);
                if (!files.length) return false;
                event.preventDefault();
                files.forEach(insertImageFile);
                return true;
            },
        },
        onUpdate: () => { contentVersion += 1; clearFieldError(fields.content_html); scheduleStats(); touch(); },
        onTransaction: updateToolbar,
    });

    TOOLS.forEach(([cmd, icon, label]) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'akd-be-tool';
        b.dataset.cmd = cmd;
        b.setAttribute('aria-label', label);
        b.title = label;
        if (icon) b.innerHTML = `<i class="${icon}" aria-hidden="true"></i>`;
        else b.innerHTML = `<span class="akd-be-tool__text" aria-hidden="true">${cmd.toUpperCase()}</span>`;
        if (['bold', 'italic', 'link', 'h2', 'h3', 'bullet', 'ordered', 'quote'].includes(cmd)) b.setAttribute('aria-pressed', 'false');
        toolbar.appendChild(b);
    });

    function openLinkBar() {
        const existing = editor.getAttributes('link').href || '';
        linkInput.value = existing;
        linkError.textContent = '';
        linkRemove.hidden = !existing;
        linkBar.hidden = false;
        linkInput.focus();
        linkInput.select();
    }

    function closeLinkBar() {
        linkBar.hidden = true;
        editor.chain().focus().run();
    }

    linkBar.addEventListener('submit', (e) => {
        e.preventDefault();
        const href = normalizeHref(linkInput.value);

        if (!href) {
            linkError.textContent = 'Enter a web address, an email (mailto:), or a /path.';
            return;
        }

        if (editor.state.selection.empty && !editor.isActive('link')) {
            editor.chain().focus().insertContent({ type: 'text', text: href, marks: [{ type: 'link', attrs: { href } }] }).run();
        } else {
            editor.chain().focus().extendMarkRange('link').setLink({ href }).run();
        }

        linkBar.hidden = true;
    });
    linkRemove.addEventListener('click', () => { editor.chain().focus().extendMarkRange('link').unsetLink().run(); linkBar.hidden = true; });
    $('[data-link-cancel]').addEventListener('click', closeLinkBar);
    linkInput.addEventListener('keydown', (e) => { if (e.key === 'Escape') { e.stopPropagation(); closeLinkBar(); } });

    const commands = {
        bold: () => editor.chain().focus().toggleBold().run(),
        italic: () => editor.chain().focus().toggleItalic().run(),
        h2: () => editor.chain().focus().toggleHeading({ level: 2 }).run(),
        h3: () => editor.chain().focus().toggleHeading({ level: 3 }).run(),
        bullet: () => editor.chain().focus().toggleBulletList().run(),
        ordered: () => editor.chain().focus().toggleOrderedList().run(),
        quote: () => editor.chain().focus().toggleBlockquote().run(),
        undo: () => editor.chain().focus().undo().run(),
        redo: () => editor.chain().focus().redo().run(),
        link: openLinkBar,
        image: () => imageInput.click(),
    };

    toolbar.addEventListener('mousedown', (e) => { if (e.target.closest('[data-cmd]')) e.preventDefault(); });
    toolbar.addEventListener('click', (e) => {
        const b = e.target.closest('[data-cmd]');
        if (b && !b.disabled) commands[b.dataset.cmd]?.();
    });
    imageInput.addEventListener('change', () => {
        const files = imageFiles(imageInput.files);
        imageInput.value = '';
        files.forEach(insertImageFile);
    });

    // ---------------------------------------------------------------- page-level guards
    const onBeforeUnload = (e) => {
        if (hasPending() || saveChain) { e.preventDefault(); e.returnValue = ''; }
    };
    const onVisibility = () => {
        if (document.visibilityState === 'hidden' && isAutosaveable() && hasPending()) requestSave();
    };
    const onKey = (e) => {
        if (!root.isConnected || !(e.ctrlKey || e.metaKey) || e.key.toLowerCase() !== 's') return;
        e.preventDefault();
        if (status() === 'published') { if (!incomplete() && hasPending()) savePublished(null); return; }

        flush().then((ok) => { if (ok && article) success('Saved.'); });
    };

    window.addEventListener('beforeunload', onBeforeUnload);
    window.addEventListener('online', onOnline);
    document.addEventListener('visibilitychange', onVisibility);
    document.addEventListener('keydown', onKey);

    function finish() {
        clearTimeout(autosaveTimer);
        cancelAnimationFrame(statsFrame);
        window.removeEventListener('beforeunload', onBeforeUnload);
        window.removeEventListener('online', onOnline);
        document.removeEventListener('visibilitychange', onVisibility);
        document.removeEventListener('keydown', onKey);
        editor?.destroy();
        editor = null;
        if (changed) document.dispatchEvent(new CustomEvent(BLOG_CHANGED_EVENT));
    }

    // ---------------------------------------------------------------- initial paint
    titleInput.value = saved.title;
    excerptInput.value = saved.excerpt;
    coverAlt.value = saved.cover_image_alt;
    slugInput.value = saved.slug;
    slugSuffix.textContent = article ? `-${initial.public_id}` : '-xxxxxxxx';
    scheduleInput.min = toLocalInput(new Date(Date.now() + 60000).toISOString());
    scheduleInput.value = article?.status === 'scheduled' ? toLocalInput(article.published_at) : '';
    syncTitle();
    syncExcerptCounter();
    renderTags();
    renderCover();
    updateStats();
    renderSaveStatus();
    updateToolbar();

    modal.open({
        title: article ? 'Edit article' : 'New article',
        subtitle: 'Drafts and scheduled articles save automatically as you write.',
        content: root,
        footer: footerRoot,
        size: 'default',
        className: 'akd-be-modal',
        initialFocus: titleInput,
        beforeClose: async () => {
            if (committed) { finish(); return true; }
            if (busy) return false;
            clearTimeout(autosaveTimer);

            if (isAutosaveable()) {
                if (hasPending()) await requestSave();

                if (hasPending() || saveState === 'error') {
                    const leave = await confirmDialog.ask({
                        title: "Couldn't save your latest changes",
                        message: 'Closing now may lose them. Close anyway?',
                        confirmLabel: 'Close anyway',
                        cancelLabel: 'Keep editing',
                        destructive: true,
                    });
                    if (!leave) return false;
                }
            } else if (hasPending()) {
                const leave = await confirmDialog.ask({
                    title: 'Discard changes?',
                    message: 'This article is published, so edits are only saved when you press Save changes.',
                    confirmLabel: 'Discard',
                    cancelLabel: 'Continue Editing',
                    destructive: true,
                });
                if (!leave) return false;
            }

            finish();
            return true;
        },
    });
}