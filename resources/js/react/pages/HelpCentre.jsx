import { useMemo, useState } from "react";
import HelpSearch from "../components/help/HelpSearch";
import HelpSearchResults from "../components/help/HelpSearchResults";
import PopularArticles from "../components/help/PopularArticles";
import HelpCategories from "../components/help/HelpCategories";
import HelpCategoryView from "../components/help/HelpCategoryView";
import HelpPanel from "../components/help/HelpPanel";
import HelpArticleViewer from "../components/help/HelpArticleViewer";
import BugReportForm from "../components/help/BugReportForm";
import { getArticleById, getPopularArticles, searchArticles } from "../data/help/helpData";

export default function HelpCentre() {
    const [query, setQuery] = useState("");
    const [categorySlug, setCategorySlug] = useState(null);
    const [activeArticleId, setActiveArticleId] = useState(null);
    const [bugReportOpen, setBugReportOpen] = useState(false);
    const popularArticles = useMemo(() => getPopularArticles(), []);

    const searchResults = useMemo(
        () => (query.trim() ? searchArticles(query) : []),
        [query]
    );

    const activeArticle = useMemo(
        () => (activeArticleId ? getArticleById(activeArticleId) : null),
        [activeArticleId]
    );

    const isSearching = query.trim().length > 0;
    const panelOpen = Boolean(activeArticleId) || bugReportOpen;
    const openArticle = (id) => setActiveArticleId(id);

    const closePanel = () => {
        setActiveArticleId(null);
        setBugReportOpen(false);
    };

    return (
        <div className="akd-help">
            <section className="akd-help__hero">
                <h1 className="akd-help__title">Help Centre</h1>
                <p className="akd-help__subtitle">
                    Find answers, learn how things work, or get help with a problem.
                </p>

                <HelpSearch value={query} onChange={setQuery} />

                {isSearching && (
                    <HelpSearchResults query={query} results={searchResults}
                        onSelectArticle={openArticle}
                    />
                )}
            </section>

            {!isSearching && (
                <div className="akd-help__body">
                    {categorySlug ? (
                        <HelpCategoryView slug={categorySlug} onBack={() => setCategorySlug(null)}
                            onSelectArticle={openArticle}
                        />
                    ) : (
                        <>
                            <PopularArticles articles={popularArticles}
                                onSelectArticle={openArticle}
                            />

                            <HelpCategories onSelectCategory={setCategorySlug} />
                        </>
                    )}

                    <section className="akd-help__support">
                        <h2 className="akd-help__support-title">Still need help?</h2>
                        
                        <p className="akd-help__support-text">
                            Can't find what you're looking for?
                        </p>
                        <div className="akd-help__support-actions">
                            <a href="/contact" className="akd-help-btn akd-help-btn--secondary"
                                target="_blank" rel="noopener noreferrer"
                            >
                                Contact Support
                            </a>
                            <button
                                type="button"
                                className="akd-help-btn akd-help-btn--ghost"
                                onClick={() => setBugReportOpen(true)}
                            >
                                Report a Bug
                            </button>
                        </div>
                    </section>

                    <footer className="akd-help__footer">
                        <a href="/privacy" target="_blank" rel="noopener noreferrer">
                            Privacy Policy
                        </a>
                        <span aria-hidden="true">·</span>
                        <a href="/terms" target="_blank" rel="noopener noreferrer">
                            Terms of Use
                        </a>
                    </footer>
                </div>
            )}

            <HelpPanel
                open={panelOpen}
                onClose={closePanel}
                title={bugReportOpen ? "Report a Bug" : activeArticle?.title ?? ""}
            >
                {bugReportOpen && <BugReportForm onDone={closePanel} />}
                {activeArticle && !bugReportOpen && <HelpArticleViewer article={activeArticle} />}
            </HelpPanel>
        </div>
    );
}