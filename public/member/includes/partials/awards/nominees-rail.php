<?php
/**
 * Community Picks. An informational Overview highlight, not a preview
 * of /member/awards/nominations. This section intentionally has no
 * section-level link and no per-item link, since none of the mock data
 * points to a real destination for an individual title.
 *
 * Expects the following to be defined by the parent view (awards.php):
 *
 * @var array{
 *     phase: string,
 *     phases: array<string, array{communityPicksDescription: string}>,
 *     communityPicks: list<array{title: string, subtitle: string, category: string, accent: string, image: string|null}>,
 * } $awardsOverview
 */

$phaseKey = $awardsOverview['phase'];
$phase    = akd_award_phase_config($awardsOverview['phases'], $phaseKey);
$picks    = $awardsOverview['communityPicks'];
?>
<section class="akd-award-section akd-award-section--rail" aria-labelledby="anaa-picks-heading">
    <header class="akd-award-section__header">
        <div class="akd-award-section__heading-group">
            <p class="akd-award-section__eyebrow">Community Highlight</p>
            <h2 class="akd-award-section__title" id="anaa-picks-heading">Community Picks</h2>
            <p class="akd-award-section__desc"><?= htmlspecialchars($phase['communityPicksDescription']) ?></p>
        </div>
    </header>

    <div class="akd-award-rail-wrap">
        <ul class="akd-award-rail" role="list" data-award-rail>
            <?php foreach ($picks as $pick): ?>
                <li class="akd-award-rail__item">
                    <div class="akd-award-nominee akd-award-nominee--<?= htmlspecialchars($pick['accent']) ?>">
                        <?php if (!empty($pick['image'])): ?>
                            <img src="<?= htmlspecialchars($pick['image']) ?>" alt="" class="akd-award-nominee__img" aria-hidden="true">
                        <?php endif; ?>
                        <span class="akd-award-nominee__gradient" aria-hidden="true"></span>

                        <span class="akd-award-nominee__category"><?= htmlspecialchars($pick['category']) ?></span>
                        <span class="akd-award-nominee__body">
                            <span class="akd-award-nominee__title"><?= htmlspecialchars($pick['title']) ?></span>
                            <span class="akd-award-nominee__subtitle"><?= htmlspecialchars($pick['subtitle']) ?></span>
                        </span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

        <button type="button" class="akd-award-rail-nav akd-award-rail-nav--prev" data-award-rail-prev aria-label="Previous pick">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </button>
        <button type="button" class="akd-award-rail-nav akd-award-rail-nav--next" data-award-rail-next aria-label="Next pick">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </button>
    </div>
</section>