import { useCallback, useEffect, useMemo, useState } from "react";
import HelpSearch from "../components/help/HelpSearch";
import HelpSearchResults from "../components/help/HelpSearchResults";
import PopularArticles from "../components/help/PopularArticles";
import HelpCategories from "../components/help/HelpCategories";
import HelpCategoryView from "../components/help/HelpCategoryView";
import HelpPanel from "../components/help/HelpPanel";
import HelpArticleViewer from "../components/help/HelpArticleViewer";
import BugReportForm from "../components/help/BugReportForm";
import { getArticleById, getPopularArticles, searchArticles } from "../data/help/helpData";
import { clearBugReportParams, readBugReportIntent } from "../utils/help/bugReportIntent";

export default function HelpCentre() {
    const [query, setQuery] = useState("");
    const [categorySlug, setCategorySlug] = useState(null);
    const [activeArticleId, setActiveArticleId] = useState(null);

    // Read the URL once on mount. It's the requested *initial* state, not a source of truth.
    const [bugIntent] = useState(readBugReportIntent);
    const [bugReportOpen, setBugReportOpen] = useState(bugIntent.open);
    const [bugReportPage, setBugReportPage] = useState(bugIntent.page);
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

    // Manual opens carry no originating-page context.
    const openBugReport = useCallback(() => {
        setBugReportPage("");
        setBugReportOpen(true);
    }, []);

    // Consume ?report=bug&from=... after handling it (safe if run twice in StrictMode).
    useEffect(() => {
        if (bugIntent.open) clearBugReportParams();
    }, [bugIntent.open]);

    // The global PHP footer's "Report a Bug" is a normal link (works without JS).
    // On this page, open the panel in place instead of reloading.
    useEffect(() => {
        const handleClick = (event) => {
            if (event.defaultPrevented || event.button !== 0) return;
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

            const link = event.target.closest?.("[data-bug-report-link]");
            if (!link) return;

            event.preventDefault();
            openBugReport();
        };

        document.addEventListener("click", handleClick);
        return () => document.removeEventListener("click", handleClick);
    }, [openBugReport]);

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
                            Can&rsquo;t find what you&rsquo;re looking for? Use Contact or
                            Report a Bug in the footer below.
                        </p>
                    </section>
                </div>
            )}

            <HelpPanel open={panelOpen} onClose={closePanel}
                title={bugReportOpen ? "Report a Bug" : activeArticle?.title ?? ""}
            >
                {bugReportOpen && (
                    <BugReportForm onDone={closePanel} initialPage={bugReportPage} />
                )}
                
                {activeArticle && !bugReportOpen && <HelpArticleViewer article={activeArticle} />}
            </HelpPanel>
        </div>
    );
}