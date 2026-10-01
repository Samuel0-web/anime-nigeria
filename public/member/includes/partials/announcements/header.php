<?php
/**
 * Desktop-only page introduction. Hidden below $bp-lg in CSS.
 *
 * Reuses the page-level values defined in announcements.php, so the
 * heading text is never duplicated in the data layer.
 *
 * @var string $page_title
 * @var string $page_description
 */
?>
<header class="akd-announce-header">
    <h1 class="akd-announce-header__title"><?= htmlspecialchars($page_title) ?></h1>
    <p class="akd-announce-header__subtitle"><?= htmlspecialchars($page_description) ?></p>
</header>