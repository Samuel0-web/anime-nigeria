<?php
/**
 * @var array{id:string,category:string,accent:string,date:string,title:string,excerpt:string,cta:string,url:string,featured:bool,image?:?string,image_alt?:?string} $item
 * @var string $slug
 * @var int    $index Position in the full list, set by the page loop. Optional.
 */

$isAward    = $item['accent'] === 'gold';
$isFeatured = !empty($item['featured']);
$image      = akd_announce_image($item);
$mediaClass = 'akd-announce-row__media' . ($image === null ? ' akd-announce-row__media--placeholder' : '');

// A card with an image starts in its loading state (skeleton visible).
// announcements.js clears it once the image has loaded or failed.
$rowClass = 'akd-announce-row' . ($image !== null ? ' is-loading' : '');

// The first three cards (one desktop row) are in the initial viewport, so
// they load eagerly and the very first image is prioritised. Everything
// else uses native lazy loading.
$position = isset($index) ? (int) $index : PHP_INT_MAX;
$loading  = $position < 3 ? 'eager' : 'lazy';
$priority = $position === 0;

// "Aug 26" for the current year, "Aug 26, 2025" otherwise.
$timestamp = strtotime($item['date']);
$dateLabel = date(date('Y', $timestamp) === date('Y') ? 'M j' : 'M j, Y', $timestamp);
?>
<li class="<?= $rowClass ?>" data-announce-row="<?= htmlspecialchars($slug) ?>">
    <?php // The whole card is one link. It contains no other interactive elements. ?>
    <a class="akd-announce-row__link" href="<?= htmlspecialchars($item['url']) ?>">
        <div class="<?= $mediaClass ?>">
            <?php if ($image !== null): ?>
                <img class="akd-announce-row__image"
                    src="<?= htmlspecialchars($image['src']) ?>"
                    alt="<?= htmlspecialchars($image['alt']) ?>"
                    loading="<?= $loading ?>"
                    decoding="async"
                    <?= $priority ? 'fetchpriority="high"' : '' ?>
                >
            <?php else: ?>
                <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
            <?php endif; ?>

            <?php if ($isFeatured): ?>
                <span class="akd-announce-row__badge">
                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                    Featured
                </span>
            <?php endif; ?>
        </div>

        <div class="akd-announce-row__content">
            <div class="akd-announce-row__top">
                <span class="akd-announce-row__category akd-announce-row__category--<?= htmlspecialchars($item['accent']) ?>">
                    <?= htmlspecialchars($item['category']) ?>
                </span>
                <span class="akd-announce-row__sep" aria-hidden="true">&middot;</span>
                <time class="akd-announce-row__date" datetime="<?= htmlspecialchars($item['date']) ?>">
                    <?= htmlspecialchars($dateLabel) ?>
                </time>
            </div>

            <h2 class="akd-announce-row__title"><?= htmlspecialchars($item['title']) ?></h2>
            <p class="akd-announce-row__excerpt"><?= htmlspecialchars($item['excerpt']) ?></p>

            <span class="akd-announce-row__cta<?= $isAward ? ' akd-announce-row__cta--gold' : '' ?>">
                <?= htmlspecialchars($item['cta']) ?>
                <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </span>
        </div>
    </a>

    <?php if ($image !== null): ?>
        <?php require __DIR__ . '/skeleton.php'; ?>
    <?php endif; ?>
</li>