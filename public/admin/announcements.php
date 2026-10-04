<?php
$page_title       = 'Announcements';
$page_description = 'Publish updates for members and manage the categories they are filed under.';

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/home'],
    ['label' => 'Announcements', 'url' => null],
];

require_once __DIR__ . '/partials/header.php';

// Only admins can delete. The API enforces this too; the UI just hides the controls.
$canDelete = ($user['role'] ?? '') === 'admin';
$pageSize  = 20;

use App\Security\Nonce;

$annBootstrap = [
    'canDelete'     => $canDelete,
    'pageSize'      => $pageSize,
    'accents'       => \App\Services\AnnouncementService::accents(),
    'loadError'     => false,
    'announcements' => ['items' => [], 'total' => 0, 'has_more' => false],
    'categories'    => [],
];

try {
    $annService = \App\Services\AnnouncementService::make();
    $annBootstrap['announcements'] = $annService->listAnnouncements(0, $pageSize);
    $annBootstrap['categories']    = $annService->listCategories();
} catch (\Throwable $e) {
    \App\Core\Logger::error($e);
    $annBootstrap['loadError'] = true;
}

if (!function_exists('akd_admin_render_ann_panel')) {
    /**
     * Static shell for one tab panel. The JS owns everything dynamic:
     * rows, counts, selection, empty and error copy.
     */
    function akd_admin_render_ann_panel(string $kind, string $createLabel, string $noun,
        bool $canDelete, bool $active, array $headLabels
    ): void {
        ?>
        <section class="akd-ann-panel" id="annPanel-<?= $kind ?>" role="tabpanel"
            aria-labelledby="annTab-<?= $kind ?>" data-ann-panel="<?= $kind ?>"<?= $active ? '' : ' hidden' ?>
        >
            <div class="akd-ann-toolbar">
                <div class="akd-ann-toolbar__default">
                    <p class="akd-ann-toolbar__summary" data-summary></p>
                    <button type="button" class="akd-admin-btn akd-admin-btn--primary" data-action="create">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i> <?= htmlspecialchars($createLabel) ?>
                    </button>
                </div>
            </div>

            <div class="akd-ann-listwrap">
                <div class="akd-ann-head" data-list-head hidden>
                    <?php if ($canDelete): ?>
                        <label class="akd-ann-check">
                            <input type="checkbox" data-select-all aria-label="Select all <?= $noun ?> shown">
                            <span class="akd-ann-check__box" aria-hidden="true">
                                <i class="fa-solid fa-check"></i><i class="fa-solid fa-minus"></i>
                            </span>
                        </label>
                    <?php endif; ?>
                    <span class="akd-ann-head__title">
                        <span class="akd-ann-head__narrow">Select all</span>
                        <span class="akd-ann-head__wide"><?= htmlspecialchars($headLabels[0]) ?></span>
                    </span>
                    <?php foreach (array_slice($headLabels, 1) as $label): ?>
                        <span class="akd-ann-head__col"><?= htmlspecialchars($label) ?></span>
                    <?php endforeach; ?>
                    <span class="akd-ann-head__col" aria-hidden="true"></span>
                </div>

                <ul class="akd-ann-list" role="list" data-list></ul>

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

                <div class="akd-ann-more" data-more hidden>
                    <button type="button" class="akd-admin-btn" data-action="load-more">Show more</button>
                </div>
            </div>
        </section>
        <?php
    }
}
?>

<main class="akd-content">
    <?php require __DIR__ . '/partials/page-header.php'; ?>

    <div class="akd-ann<?= $canDelete ? '' : ' akd-ann--no-bulk' ?>" id="akdAnn">
        <div class="akd-ann__tabs" role="tablist" aria-label="Announcement management">
            <button type="button" class="akd-ann__tab" role="tab" id="annTab-announcements"
                aria-controls="annPanel-announcements" aria-selected="true" data-ann-tab="announcements"
            >
                Announcements
                <span class="akd-ann__tab-count" data-tab-count="announcements"><?= (int) $annBootstrap['announcements']['total'] ?></span>
            </button>
            <button type="button" class="akd-ann__tab" role="tab" id="annTab-categories"
                aria-controls="annPanel-categories" aria-selected="false" tabindex="-1" data-ann-tab="categories"
            >
                Categories
                <span class="akd-ann__tab-count" data-tab-count="categories"><?= count($annBootstrap['categories']) ?></span>
            </button>
        </div>

        <?php akd_admin_render_ann_panel('announcements', 'New announcement', 'announcements',
            $canDelete, true, ['Announcement', 'Category', 'Published', 'Destination']); ?>
        <?php akd_admin_render_ann_panel('categories', 'New category', 'categories',
            $canDelete, false, ['Category', 'Used by', 'Created']); ?>
    </div>

    <?php if ($canDelete): ?>
        <?php foreach (['announcements', 'categories'] as $akdBulkKind): ?>
            <div class="akd-ann-bulkbar" data-bulkbar="<?= $akdBulkKind ?>" role="region"
                aria-label="Bulk actions for <?= $akdBulkKind ?>"
            >
                <span class="akd-ann-bulkbar__count" data-bulk-count aria-live="polite">0 selected</span>
                <div class="akd-ann-bulkbar__actions">
                    <button type="button" class="akd-ann-bulkbar__clear" data-bulk-action="clear">Clear</button>
                    <button type="button" class="akd-ann-bulkbar__delete" data-bulk-action="delete">Delete</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

<script type="application/json" id="akdAnnouncementsData"<?= Nonce::attr() ?>>
    <?= json_encode(
        $annBootstrap,
        JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            | JSON_INVALID_UTF8_SUBSTITUTE
    ) ?>
</script>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>