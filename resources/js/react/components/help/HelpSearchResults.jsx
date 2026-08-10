import HelpArticleRow from "./HelpArticleRow";

export default function HelpSearchResults({ query, results, onSelectArticle }) {
    return (
        <div className="akd-help-results" role="region" aria-label="Search results">
            <p className="akd-help-results__label">Search results</p>

            {results.length === 0 ? (
                <div className="akd-help-results__empty">
                    <i className="fa-solid fa-magnifying-glass" aria-hidden="true" />
                    <p>No articles found for &ldquo;{query}&rdquo;</p>
                    <span>Try a different search term or browse categories below.</span>
                </div>
            ) : (
                <ul className="akd-help-results__list">
                    {results.map((article) => (
                        <li key={article.id}>
                            <HelpArticleRow article={article} onSelect={onSelectArticle} />
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}