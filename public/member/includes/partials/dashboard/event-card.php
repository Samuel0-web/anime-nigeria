<?php
// Expects $event in scope (included from event-rail.php's foreach)
if (!isset($event)) {
    return;
}
$imageUrl = str_replace(' ', '%20', $event['image']);
?>
<li class="akd-dash-event-card akd-dash-event-card--<?= htmlspecialchars($event['status']) ?>" role="listitem">
    <a class="akd-dash-event-card__link" href="<?= htmlspecialchars($event['url']) ?>">
        <span class="akd-dash-event-card__image" style="background-image:url('<?= $imageUrl ?>');" aria-hidden="true"></span>
        <span class="akd-dash-event-card__overlay" aria-hidden="true"></span>

        <span class="akd-dash-event-card__content">
            <span class="akd-dash-event-card__status">
                <?php if ($event['status'] === 'live'): ?>
                    <span class="akd-dash-event-card__pulse" aria-hidden="true"></span>
                <?php endif; ?>
                <?= htmlspecialchars($event['statusLabel']) ?>
            </span>

            <span class="akd-dash-event-card__title"><?= htmlspecialchars($event['title']) ?></span>
            <span class="akd-dash-event-card__meta"><?= htmlspecialchars($event['meta']) ?></span>

            <span class="akd-dash-event-card__cta">
                <?= htmlspecialchars($event['action']) ?>
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </span>
        </span>
    </a>
</li>