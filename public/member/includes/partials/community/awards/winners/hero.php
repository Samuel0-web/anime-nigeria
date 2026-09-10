<?php
/**
 * Winners hero. Reuses the Overview hero's image and scrim treatment
 * (.akd-anca-hero, see _overview.scss) with a --winners modifier that
 * trims the height and drops the phase status pill and actions, since
 * this hero opens a celebration rather than pointing at another stage.
 * Deliberately the same asset as the Overview hero, not a second image.
 *
 * @var array{edition: int, hero: array{image: string, imageAlt: string}} $ancaOverview
 */

$hero = $ancaOverview['hero'];
?>
<section class="akd-anca-hero akd-anca-hero--winners" aria-labelledby="anca-winners-hero-heading">
    <div class="akd-anca-hero__art">
        <img
            src="<?= htmlspecialchars($hero['image']) ?>"
            alt="<?= htmlspecialchars($hero['imageAlt']) ?>"
            class="akd-anca-hero__art-img"
        >
        <div class="akd-anca-hero__art-scrim" aria-hidden="true"></div>
    </div>

    <div class="akd-anca-hero__grain" aria-hidden="true"></div>

    <div class="akd-anca-hero__content">
        <p class="akd-anca-hero__kicker">Community Awards <?= htmlspecialchars((string) $ancaOverview['edition']) ?></p>
        <h1 class="akd-anca-hero__headline" id="anca-winners-hero-heading">The Community Has Chosen</h1>
        <p class="akd-anca-hero__tagline">Celebrating the people recognised by the Anime Nigeria community this year.</p>
    </div>
</section>