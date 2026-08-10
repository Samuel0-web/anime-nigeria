export default function HelpArticleRow({ article, onSelect, showCategory = true }) {
    return (
        <button type="button" className="akd-help-result" onClick={() => onSelect(article.id)}>
            <span className="akd-help-result__text">
                <span className="akd-help-result__title">{article.title}</span>
                {showCategory && (
                    <span className="akd-help-result__category">{article.category?.name}</span>
                )}
            </span>
            <i className="fa-solid fa-chevron-right" aria-hidden="true" />
        </button>
    );
}