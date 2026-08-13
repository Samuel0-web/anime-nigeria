<?php $achievements = $dashboardData['achievements'] ?? []; ?>
<section class="akd-dash-card akd-dash-achieve" aria-labelledby="dash-achieve-heading">
    <div class="akd-dash-section__header">
        <h2 class="akd-dash-section__title" id="dash-achieve-heading">Recent Achievements</h2>
        <a class="akd-dash-section__action" href="/member/achievements">View all</a>
    </div>

    <ul class="akd-dash-achieve__list" role="list">
        <?php foreach ($achievements as $ach): ?>
            <li class="akd-dash-achieve__item" role="listitem">
                <span class="akd-dash-achieve__icon"><i class="fa-solid <?= htmlspecialchars($ach['icon']) ?>" aria-hidden="true"></i></span>
                <span class="akd-dash-achieve__body">
                    <span class="akd-dash-achieve__name"><?= htmlspecialchars($ach['name']) ?></span>
                    <span class="akd-dash-achieve__text"><?= htmlspecialchars($ach['text']) ?></span>
                </span>
                <span class="akd-dash-achieve__time"><?= htmlspecialchars($ach['time']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
</section>