<?php
/**
 * @var array{id:string,category:string,accent:string,icon:string,title:string,description:string,time:string,unread:bool,cta?:array} $item
 * @var string $slug
 */
$hasCta = !empty($item['cta']['label']) && !empty($item['cta']['url']);
?>
<li class="akd-notify-row" role="listitem" data-notify-row="<?= htmlspecialchars($slug) ?>" data-notify-id="<?= htmlspecialchars($item['id']) ?>">
    <button type="button" class="akd-notify-row__select" data-notify-select aria-pressed="false"
        aria-label="Select <?= htmlspecialchars($item['title']) ?>"
    >
        <span class="akd-notify-row__select-dot" aria-hidden="true"></span>
    </button>

    <div class="akd-notify-row__icon akd-notify-icon--<?= htmlspecialchars($item['accent']) ?>">
        <i class="fa-solid <?= htmlspecialchars($item['icon']) ?>" aria-hidden="true"></i>
    </div>

    <div class="akd-notify-row__body">
        <div class="akd-notify-row__top">
            <span class="akd-notify-row__category"><?= htmlspecialchars($item['category']) ?></span>
            <span class="akd-notify-row__time"><?= htmlspecialchars($item['time']) ?></span>
        </div>

        <h3 class="akd-notify-row__title"><?= htmlspecialchars($item['title']) ?></h3>
        <?php if (!empty($item['description'])): ?>
            <p class="akd-notify-row__excerpt"><?= htmlspecialchars($item['description']) ?></p>
        <?php endif; ?>

        <div class="akd-notify-row__actions">
            <?php if ($hasCta): ?>
                <a href="<?= htmlspecialchars($item['cta']['url']) ?>" class="akd-notify-row__cta">
                    <?= htmlspecialchars($item['cta']['label']) ?>
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            <?php endif; ?>

            <button type="button" class="akd-notify-row__delete" data-notify-delete aria-label="Delete this notification">
                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                <span>Delete</span>
            </button>
        </div>
    </div>
</li>