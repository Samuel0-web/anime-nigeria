<?php
/**
 * Expects the following to be defined by the parent view (winners.php):
 *
 * @var array{revealWinners: bool} $winnersPage
 */
?>
<section class="akd-award-cta akd-award-cta--quiet" aria-label="Community identity">
    <div class="akd-award-cta__grain" aria-hidden="true"></div>
    <div class="akd-award-cta__content">
        <?php if (!empty($winnersPage['revealWinners'])): ?>
            <h2 class="akd-award-cta__heading">Chosen by the community.</h2>
            <p class="akd-award-cta__desc">Your favourites. Your votes. Your awards.</p>
        <?php else: ?>
            <h2 class="akd-award-cta__heading">The community decides.</h2>
            <p class="akd-award-cta__desc">Every ANAA winner starts with a community nomination and a community vote.</p>
        <?php endif; ?>
    </div>
</section>