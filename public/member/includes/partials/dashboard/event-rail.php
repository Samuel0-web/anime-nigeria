<?php $events = $dashboardData['events'] ?? []; ?>
<section class="akd-dash-section akd-dash-section--rail" aria-labelledby="dash-rail-heading">
    <div class="akd-dash-section__header">
        <h2 class="akd-dash-section__title" id="dash-rail-heading">Live &amp; Upcoming</h2>
    </div>

    <div class="akd-dash-rail-wrap" data-dash-carousel>
        <ul class="akd-dash-rail" role="list" data-dash-carousel-track>
            <?php foreach ($events as $event): ?>
                <?php require __DIR__ . '/event-card.php'; ?>
            <?php endforeach; ?>
        </ul>

        <button type="button" class="akd-dash-rail-nav akd-dash-rail-nav--prev" data-dash-carousel-prev aria-label="Previous event">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </button>
        <button type="button" class="akd-dash-rail-nav akd-dash-rail-nav--next" data-dash-carousel-next aria-label="Next event">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </button>
    </div>
</section>