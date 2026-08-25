<?php
/**
 * The featured Best Anime winner. Only required from winners.php when
 * the current phase reveals winners.
 *
 * Expects the following to be defined by the parent view (winners.php):
 *
 * @var array{
 *     categories: list<array{
 *         slug: string, name: string, winnerId?: string|null,
 *         nominees: list<array{id: string, name: string, image: string|null}>,
 *     }>,
 * } $awardsOverview
 */

$featuredCategory = null;

foreach ($awardsOverview['categories'] as $category) {
    if ($category['slug'] === 'best-anime') {
        $featuredCategory = $category;
        break;
    }
}

if ($featuredCategory === null) {
    return;
}

$winner = akd_award_winner_for_category($featuredCategory);

if ($winner === null) {
    return;
}
?>
<section class="akd-winner-feature" aria-labelledby="anaa-featured-winner-heading">
    <?php if (!empty($winner['image'])): ?>
        <img src="<?= htmlspecialchars($winner['image']) ?>" alt="" class="akd-winner-feature__img">
    <?php endif; ?>
    <span class="akd-winner-feature__gradient" aria-hidden="true"></span>
    <span class="akd-winner-feature__grain" aria-hidden="true"></span>

    <div class="akd-winner-feature__content">
        <span class="akd-winner-tag akd-winner-tag--lg">
            <i class="fa-solid fa-star" aria-hidden="true"></i> Winner
        </span>
        <p class="akd-winner-feature__category"><?= htmlspecialchars($featuredCategory['name']) ?></p>
        <h2 class="akd-winner-feature__name" id="anaa-featured-winner-heading">
            <?= htmlspecialchars($winner['name']) ?>
        </h2>
        <p class="akd-winner-feature__note">Chosen by the Anime Nigeria community.</p>
    </div>
</section>