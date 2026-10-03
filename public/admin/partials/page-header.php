<?php
// public/admin/partials/page-header.php
// Expects (all optional): $page_title, $page_description, $breadcrumbs,
// $pageActions = [['label' => 'Create User', 'url' => '/admin/users/create', 'primary' => true], ...]
$pageActions ??= [];
$breadcrumbs ??= [];
$crumbCount = count($breadcrumbs);
?>
<div class="akd-admin-page-head">
    <div class="akd-admin-page-head__main">
        <?php if ($crumbCount > 0): ?>
            <nav class="akd-admin-page-head__crumbs" aria-label="Breadcrumb">
                <?php foreach ($breadcrumbs as $i => $crumb): ?>
                    <?php if ($i > 0): ?>
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    <?php endif; ?>

                    <?php if (!empty($crumb['url']) && $i < $crumbCount - 1): ?>
                        <a href="<?= htmlspecialchars($crumb['url']) ?>"><?= htmlspecialchars($crumb['label']) ?></a>
                    <?php else: ?>
                        <span aria-current="page"><?= htmlspecialchars($crumb['label']) ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <h1 class="akd-admin-page-head__title"><?= htmlspecialchars($page_title ?? '') ?></h1>

        <?php if (!empty($page_description)): ?>
            <p class="akd-admin-page-head__desc"><?= htmlspecialchars($page_description) ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($pageActions)): ?>
        <div class="akd-admin-page-head__actions">
            <?php foreach ($pageActions as $action): ?>
                <a href="<?= htmlspecialchars($action['url'] ?? '#') ?>"
                   class="akd-admin-btn<?= !empty($action['primary']) ? ' akd-admin-btn--primary' : '' ?>">
                    <?= htmlspecialchars($action['label'] ?? '') ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>