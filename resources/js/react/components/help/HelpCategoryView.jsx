import { useEffect } from "react";
import { getArticlesByCategory, withCategory } from "../../data/help/helpData";
import HelpArticleRow from "./HelpArticleRow";

export default function HelpCategoryView({ slug, onBack, onSelectArticle }) {
    const { category, sections } = getArticlesByCategory(slug);

    useEffect(() => {
        window.scrollTo(0, 120);
    }, [slug]);

    if (!category) return null;

    return (
        <section className="akd-help-category-view">
            <button type="button" className="akd-help-category-view__back" onClick={onBack}>
                <i className="fa-solid fa-arrow-left" aria-hidden="true" />
                Help Centre
            </button>

            <h2 className="akd-help-category-view__title">{category.name}</h2>
            <p className="akd-help-category-view__description">{category.description}</p>

            {sections.map((section) => (
                <div className="akd-help-category-view__section" key={section.name ?? "general"}>
                    {section.name && (
                        <h3 className="akd-help-category-view__section-title">{section.name}</h3>
                    )}
                    <ul className="akd-help-category-view__list">
                        {section.articles.map((article) => (
                            <li key={article.id}>
                                <HelpArticleRow article={withCategory(article)}
                                    onSelect={onSelectArticle} showCategory={false}
                                />
                            </li>
                        ))}
                    </ul>
                </div>
            ))}
        </section>
    );
}