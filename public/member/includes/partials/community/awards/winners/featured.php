<?php
/**
 * Featured winner: the visual centrepiece of the page. ANCA has no
 * existing "featured category" concept (the Overview page deliberately
 * moved away from a featured flag toward structural first/last
 * styling), so per the brief this uses a deterministic fallback
 * instead of inventing a new one: the first category in the existing
 * collection.
 *
 * @var array{
 *     categories: list<array{slug: string, name: string, winnerId?: string, nominees: list<array<string, mixed>>}>,
 * } $ancaOverview
 */

$featuredCategory = $ancaOverview['categories'][0] ?? null;
$featuredWinner    = $featuredCategory ? akd_anca_winner_for_category($featuredCategory) : null;

if (!$featuredCategory || !$featuredWinner) {
    return;
}
?>
<section class="akd-anca-featured-winner" aria-labelledby="anca-featured-winner-heading">
    <p class="akd-anca-featured-winner__eyebrow">Featured Winner</p>

    <a href="/member/player/<?= htmlspecialchars($featuredWinner['username']) ?>" class="akd-anca-featured-winner__portrait-link">
        <span class="akd-anca-featured-winner__portrait akd-anca-featured-winner__portrait--<?= htmlspecialchars($featuredWinner['accent']) ?>">
            <span class="akd-anca-featured-winner__initials" aria-hidden="true">
                <?= htmlspecialchars(akd_anca_initials($featuredWinner['fullname'])) ?>
            </span>
        </span>
    </a>

    <p class="akd-anca-featured-winner__category"><?= htmlspecialchars($featuredCategory['name']) ?></p>

    <h2 class="akd-anca-featured-winner__name" id="anca-featured-winner-heading">
        <a href="/member/player/<?= htmlspecialchars($featuredWinner['username']) ?>"><?= htmlspecialchars($featuredWinner['fullname']) ?></a>
    </h2>

    <p class="akd-anca-featured-winner__username">@<?= htmlspecialchars($featuredWinner['username']) ?></p>

    <?php if (!empty($featuredWinner['reason'])): ?>
        <p class="akd-anca-featured-winner__reason"><?= htmlspecialchars($featuredWinner['reason']) ?></p>
    <?php endif; ?>

    <span class="akd-anca-featured-winner__badge">Winner</span>
</section>