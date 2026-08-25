<?php
/**
 * The remaining eight category winners (all categories except Best
 * Anime, which gets its own featured treatment above). Only required
 * from winners.php when the current phase reveals winners.
 *
 * Expects the following to be defined by the parent view (winners.php):
 *
 * @var array{
 *     categories: list<array{
 *         slug: string, name: string, accent: string, winnerId?: string|null,
 *         nominees: list<array{id: string, name: string, image: string|null}>,
 *     }>,
 * } $awardsOverview
 */

$remaining = array_values(array_filter(
    $awardsOverview['categories'],
    static fn(array $category): bool => $category['slug'] !== 'best-anime'
));
?>
<section class="akd-award-section" aria-labelledby="anaa-winners-grid-heading">
    <header class="akd-award-section__header">
        <div class="akd-award-section__heading-group">
            <p class="akd-award-section__eyebrow">Award Results</p>
            <h2 class="akd-award-section__title" id="anaa-winners-grid-heading">2026 Winners</h2>
            <p class="akd-award-section__desc">A look at the anime, characters and moments the community chose this year.</p>
        </div>
    </header>

    <div class="akd-award-categories">
        <?php foreach ($remaining as $category): ?>
            <?php
            $winner = akd_award_winner_for_category($category);
            if ($winner === null) {
                continue;
            }
            ?>
            <div class="akd-award-category akd-award-category--<?= htmlspecialchars($category['accent']) ?>">
                <?php if (!empty($winner['image'])): ?>
                    <img src="<?= htmlspecialchars($winner['image']) ?>" alt="" class="akd-award-category__img" aria-hidden="true">
                <?php endif; ?>
                <span class="akd-award-category__gradient" aria-hidden="true"></span>

                <span class="akd-award-category__body">
                    <span class="akd-winner-tag">
                        <i class="fa-solid fa-star" aria-hidden="true"></i> Winner
                    </span>
                    <span class="akd-award-category__count"><?= htmlspecialchars($category['name']) ?></span>
                    <span class="akd-award-category__name"><?= htmlspecialchars($winner['name']) ?></span>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</section>