import { getCategoriesWithCounts } from "../../data/help/helpData";

export default function HelpCategories({ onSelectCategory }) {
    const categories = getCategoriesWithCounts();

    return (
        <section className="akd-help-categories">
            <h2 className="akd-help-section-title">Browse by category</h2>
            <div className="akd-help-categories__grid">
                {categories.map((category) => (
                    <button key={category.id} type="button" className="akd-help-category-card"
                        onClick={() => onSelectCategory(category.slug)}
                    >
                        <span className="akd-help-category-card__icon">
                            <i className={`fa-solid ${category.icon}`} aria-hidden="true" />
                        </span>

                        <span className="akd-help-category-card__name">{category.name}</span>

                        <span className="akd-help-category-card__description">
                            {category.description}
                        </span>

                        <span className="akd-help-category-card__count">
                            {category.articleCount}
                            &nbsp;{category.articleCount === 1 ? "article" : "articles"}
                        </span>
                    </button>
                ))}
            </div>
        </section>
    );
}