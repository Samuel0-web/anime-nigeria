<?php
require_once __DIR__ . '/footer-links.php';

$akdFooterLinks = akd_member_footer_links($_SERVER['REQUEST_URI'] ?? '/');

$akdExternalAttrs = static fn (array $link): string =>
    !empty($link['external']) ? ' target="_blank" rel="noopener noreferrer"' : '';
?>
<footer class="akd-footer">
    <nav class="akd-footer__nav" aria-label="Support and legal">
        <div class="akd-footer__actions">
            <a href="<?= htmlspecialchars($akdFooterLinks['contact']['href']) ?>"
               class="akd-help-btn akd-help-btn--secondary"<?= $akdExternalAttrs($akdFooterLinks['contact']) ?>>
                <?= htmlspecialchars($akdFooterLinks['contact']['label']) ?>
            </a>

            <a href="<?= htmlspecialchars($akdFooterLinks['bug']['href']) ?>"
                class="akd-help-btn akd-help-btn--ghost" data-bug-report-link<?= !empty($akdFooterLinks['bug']['intercepted']) ? ' data-preloader-ignore' : '' ?>>
                <?= htmlspecialchars($akdFooterLinks['bug']['label']) ?>
            </a>
        </div>

        <div class="akd-help__footer">
            <a href="<?= htmlspecialchars($akdFooterLinks['privacy']['href']) ?>"<?= $akdExternalAttrs($akdFooterLinks['privacy']) ?>>
                <?= htmlspecialchars($akdFooterLinks['privacy']['label']) ?>
            </a>
            <span aria-hidden="true">·</span>
            <a href="<?= htmlspecialchars($akdFooterLinks['terms']['href']) ?>"<?= $akdExternalAttrs($akdFooterLinks['terms']) ?>>
                <?= htmlspecialchars($akdFooterLinks['terms']['label']) ?>
            </a>
        </div>
    </nav>
</footer>
</body>
</html>