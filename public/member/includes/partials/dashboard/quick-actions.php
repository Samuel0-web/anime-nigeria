<?php $quickActions = $dashboardData['quickActions'] ?? []; ?>
<section class="akd-dash-quick" aria-labelledby="dash-quick-heading">
    <h2 class="akd-dash-section__title akd-visually-hidden" id="dash-quick-heading">Quick Actions</h2>

    <ul class="akd-dash-quick__list" role="list">
        <?php foreach ($quickActions as $action): ?>
            <li class="akd-dash-quick__item" role="listitem">
                <a class="akd-dash-quick__link" href="<?= htmlspecialchars($action['url']) ?>">
                    <span class="akd-dash-quick__icon"><i class="fa-solid <?= htmlspecialchars($action['icon']) ?>" aria-hidden="true"></i></span>
                    <span class="akd-dash-quick__text">
                        <span class="akd-dash-quick__label"><?= htmlspecialchars($action['label']) ?></span>
                        <span class="akd-dash-quick__desc"><?= htmlspecialchars($action['text']) ?></span>
                    </span>
                    <i class="fa-solid fa-chevron-right akd-dash-quick__chevron" aria-hidden="true"></i>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>