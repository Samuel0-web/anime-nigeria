<?php
/**
 * The editorial winner sequence. Excludes the featured category (index
 * 0, see featured.php) from the loop the same way ANAA's own
 * winners/grid.php excludes its featured category, but keeps original
 * absolute position for numbering so the sequence reads 02/07 through
 * 07/07 rather than restarting at 01. Alternates left/right alignment
 * by loop position, not by category metadata, so this keeps working
 * unchanged if the category count changes.
 *
 * @var array{
 *     categories: list<array{slug: string, name: string, winnerId?: string, nominees: list<array<string, mixed>>}>,
 * } $ancaOverview
 */

$categories = $ancaOverview['categories'];
$total      = count($categories);
$remaining  = array_slice($categories, 1, null, true); // preserve original keys for numbering
$pad        = static fn(int $n): string => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
?>
<section class="akd-anca-section" aria-labelledby="anca-winners-wall-heading">
    <header class="akd-anca-section__header" style="max-width: none; text-align: center; margin-inline: auto;">
        <p class="akd-anca-section__eyebrow" style="justify-content: center;">Meet the Winners</p>
        <h2 class="akd-anca-section__title" id="anca-winners-wall-heading">The members recognised this year</h2>
        <p class="akd-anca-section__desc" style="margin-inline: auto;">The members recognised across this year's community categories.</p>
    </header>

    <div class="akd-anca-winner-wall">
        <?php $loopIndex = 0; ?>
        <?php foreach ($remaining as $originalIndex => $category): ?>
            <?php
            $winner = akd_anca_winner_for_category($category);
            if (!$winner) {
                continue;
            }

            $position = $originalIndex + 1;
            $alt      = $loopIndex % 2 === 1;
            $loopIndex++;
            ?>
            <article class="akd-anca-winner-row<?= $alt ? ' akd-anca-winner-row--alt' : '' ?>" style="--anca-row-delay: <?= $loopIndex * 60 ?>ms;">
                <p class="akd-anca-winner-row__index" aria-hidden="true"><?= htmlspecialchars($pad($position)) ?> / <?= htmlspecialchars($pad($total)) ?></p>

                <div class="akd-anca-winner-row__content">
                    <a href="/member/player/<?= htmlspecialchars($winner['username']) ?>" class="akd-anca-winner-row__portrait-link">
                        <span class="akd-anca-winner-row__portrait akd-anca-winner-row__portrait--<?= htmlspecialchars($winner['accent']) ?>">
                            <span class="akd-anca-winner-row__initials" aria-hidden="true"><?= htmlspecialchars(akd_anca_initials($winner['fullname'])) ?></span>
                        </span>
                    </a>

                    <div class="akd-anca-winner-row__info">
                        <p class="akd-anca-winner-row__category"><?= htmlspecialchars($category['name']) ?></p>
                        <h3 class="akd-anca-winner-row__name">
                            <a href="/member/player/<?= htmlspecialchars($winner['username']) ?>"><?= htmlspecialchars($winner['fullname']) ?></a>
                        </h3>
                        <p class="akd-anca-winner-row__username">@<?= htmlspecialchars($winner['username']) ?></p>
                        <?php if (!empty($winner['reason'])): ?>
                            <p class="akd-anca-winner-row__reason"><?= htmlspecialchars($winner['reason']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </article>

            <?php if ($originalIndex !== array_key_last($remaining)): ?>
                <hr class="akd-anca-winner-row__divider" aria-hidden="true">
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>