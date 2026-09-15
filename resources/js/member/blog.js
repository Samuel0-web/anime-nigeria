// Instant client-side search over the mock article dataset embedded in
// the page. No network request yet — swap readDataset() for a fetch to
// /member/blog/search once that route returns JSON instead of a page.

function readDataset() {
    const node = document.getElementById('akdBlogDataset');
    if (!node) return [];

    try {
        return JSON.parse(node.textContent);
    } catch (err) {
        console.error('Could not parse blog dataset', err);
        return [];
    }
}

function matches(article, query) {
    const haystack = [
        article.title,
        article.excerpt,
        article.category,
        ...(article.tags || []),
    ].join(' ').toLowerCase();

    return haystack.includes(query);
}

function renderResults(container, articles, query) {
    container.innerHTML = '';

    if (!articles.length) {
        const empty = document.createElement('div');
        empty.className = 'akd-blog-search__empty';
        empty.textContent = `No articles match "${query}".`;
        container.appendChild(empty);
        return;
    }

    articles.slice(0, 6).forEach((article) => {
        const link = document.createElement('a');
        link.className = 'akd-blog-search__result';
        link.href = `/member/blog/post/${encodeURIComponent(article.slug)}`;
        link.setAttribute('role', 'option');

        const category = document.createElement('span');
        category.className = 'akd-blog-search__result-category';
        category.textContent = article.category;

        const title = document.createElement('span');
        title.className = 'akd-blog-search__result-title';
        title.textContent = article.title;

        link.append(category, title);
        container.appendChild(link);
    });

    const viewAll = document.createElement('a');
    viewAll.className = 'akd-blog-search__viewall';
    viewAll.href = `/member/blog/search?q=${encodeURIComponent(query)}`;
    viewAll.textContent = `View all results for "${query}"`;
    container.appendChild(viewAll);
}

export function initBlogSearch() {
    const root = document.querySelector('[data-blog-search]');
    if (!root) return;

    const input = root.querySelector('[data-blog-search-input]');
    const clearBtn = root.querySelector('[data-blog-search-clear]');
    const results = root.querySelector('[data-blog-search-results]');

    if (!input || !results) return;

    const dataset = readDataset();
    let debounceTimer = null;

    const close = () => {
        results.classList.remove('is-open');
        results.hidden = true;
    };

    const runSearch = () => {
        const query = input.value.trim().toLowerCase();
        clearBtn.hidden = query.length === 0;

        if (!query) {
            close();
            return;
        }

        const matched = dataset.filter((article) => matches(article, query));
        renderResults(results, matched, input.value.trim());
        results.hidden = false;
        results.classList.add('is-open');
    };

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(runSearch, 150);
    });

    input.addEventListener('focus', () => {
        if (input.value.trim()) {
            results.hidden = false;
            results.classList.add('is-open');
        }
    });

    clearBtn.addEventListener('click', () => {
        input.value = '';
        clearBtn.hidden = true;
        close();
        input.focus();
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            close();
        }
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
            input.blur();
        }
    });
}

export function initBlogCardImages() {
    document.querySelectorAll('.akd-blog-card__media').forEach((media) => {
        const img = media.querySelector('.akd-blog-card__image');
        if (!img) return;

        const markLoaded = () => media.classList.add('is-loaded');

        if (img.complete && img.naturalWidth > 0) {
            markLoaded();
            return;
        }

        img.addEventListener('load', markLoaded);
        img.addEventListener('error', () => img.remove());
    });
}