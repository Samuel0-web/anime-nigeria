<?php
/**
 * Expects the following to be defined by the parent view (overview.php):
 *
 * @var array{
 *     philosophy: array{eyebrow: string, heading: string, body: string, traits: list<array{label: string, note: string}>},
 * } $ancaOverview
 */

$philosophy = $ancaOverview['philosophy'];
?>
<section class="akd-anca-section" id="anca-philosophy" aria-labelledby="anca-philosophy-heading">
    <header class="akd-anca-section__header">
        <p class="akd-anca-section__eyebrow"><?= htmlspecialchars($philosophy['eyebrow']) ?></p>
        <h2 class="akd-anca-section__title" id="anca-philosophy-heading"><?= htmlspecialchars($philosophy['heading']) ?></h2>
        <p class="akd-anca-section__desc"><?= htmlspecialchars($philosophy['body']) ?></p>
    </header>

    <ul class="akd-anca-traits" role="list">
        <?php foreach ($philosophy['traits'] as $trait): ?>
            <li class="akd-anca-trait">
                <span class="akd-anca-trait__label"><?= htmlspecialchars($trait['label']) ?></span>
                <span class="akd-anca-trait__note"><?= htmlspecialchars($trait['note']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
</section>