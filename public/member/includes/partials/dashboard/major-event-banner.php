<?php $majorEvent = $dashboardData['majorEvent'] ?? null; ?>
<?php if ($majorEvent): ?>
<section class="akd-dash-banner" aria-labelledby="dash-banner-heading">
    <?php if (!empty($majorEvent['image'])): ?>
        <span class="akd-dash-banner__image" style="background-image:url('<?= htmlspecialchars(str_replace(' ', '%20', $majorEvent['image'])) ?>');" aria-hidden="true"></span>
        <span class="akd-dash-banner__overlay" aria-hidden="true"></span>
    <?php endif; ?>
    <div class="akd-dash-banner__content">
        <span class="akd-dash-banner__eyebrow">Major Event</span>
        <h2 class="akd-dash-banner__title" id="dash-banner-heading"><?= htmlspecialchars($majorEvent['title']) ?></h2>
        <p class="akd-dash-banner__text"><?= htmlspecialchars($majorEvent['text']) ?></p>
    </div>
    <a class="akd-dash-banner__cta" href="<?= htmlspecialchars($majorEvent['url']) ?>">
        <?= htmlspecialchars($majorEvent['action']) ?>
        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
    </a>
</section>
<?php endif; ?>