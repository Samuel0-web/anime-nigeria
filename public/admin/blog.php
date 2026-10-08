<?php
use App\Core\Logger;
use App\Security\Nonce;
use App\Services\BlogAdminService;
use App\Services\BlogBannedWordService;

$page_title       = 'Blog';
$page_description = 'Write, schedule and publish articles, and manage categories and comment moderation.';

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/home'],
    ['label' => 'Blog', 'url' => null],
];

require_once __DIR__ . '/partials/header.php';

// Moderators write and curate; only admins archive, restore and delete. The API enforces this too.
$canAdmin = ($user['role'] ?? '') === 'admin';

$blogBoot = [
    'canAdmin'    => $canAdmin,
    'pageSize'    => BlogAdminService::ADMIN_PAGE_SIZE,
    'maxFeatured' => BlogAdminService::MAX_FEATURED,
    'loadError'   => false,
    'articles'    => ['items' => [], 'has_more' => false,
    'counts'      => ['draft' => 0, 'scheduled' => 0, 'published' => 0, 'archived' => 0]],
    'categories'  => [],
    'words'       => [],
    'featured'    => [],
];

try {
    $blogAdmin = BlogAdminService::make();
    $list = $blogAdmin->list([]);
    $blogBoot['articles'] = ['items' => $list['items'], 'has_more' => $list['has_more'], 'counts' => $list['counts']];
    $blogBoot['categories'] = $blogAdmin->listCategories();
    $blogBoot['words'] = BlogBannedWordService::make()->list();
    $blogBoot['featured'] = $blogAdmin->featuredList();
} catch (\Throwable $e) {
    Logger::error($e);
    $blogBoot['loadError'] = true;
}

if (!function_exists('akd_admin_blog_list')) {
    /** Static shell for one list. The JS owns rows, counts, selection, empty and error copy. */
    function akd_admin_blog_list(bool $canAdmin, string $noun, array $headLabels, bool $paged): void {
        ?>
        <div class="akd-bl-listwrap">
            <div class="akd-bl-head" data-list-head hidden>
                <?php if ($canAdmin): ?>
                    <label class="akd-ann-check">
                        <input type="checkbox" data-select-all aria-label="Select all <?= $noun ?> shown">
                        <span class="akd-ann-check__box" aria-hidden="true">
                            <i class="fa-solid fa-check"></i><i class="fa-solid fa-minus"></i>
                        </span>
                    </label>
                <?php endif; ?>
                <span class="akd-bl-head__title">
                    <span class="akd-bl-head__narrow">Select all</span>
                    <span class="akd-bl-head__wide"><?= htmlspecialchars($headLabels[0]) ?></span>
                </span>
                <?php foreach (array_slice($headLabels, 1) as $label): ?>
                    <span class="akd-bl-head__col"><?= htmlspecialchars($label) ?></span>
                <?php endforeach; ?>
                <span class="akd-bl-head__col" aria-hidden="true"></span>
            </div>

            <ul class="akd-bl-list" role="list" data-list></ul>

            <div class="akd-ann-skeleton" data-skeleton aria-hidden="true">
                <?php for ($i = 0; $i < 3; $i++): ?>
                    <div class="akd-ann-skeleton__row">
                        <span class="akd-ann-skeleton__thumb"></span>
                        <span class="akd-ann-skeleton__lines"><span></span><span></span></span>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="akd-ann-state" data-empty hidden>
                <span class="akd-ann-state__icon" aria-hidden="true"><i class="fa-solid" data-empty-icon></i></span>
                <h3 class="akd-ann-state__title" data-empty-title></h3>
                <p class="akd-ann-state__text" data-empty-text></p>
                <button type="button" class="akd-admin-btn akd-admin-btn--primary" data-empty-action></button>
            </div>

            <div class="akd-ann-state akd-ann-state--error" data-error role="alert" hidden>
                <span class="akd-ann-state__icon" aria-hidden="true"><i class="fa-solid fa-circle-exclamation"></i></span>
                <h3 class="akd-ann-state__title">Couldn't load <?= $noun ?></h3>
                <p class="akd-ann-state__text">Check your connection and try again.</p>
                <button type="button" class="akd-admin-btn" data-action="retry">
                    <i class="fa-solid fa-rotate-right" aria-hidden="true"></i> Try again
                </button>
            </div>

            <?php if ($paged): ?>
                <div class="akd-ann-more" data-more hidden>
                    <button type="button" class="akd-admin-btn" data-action="load-more">Show more</button>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

if (!function_exists('akd_admin_blog_search')) {
    function akd_admin_blog_search(string $placeholder): void {
        ?>
        <div class="akd-bl-search">
            <i class="fa-solid fa-magnifying-glass akd-bl-search__icon" aria-hidden="true"></i>

            <input type="text" inputmode="search" enterkeyhint="search" role="searchbox"
                class="akd-admin-field__input akd-bl-search__input" data-search
                placeholder="<?= htmlspecialchars($placeholder) ?>" 
                aria-label="<?= htmlspecialchars($placeholder) ?>" autocomplete="off" 
                maxlength="100"
            >

            <button type="button" class="akd-bl-search__clear" data-search-clear aria-label="Clear search" hidden>
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>
        <?php
    }
}

$blogTabs = [
    'blog'       => ['Blog', true],
    'categories' => ['Categories', true],
    'words'      => ['Banned Words', true],
    'archive'    => ['Archive', $canAdmin],
];
?>

<main class="akd-content">
    <?php require __DIR__ . '/partials/page-header.php'; ?>

    <div class="akd-bl<?= $canAdmin ? '' : ' akd-bl--no-bulk' ?>" id="akdBlog">
        <?php /* data-no-sidebar-swipe: scrolling this strip must never start the sidebar gesture. */ ?>
        <div class="akd-bl__tabs" role="tablist" aria-label="Blog management" data-no-sidebar-swipe>
            <?php foreach ($blogTabs as $kind => [$label, $visible]): if (!$visible) continue; ?>
                <button type="button" class="akd-bl__tab" role="tab" id="blTab-<?= $kind ?>"
                    aria-controls="blPanel-<?= $kind ?>" aria-selected="<?= $kind === 'blog' ? 'true' : 'false' ?>"
                    <?= $kind === 'blog' ? '' : 'tabindex="-1"' ?> data-bl-tab="<?= $kind ?>"
                >
                    <?= htmlspecialchars($label) ?>
                    <span class="akd-bl__tab-count" data-tab-count="<?= $kind ?>">0</span>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Blog -->
        <section class="akd-bl-panel" id="blPanel-blog" role="tabpanel" aria-labelledby="blTab-blog" data-bl-panel="blog">
            <div class="akd-bl-toolbar">
                <?php akd_admin_blog_search('Search title, slug or category'); ?>

                <div class="akd-select akd-bl-select" data-filter-category>
                    <button type="button" class="akd-admin-field__input akd-select__trigger"
                        aria-haspopup="listbox" aria-expanded="false" aria-label="Filter by category">
                        <span class="akd-select__label" data-select-label>All categories</span>
                        <i class="fa-solid fa-chevron-down akd-select__chevron" aria-hidden="true"></i>
                    </button>
                </div>

                <button type="button" class="akd-admin-btn akd-admin-btn--primary akd-bl-toolbar__create" data-action="create">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> New article
                </button>
            </div>

            <div class="akd-bl-chips" role="group" aria-label="Filter by status">
                <?php foreach (['all' => 'All', 'draft' => 'Drafts', 'scheduled' => 'Scheduled', 'published' => 'Published'] as $key => $label): ?>
                    <button type="button" class="akd-bl-chip" data-status-filter="<?= $key ?>"
                        aria-pressed="<?= $key === 'all' ? 'true' : 'false' ?>"
                    >
                        <?= $label ?> <span class="akd-bl-chip__count" data-status-count="<?= $key ?>">0</span>
                    </button>
                <?php endforeach; ?>
            </div>

            <section class="akd-bl-feat" data-featured data-no-sidebar-swipe aria-labelledby="blFeatHeading">
                <div class="akd-bl-feat__head">
                    <h3 class="akd-bl-feat__title" id="blFeatHeading"><i class="fa-solid fa-star" aria-hidden="true"></i> Featured on the Blog home</h3>
                    <p class="akd-bl-feat__hint">Drag the grip, or use the arrows, to reorder. Up to <?= (int) BlogAdminService::MAX_FEATURED ?> articles; members only see published ones.</p>
                </div>
                <ol class="akd-bl-feat__list" data-feat-list></ol>
            </section>

            <p class="akd-bl-summary" data-summary aria-live="polite"></p>
            <?php akd_admin_blog_list($canAdmin, 'articles', ['Article', 'Status', 'Date', 'Read'], true); ?>
        </section>

        <!-- Categories -->
        <section class="akd-bl-panel" id="blPanel-categories" role="tabpanel" aria-labelledby="blTab-categories"
            data-bl-panel="categories" hidden
        >
            <div class="akd-bl-toolbar">
                <p class="akd-bl-summary" data-summary aria-live="polite"></p>
                <button type="button" class="akd-admin-btn akd-admin-btn--primary akd-bl-toolbar__create" data-action="create">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> New category
                </button>
            </div>
            <?php akd_admin_blog_list($canAdmin, 'categories', ['Category', 'Description', 'Articles'], false); ?>
        </section>

        <!-- Banned words -->
        <section class="akd-bl-panel" id="blPanel-words" role="tabpanel" aria-labelledby="blTab-words"
            data-bl-panel="words" hidden
        >
            <form class="akd-bl-quickadd" data-word-add novalidate>
                <input type="text" class="akd-admin-field__input" maxlength="60" autocomplete="off"
                    placeholder="Add a word or phrase" aria-label="Word or phrase to ban">
                <button type="submit" class="akd-admin-btn akd-admin-btn--primary">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Add
                </button>
            </form>
            <p class="akd-admin-field__error akd-bl-quickadd__error" data-word-add-error role="alert"></p>

            <details class="akd-bl-help">
                <summary>How matching works</summary>
                <ul>
                    <li><strong>Whole words only.</strong> Banning "heck" redacts "heck" and "HECK", never "Heckle" or "check".</li>
                    <li><strong>Phrases work.</strong> "free followers" matches across any spacing.</li>
                    <li><strong>Redaction, not rejection.</strong> A match becomes [redacted] and the comment is still posted.</li>
                    <li><strong>New comments only.</strong> Comments already posted are not changed when the list changes.</li>
                    <li><strong>Keep it short.</strong> Slurs, spam phrases and spoilers for ongoing events work best. Up to 500 entries.</li>
                </ul>
            </details>

            <p class="akd-bl-summary" data-summary aria-live="polite"></p>
            <?php akd_admin_blog_list($canAdmin, 'banned words', ['Word or phrase', 'Added'], false); ?>
        </section>

        <?php if ($canAdmin): ?>
            <!-- Archive -->
            <section class="akd-bl-panel" id="blPanel-archive" role="tabpanel" aria-labelledby="blTab-archive"
                data-bl-panel="archive" hidden
            >
                <div class="akd-bl-toolbar">
                    <?php akd_admin_blog_search('Search archived articles'); ?>
                </div>
                <p class="akd-bl-summary" data-summary aria-live="polite"></p>
                <?php akd_admin_blog_list($canAdmin, 'archived articles', ['Article', 'Status', 'Date', 'Read'], true); ?>
            </section>
        <?php endif; ?>
    </div>

    <?php if ($canAdmin): ?>
        <?php
        $blogBars = [
            'blog'       => ['archive' => 'Archive'],
            'archive'    => ['restore' => 'Restore', 'delete' => 'Delete'],
            'categories' => ['delete' => 'Delete'],
            'words'      => ['delete' => 'Delete'],
        ];
        foreach ($blogBars as $barKind => $barActions): ?>
            <div class="akd-ann-bulkbar" data-bulkbar="<?= $barKind ?>" role="region" aria-label="Bulk actions">
                <span class="akd-ann-bulkbar__count" data-bulk-count aria-live="polite">0 selected</span>
                <div class="akd-ann-bulkbar__actions">
                    <button type="button" class="akd-ann-bulkbar__clear" data-bulk-action="clear">Clear</button>
                    <?php foreach ($barActions as $action => $label): ?>
                        <button type="button"
                            class="<?= $action === 'restore' ? 'akd-ann-bulkbar__clear akd-bl-bulkbar__restore' : 'akd-ann-bulkbar__delete' ?>"
                            data-bulk-action="<?= $action ?>"><?= $label ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

<script type="application/json" id="akdBlogData"<?= Nonce::attr() ?>>
    <?= json_encode(
        $blogBoot,
        JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            | JSON_INVALID_UTF8_SUBSTITUTE
    ) ?>
</script>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>