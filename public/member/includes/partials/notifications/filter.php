<?php
/**
 * @var array<int, array{id:string,category:string,accent:string,icon:string,title:string,description:string,time:string,unread:bool,cta?:array}> $notifications
 */

$categoryAccents = [];
foreach ($notifications as $notif) {
    if (!isset($categoryAccents[$notif['category']])) {
        $categoryAccents[$notif['category']] = $notif['accent'];
    }
}
?>
<div class="akd-notify-toolbar">
    <div class="akd-notify-filter" role="group" aria-label="Filter notifications by category" data-notify-filter>
        <div class="akd-notify-filter__row">
            <button type="button" class="akd-notify-filter__nav akd-notify-filter__nav--prev"
                data-filter-nav="prev" aria-label="Show previous categories" hidden
            >
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
            </button>

            <div class="akd-notify-filter__scroll" data-filter-scroll>
                <button type="button" class="akd-notify-filter__pill" data-filter-value="all" aria-pressed="true">
                    All
                </button>
                <?php foreach ($categoryAccents as $category => $accent): ?>
                    <?php $isAward = $accent === 'gold'; ?>
                    <button type="button"
                        class="akd-notify-filter__pill<?= $isAward ? ' akd-notify-filter__pill--gold' : '' ?>"
                        data-filter-value="<?= htmlspecialchars(akd_notify_slug($category)) ?>"
                        aria-pressed="false"
                    >
                        <?= htmlspecialchars($category) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <button type="button"
                class="akd-notify-filter__nav akd-notify-filter__nav--next"
                data-filter-nav="next" aria-label="Show more categories" hidden
            >
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <?php if (!empty($notifications)): ?>
        <button type="button" class="akd-notify-selectall" data-notify-select-all aria-pressed="false">
            <i class="fa-solid fa-check" aria-hidden="true"></i>
            <span data-notify-select-all-label>Select all</span>
        </button>
    <?php endif; ?>
</div>

<p class="akd-visually-hidden" role="status" aria-live="polite" data-notify-status></p>