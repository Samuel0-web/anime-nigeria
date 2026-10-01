<?php
/**
 * Loading representation of a single announcement card.
 *
 * Included by row.php inside each card that has an image. It is laid over
 * the real card (position: absolute; inset: 0), so it always has the exact
 * same size as the card it stands in for and cannot cause layout shift.
 * Its structure mirrors the real card: image, meta, title, excerpt, CTA.
 *
 * Purely visual, so it is hidden from assistive technologies.
 */
?>
<div class="akd-announce-skeleton" aria-hidden="true">
    <div class="akd-announce-skeleton__media"></div>

    <div class="akd-announce-skeleton__content">
        <span class="akd-announce-skeleton__line akd-announce-skeleton__line--meta"></span>

        <div class="akd-announce-skeleton__lines">
            <span class="akd-announce-skeleton__line akd-announce-skeleton__line--title"></span>
            <span class="akd-announce-skeleton__line akd-announce-skeleton__line--title akd-announce-skeleton__line--short"></span>
        </div>

        <div class="akd-announce-skeleton__lines">
            <span class="akd-announce-skeleton__line akd-announce-skeleton__line--text"></span>
            <span class="akd-announce-skeleton__line akd-announce-skeleton__line--text"></span>
            <span class="akd-announce-skeleton__line akd-announce-skeleton__line--text akd-announce-skeleton__line--short"></span>
        </div>

        <div class="akd-announce-skeleton__cta">
            <span class="akd-announce-skeleton__line akd-announce-skeleton__line--cta"></span>
        </div>
    </div>
</div>