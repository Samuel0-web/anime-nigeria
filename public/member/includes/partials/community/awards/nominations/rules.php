<?php
/**
 * Compact nomination rules strip, not a rules section. Only facts the
 * current ANCA data actually supports: the per-category limit and the
 * close date. Nothing here is invented.
 *
 * Expects the following to be defined by the parent view (nominations.php):
 *
 * @var array{
 *     dates: array{nominations_close: string},
 *     nomination: array{limitPerCategory: int},
 * } $ancaOverview
 */

$limit    = $ancaOverview['nomination']['limitPerCategory'];
$closes   = akd_anca_format_date($ancaOverview['dates']['nominations_close']);
$limitTxt = $limit === 1 ? 'One nomination per category' : $limit . ' nominations per category';
?>
<div class="akd-anca-nom-rules" role="note">
    <span class="akd-anca-nom-rules__item"><?= htmlspecialchars($limitTxt) ?></span>
    <span class="akd-anca-nom-rules__divider" aria-hidden="true">&middot;</span>
    <span class="akd-anca-nom-rules__item">Nominations can be changed until they close</span>
    <span class="akd-anca-nom-rules__divider" aria-hidden="true">&middot;</span>
    <span class="akd-anca-nom-rules__item">Closes <?= htmlspecialchars($closes) ?></span>
</div>