<?php
$page_title       = "Gallery";
$page_description = "Moments, events, and memories from the Anime Nigeria community.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Gallery', 'url' => null],
];

require_once __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/data/gallery-support.php';
require __DIR__ . '/includes/data/gallery-data.php';

/** @var array<int, array{id:int,image:string,title:string,caption:string,category:string,date:string,alt:string,width:int,height:int}> $galleryItems */
$galleryCategories = akd_gallery_categories($galleryItems);
?>

<main class="akd-content">
    <div class="akd-gallery">
        <div class="akd-gallery__intro">
            <h2 class="akd-gallery__heading">Gallery</h2>
            <p class="akd-gallery__subheading">Moments, events, and memories from the Anime Nigeria community.</p>
        </div>

        <?php if (empty($galleryItems)): ?>
            <div class="akd-gallery__empty">
                <div class="akd-gallery__empty-icon" aria-hidden="true">
                    <i class="fa-solid fa-images"></i>
                </div>
                <h3 class="akd-gallery__empty-title">No moments yet</h3>
                <p class="akd-gallery__empty-text">New community moments will appear here when they're added.</p>
            </div>
        <?php else: ?>
            <div class="akd-gallery__filter" role="group" aria-label="Filter gallery by category">
                <?php foreach ($galleryCategories as $i => $category): ?>
                    <?php $filterSlug = $category === 'All' ? 'all' : akd_gallery_slug($category); ?>
                    <button
                        type="button"
                        class="akd-gallery__filter-btn<?= $i === 0 ? ' is-active' : '' ?>"
                        data-gallery-filter="<?= htmlspecialchars($filterSlug) ?>"
                        aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"
                    >
                        <?= htmlspecialchars($category) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="akd-gallery__grid" data-gallery-grid>
                <?php foreach ($galleryItems as $item): ?>
                    <button
                        type="button"
                        class="akd-gallery__item"
                        data-gallery-item="<?= (int) $item['id'] ?>"
                        data-gallery-category="<?= htmlspecialchars(akd_gallery_slug($item['category'])) ?>"
                        data-width="<?= (int) $item['width'] ?>"
                        data-height="<?= (int) $item['height'] ?>"
                        aria-label="View <?= htmlspecialchars($item['title']) ?>"
                    >
                        <img
                            src="<?= htmlspecialchars($item['image']) ?>"
                            alt="<?= htmlspecialchars($item['alt']) ?>"
                            width="<?= (int) $item['width'] ?>"
                            height="<?= (int) $item['height'] ?>"
                            loading="lazy"
                            decoding="async"
                            class="akd-gallery__image"
                        >
                        <span class="akd-gallery__fallback" aria-hidden="true">
                            <i class="fa-solid fa-image"></i>
                        </span>
                        <span class="akd-gallery__overlay" aria-hidden="true">
                            <span class="akd-gallery__overlay-title"><?= htmlspecialchars($item['title']) ?></span>
                            <span class="akd-gallery__overlay-meta">
                                <?= htmlspecialchars($item['category']) ?><?= $item['date'] ? ' &bull; ' . htmlspecialchars($item['date']) : '' ?>
                            </span>
                        </span>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="akd-gallery__empty akd-gallery__empty--filtered" hidden data-gallery-filtered-empty>
                <div class="akd-gallery__empty-icon" aria-hidden="true">
                    <i class="fa-solid fa-images"></i>
                </div>
                <h3 class="akd-gallery__empty-title">Nothing here yet</h3>
                <p class="akd-gallery__empty-text">New moments will appear here when they're added.</p>
            </div>

            <script type="application/json" data-gallery-data><?= json_encode(
                array_map(static function (array $item): array {
                    return [
                        'id'            => $item['id'],
                        'title'         => $item['title'],
                        'caption'       => $item['caption'],
                        'categoryLabel' => $item['category'],
                        'date'          => $item['date'],
                    ];
                }, $galleryItems),
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            ) ?></script>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>