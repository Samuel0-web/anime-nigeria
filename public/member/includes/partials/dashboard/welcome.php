<?php
$name    = $dashboardData['welcome']['name'] ?? '';
$message = $dashboardData['welcome']['message'] ?? '';
?>
<section class="akd-dash-welcome" aria-label="Welcome">
    <h1 class="akd-dash-welcome__title">
        Welcome back, <span class="akd-dash-welcome__name"><?= htmlspecialchars($name) ?></span>
    </h1>
    <p class="akd-dash-welcome__subtitle"><?= htmlspecialchars($message) ?></p>
</section>