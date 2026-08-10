import HelpFeedback from "./HelpFeedback";

function ArticleBlock({ block }) {
    switch (block.type) {
        case "heading":
            return <h3 className="akd-article__heading">{block.text}</h3>;

        case "paragraph":
            return <p className="akd-article__paragraph">{block.text}</p>;

        case "steps":
            return (
                <ol className="akd-article__steps">
                    {block.items.map((item, index) => (
                        <li key={index}>{item}</li>
                    ))}
                </ol>
            );

        case "list":
            return (
                <ul className="akd-article__list">
                    {block.items.map((item, index) => (
                        <li key={index}>{item}</li>
                    ))}
                </ul>
            );

        case "note":
            return (
                <div className="akd-article__note">
                    <i className="fa-solid fa-lightbulb" aria-hidden="true" />
                    <p>{block.text}</p>
                </div>
            );

        case "link":
            return (
                <a
                    className="akd-article__link"
                    href={block.href}
                    target={block.external ? "_blank" : undefined}
                    rel={block.external ? "noopener noreferrer" : undefined}
                >
                    {block.text}
                    <i className="fa-solid fa-arrow-up-right-from-square" aria-hidden="true" />
                </a>
            );

        default:
            return null;
    }
}

export default function HelpArticleViewer({ article }) {
    if (!article) return null;

    return (
        <div className="akd-article">
            <p className="akd-article__category">{article.category?.name}</p>
            {article.description && (
                <p className="akd-article__description">{article.description}</p>
            )}

            <div className="akd-article__content">
                {article.content.map((block, index) => (
                    <ArticleBlock key={index} block={block} />
                ))}
            </div>

            <HelpFeedback articleId={article.id} />
        </div>
    );
}