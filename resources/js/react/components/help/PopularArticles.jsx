import HelpArticleRow from "./HelpArticleRow";

export default function PopularArticles({ articles, onSelectArticle }) {
    if (articles.length === 0) return null;

    return (
        <section className="akd-help-popular">
            <h2 className="akd-help-section-title">Popular Articles</h2>
            <ul className="akd-help-popular__list">
                {articles.map((article) => (
                    <li key={article.id}>
                        <HelpArticleRow article={article} onSelect={onSelectArticle} />
                    </li>
                ))}
            </ul>
        </section>
    );
}