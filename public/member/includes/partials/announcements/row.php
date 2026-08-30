<?php
/**
 * @var array{id:string,category:string,accent:string,date:string,title:string,excerpt:string,cta:string,url:string,featured:bool} $item
 * @var string $slug
 */

$isAward = $item['accent'] === 'gold';
?>
<li class="akd-announce-row" role="listitem" data-announce-row="<?= htmlspecialchars($slug) ?>">
    <div class="akd-announce-row__top">
        <span class="akd-announce-row__category akd-announce-row__category--<?= htmlspecialchars($item['accent']) ?>">
            <?= htmlspecialchars($item['category']) ?>
        </span>
        <time class="akd-announce-row__date" datetime="<?= htmlspecialchars($item['date']) ?>">
            <?= htmlspecialchars(date('M j', strtotime($item['date']))) ?>
        </time>
    </div>

    <h3 class="akd-announce-row__title"><?= htmlspecialchars($item['title']) ?></h3>
    <p class="akd-announce-row__excerpt"><?= htmlspecialchars($item['excerpt']) ?></p>

    <a href="<?= htmlspecialchars($item['url']) ?>"
        class="akd-announce-row__cta<?= $isAward ? ' akd-announce-row__cta--gold' : '' ?>"
    >
        <?= htmlspecialchars($item['cta']) ?>
        <i class="fas fa-arrow-right" aria-hidden="true"></i>
    </a>
</li>