<?php
/**
 * Quiet closing section, the end of the award experience. Points to
 * Honoured Ones for historical recognition, this page never shows past
 * winners itself.
 *
 * @var array{honouredOnesLink: string} $ancaOverview
 */
?>
<section class="akd-anca-winners-closing" aria-labelledby="anca-winners-closing-heading">
    <p class="akd-anca-winners-closing__eyebrow">Recognised by the Community</p>
    <h2 class="akd-anca-winners-closing__heading" id="anca-winners-closing-heading">Recognised by the community</h2>
    <p class="akd-anca-winners-closing__desc">These awards celebrate the people who help make Anime Nigeria what it is.</p>

    <a href="<?= htmlspecialchars($ancaOverview['honouredOnesLink']) ?>" class="akd-award-btn akd-award-btn--ghost">
        View Honoured Ones
    </a>
</section>