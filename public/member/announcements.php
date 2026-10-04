<?php
$page_title       = 'Announcements';
$page_description = "What's happening across Anime Nigeria.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Announcements', 'url' => null],
];

$markAnnouncementsRead = true; // header.php clears this member's unread announcements

require_once __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/data/announcements-support.php';
require __DIR__ . '/includes/data/announcements-data.php';

/** @var array<int, array{id:string,category_id:int,category:string,accent:string,date:string,title:string,excerpt:string,cta:string,url:string,featured:bool,image?:?string,image_alt?:?string}> $announcements */
/** @var array<int, array{id:int,name:string,accent:string,count:int}> $announceCategories */
?>

<main class="akd-content">
    <div class="akd-announce" data-announce-root data-announce-endpoint="/member/api/announcements">
        <?php require __DIR__ . '/includes/partials/announcements/header.php'; ?>

        <?php if ($announceLoadError): ?>
            <div class="akd-announce-empty">
                <div class="akd-announce-empty__icon" aria-hidden="true">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h2 class="akd-announce-empty__title">Couldn't load announcements</h2>
                <p class="akd-announce-empty__text">Something went wrong on our side. Please try again.</p>
                <a class="akd-announce-more__button" href="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/member/announcements') ?>">Reload</a>
            </div>
        <?php elseif (empty($announcements)): ?>
            <?php require __DIR__ . '/includes/partials/announcements/empty.php'; ?>
        <?php else: ?>
            <?php require __DIR__ . '/includes/partials/announcements/filter.php'; ?>

            <ul class="akd-announce-group" role="list" data-announce-group
                data-next-cursor="<?= htmlspecialchars((string) $announceNextCursor) ?>"
                data-has-more="<?= $announceHasMore ? '1' : '0' ?>"
            >
                <?php foreach ($announcements as $index => $item): ?>
                    <?php require __DIR__ . '/includes/partials/announcements/row.php'; ?>
                <?php endforeach; ?>
            </ul>

            <div class="akd-announce-empty" hidden data-announce-empty>
                <div class="akd-announce-empty__icon" aria-hidden="true">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
                <h2 class="akd-announce-empty__title">No announcements here</h2>
                <p class="akd-announce-empty__text">There are no announcements in this category yet.</p>
            </div>

            <div class="akd-announce-more" data-announce-more<?= $announceHasMore ? '' : ' hidden' ?>>
                <p class="akd-announce-more__error" data-announce-more-error role="alert" hidden>
                    Couldn't load announcements. Check your connection and try again.
                </p>
                <button type="button" class="akd-announce-more__button" data-announce-more-btn>See more</button>
            </div>

            <?php /* Cloned while a batch loads. Reuses the existing card skeleton. */ ?>
            <template data-announce-placeholder>
                <li class="akd-announce-row akd-announce-row--placeholder" aria-hidden="true">
                    <?php require __DIR__ . '/includes/partials/announcements/skeleton.php'; ?>
                </li>
            </template>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>