import { categories } from "./categories";
import { articles } from "./articles";

function normalize(value) {
    return value.toLowerCase().trim();
}

export function getCategoryBySlug(slug) {
    return categories.find((category) => category.slug === slug) ?? null;
}

/** Attaches the resolved category object to an article. */
export function withCategory(article) {
    return { ...article, category: getCategoryBySlug(article.categorySlug) };
}

export function getCategoriesWithCounts() {
    return categories.map((category) => ({
        ...category,
        articleCount: articles.filter((a) => a.categorySlug === category.slug).length,
    }));
}

export function getPopularArticles(limit = 5) {
    return articles.filter((a) => a.popular).slice(0, limit).map(withCategory);
}

export function getArticleById(id) {
    const article = articles.find((a) => a.id === id);
    return article ? withCategory(article) : null;
}

/**
 * Returns a category's articles grouped by their optional `section` field.
 * Articles without a section are grouped under a single unnamed section.
 */
export function getArticlesByCategory(slug) {
    const category = getCategoryBySlug(slug);
    const categoryArticles = articles.filter((a) => a.categorySlug === slug);

    const grouped = new Map();
    categoryArticles.forEach((article) => {
        const key = article.section ?? "";
        if (!grouped.has(key)) grouped.set(key, []);
        grouped.get(key).push(article);
    });

    return {
        category,
        sections: Array.from(grouped.entries()).map(([name, items]) => ({
            name: name || null,
            articles: items,
        })),
    };
}

export function searchArticles(query, limit = 8) {
    const term = normalize(query);
    if (!term) return [];

    const weighted = [
        { field: (a) => a.title, weight: 3 },
        { field: (a) => a.description, weight: 2 },
        { field: (a) => getCategoryBySlug(a.categorySlug)?.name ?? "", weight: 1 },
        { field: (a) => a.tags.join(" "), weight: 1 },
    ];

    return articles
        .map((article) => {
            const score = weighted.reduce((total, { field, weight }) => {
                return normalize(field(article)).includes(term) ? total + weight : total;
            }, 0);
            return { article, score };
        })
        .filter(({ score }) => score > 0)
        .sort((a, b) => b.score - a.score)
        .slice(0, limit)
        .map(({ article }) => withCategory(article));
}