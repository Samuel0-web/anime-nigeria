<?php $notifications = $dashboardData['notifications'] ?? []; ?>
<section class="akd-dash-card akd-dash-notif" aria-labelledby="dash-notif-heading">
    <div class="akd-dash-section__header">
        <h2 class="akd-dash-section__title" id="dash-notif-heading">Notifications</h2>
        <a class="akd-dash-section__action" href="#"><!-- TODO: link to full notifications page --> View all</a>
    </div>

    <ul class="akd-dash-notif__list" role="list">
        <?php foreach ($notifications as $notif): ?>
            <li class="akd-dash-notif__item<?= !empty($notif['unread']) ? ' akd-dash-notif__item--unread' : '' ?>" role="listitem">
                <span class="akd-dash-notif__icon akd-dash-notif__icon--<?= htmlspecialchars($notif['accent']) ?>">
                    <i class="fa-solid <?= htmlspecialchars($notif['icon']) ?>" aria-hidden="true"></i>
                </span>
                <span class="akd-dash-notif__body">
                    <span class="akd-dash-notif__title"><?= htmlspecialchars($notif['title']) ?></span>
                    <span class="akd-dash-notif__text"><?= htmlspecialchars($notif['text']) ?></span>
                </span>
                <span class="akd-dash-notif__time"><?= htmlspecialchars($notif['time']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
</section>