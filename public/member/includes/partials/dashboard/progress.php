<?php $progress = $dashboardData['progress'] ?? []; ?>
<section class="akd-dash-card akd-dash-progress" aria-labelledby="dash-progress-heading">
    <div class="akd-dash-section__header">
        <h2 class="akd-dash-section__title" id="dash-progress-heading">Your Progress</h2>
    </div>

    <ul class="akd-dash-progress__grid" role="list">
        <?php foreach ($progress as $stat): ?>
            <li class="akd-dash-progress__stat" role="listitem">
                <span class="akd-dash-progress__icon"><i class="fa-solid <?= htmlspecialchars($stat['icon']) ?>" aria-hidden="true"></i></span>
                <span class="akd-dash-progress__value"><?= htmlspecialchars((string) $stat['value']) ?></span>
                <span class="akd-dash-progress__label"><?= htmlspecialchars($stat['label']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
</section>