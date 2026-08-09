<?php
http_response_code(404);

$page_title       = "Page Not Found";
$page_description = "The page you're looking for doesn't exist.";

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Page Not Found', 'url' => null],
];

require_once __DIR__ . '/includes/header.php';
?>

<main class="akd-content">
    <section class="akd-route-404">
        <!-- Background Watermark -->
        <div class="akd-route-404__code" aria-hidden="true">404</div>

        <!-- Foreground Content -->
        <div class="akd-route-404__content">
            <div class="akd-route-404__icon">
                <i class="fa-solid fa-compass"></i>
            </div>

            <h1 class="akd-route-404__title">Page not found</h1>

            <p class="akd-route-404__description">
                The page you're looking for doesn't exist or may have been moved.
            </p>

            <div class="akd-route-404__actions">
                <a href="/dashboard" class="akd-btn akd-btn--primary">
                    <i class="fa-solid fa-house"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>