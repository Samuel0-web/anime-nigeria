// resources/js/admin/blog-figure.js
// An image with optional caption: the one custom node. It serialises to exactly what
// BlogHtml accepts: <figure><img src alt><figcaption>...</figcaption></figure>.
import { Node } from '@tiptap/core';

function field(placeholder, maxLength) {
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'akd-be-figure__input';
    input.placeholder = placeholder;
    input.maxLength = maxLength;
    input.autocomplete = 'off';
    input.setAttribute('aria-label', placeholder);
    return input;
}

export const BlogFigure = Node.create({
    name: 'blogFigure',
    group: 'block',
    atom: true,
    draggable: true,
    selectable: true,

    addAttributes() {
        return { src: { default: null }, alt: { default: '' }, caption: { default: '' } };
    },

    parseHTML() {
        return [
            {
                tag: 'figure',
                getAttrs: (el) => {
                    const img = el.querySelector('img');
                    if (!img || !img.getAttribute('src')) return false;

                    return {
                        src: img.getAttribute('src'),
                        alt: img.getAttribute('alt') || '',
                        caption: (el.querySelector('figcaption')?.textContent || '').trim(),
                    };
                },
            },
            {
                tag: 'img[src]',
                getAttrs: (el) => ({ src: el.getAttribute('src'), alt: el.getAttribute('alt') || '', caption: '' }),
            },
        ];
    },

    renderHTML({ node }) {
        const { src, alt, caption } = node.attrs;
        const img = ['img', { src, alt: alt || '' }];

        return caption ? ['figure', {}, img, ['figcaption', {}, caption]] : ['figure', {}, img];
    },

    addNodeView() {
        return ({ node: initial, getPos, editor }) => {
            let node = initial;

            const dom = document.createElement('figure');
            dom.className = 'akd-be-figure';

            const img = document.createElement('img');
            img.className = 'akd-be-figure__image';
            img.draggable = false;

            const fields = document.createElement('div');
            fields.className = 'akd-be-figure__fields';
            const alt = field('Alt text: describe the image', 200);
            const caption = field('Caption (optional)', 300);
            fields.append(alt, caption);
            dom.append(img, fields);

            const write = (name, value) => {
                const pos = getPos();
                if (typeof pos !== 'number') return;
                editor.view.dispatch(editor.state.tr.setNodeMarkup(pos, undefined, { ...node.attrs, [name]: value }));
            };

            function sync() {
                if (img.getAttribute('src') !== node.attrs.src) {
                    dom.classList.remove('is-broken');
                    img.src = node.attrs.src;
                }

                img.alt = node.attrs.alt || '';
                if (alt.value !== (node.attrs.alt || '')) alt.value = node.attrs.alt || '';
                if (caption.value !== (node.attrs.caption || '')) caption.value = node.attrs.caption || '';
                dom.classList.toggle('needs-alt', !node.attrs.alt);
            }

            alt.addEventListener('input', () => write('alt', alt.value));
            caption.addEventListener('input', () => write('caption', caption.value));
            img.addEventListener('error', () => dom.classList.add('is-broken'));
            sync();

            return {
                dom,
                update(next) {
                    if (next.type !== node.type) return false;
                    node = next;
                    sync();
                    return true;
                },
                stopEvent: (event) => fields.contains(event.target),
                ignoreMutation: () => true,
            };
        };
    },
});