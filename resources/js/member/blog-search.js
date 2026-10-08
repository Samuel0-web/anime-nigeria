import { api } from '../modules/api';

const MIN_CHARS = 2;
const DEBOUNCE_MS = 200;

// Instant search dropdown. Every keystroke group asks the database; nothing is filtered locally.
export function initBlogSearch() {
    const root = document.querySelector('[data-blog-search]');
    const input = root?.querySelector('[data-blog-search-input]');
    const results = root?.querySelector('[data-blog-search-results]');
    const clear = root?.querySelector('[data-blog-search-clear]');

    if (!root || !input || !results) {
        return;
    }

    const endpoint = root.dataset.endpoint;
    const searchPage = root.dataset.searchPage;
    let timer = null;
    let controller = null;
    let seq = 0;

    const searchUrl = (query) => `${searchPage}?q=${encodeURIComponent(query)}`;

    function close() {
        results.hidden = true;
        results.replaceChildren();
    }

    function render(items, query) {
        results.replaceChildren();

        if (!items.length) {
            const empty = document.createElement('p');
            empty.className = 'akd-blog-search__empty';
            empty.textContent = `No articles match "${query}".`;
            results.append(empty);
        } else {
            items.forEach((item) => {
                const link = document.createElement('a');
                link.className = 'akd-blog-search__item';
                link.href = item.url;
                link.setAttribute('role', 'option');

                const title = document.createElement('span');
                title.className = 'akd-blog-search__item-title';
                title.textContent = item.title; // textContent: never parsed as HTML

                const meta = document.createElement('span');
                meta.className = 'akd-blog-search__item-meta';
                meta.textContent = item.category;

                link.append(title, meta);
                results.append(link);
            });

            const all = document.createElement('a');
            all.className = 'akd-blog-search__item akd-blog-search__item--all';
            all.href = searchUrl(query);
            all.textContent = 'See all results';
            results.append(all);
        }

        results.hidden = false;
    }

    async function run(query) {
        controller?.abort();
        controller = new AbortController();
        const mine = ++seq;

        try {
            const result = await api(`${endpoint}?q=${encodeURIComponent(query)}`, { signal: controller.signal });

            if (mine === seq) {
                render(result.items || [], query);
            }
        } catch {
            if (mine === seq) {
                close(); // aborted or failed: stay quiet, Enter still goes to the full results page
            }
        }
    }

    input.addEventListener('input', () => {
        const query = input.value.trim();
        clear.hidden = input.value === '';
        window.clearTimeout(timer);

        if (query.length < MIN_CHARS) {
            controller?.abort();
            seq += 1;
            close();
            return;
        }

        timer = window.setTimeout(() => run(query), DEBOUNCE_MS);
    });

    input.addEventListener('keydown', (event) => {
        const query = input.value.trim();

        if (event.key === 'Enter' && query !== '') {
            window.location.assign(searchUrl(query));
        } else if (event.key === 'Escape') {
            close();
        }
    });

    clear.addEventListener('click', () => {
        input.value = '';
        clear.hidden = true;
        close();
        input.focus();
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            close();
        }
    });
}