<?php
/** @var array $notifications */
?>
<div class="akd-notify-empty" data-notify-empty<?= empty($notifications) ? '' : ' hidden' ?>>
    <div class="akd-notify-empty__icon" aria-hidden="true">
        <i class="fa-solid fa-bell"></i>
    </div>
    <h2 class="akd-notify-empty__title">No notifications yet</h2>
    <p class="akd-notify-empty__text">We'll show your latest activity here.</p>
</div>