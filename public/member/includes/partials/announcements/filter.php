<?php
/**
 * Category tabs. Only categories that currently have announcements are
 * passed in (AnnouncementService::memberCategories), so an empty category
 * never appears. The value of each tab is the category ID.
 *
 * @var array<int, array{id:int,name:string,accent:string,count:int}> $announceCategories
 */
?>
<div class="akd-announce-filter" role="group" aria-label="Filter announcements by category" data-announce-filter>
    <div class="akd-announce-filter__row">
        <button type="button" class="akd-announce-filter__nav akd-announce-filter__nav--prev"
            data-filter-nav="prev" aria-label="Show previous categories" hidden
        >
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </button>

        <div class="akd-announce-filter__scroll" data-filter-scroll>
            <button type="button" class="akd-announce-filter__pill" data-filter-value="all" aria-pressed="true">
                All
            </button>
            <?php foreach ($announceCategories as $category): ?>
                <?php $isAward = $category['accent'] === 'gold'; ?>
                <button type="button"
                    class="akd-announce-filter__pill<?= $isAward ? ' akd-announce-filter__pill--gold' : '' ?>"
                    data-filter-value="<?= (int) $category['id'] ?>"
                    aria-pressed="false"
                >
                    <?= htmlspecialchars($category['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <button type="button"
            class="akd-announce-filter__nav akd-announce-filter__nav--next"
            data-filter-nav="next" aria-label="Show more categories" hidden
        >
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </button>
    </div>
</div>

<p class="akd-visually-hidden" role="status" aria-live="polite" data-announce-status></p>