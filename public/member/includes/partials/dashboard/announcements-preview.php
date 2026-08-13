<?php $announcements = $dashboardData['announcements'] ?? []; ?>
<section class="akd-dash-card akd-dash-announce" aria-labelledby="dash-announce-heading">
    <div class="akd-dash-section__header">
        <h2 class="akd-dash-section__title" id="dash-announce-heading">Announcements</h2>
        <a class="akd-dash-section__action" href="#"><!-- TODO: link to full announcements page --> View all</a>
    </div>

    <ul class="akd-dash-announce__list" role="list">
        <?php foreach ($announcements as $note): ?>
            <li class="akd-dash-announce__item" role="listitem">
                <span class="akd-dash-announce__date"><?= htmlspecialchars($note['date']) ?></span>
                <span class="akd-dash-announce__body">
                    <span class="akd-dash-announce__title"><?= htmlspecialchars($note['title']) ?></span>
                    <span class="akd-dash-announce__text"><?= htmlspecialchars($note['text']) ?></span>
                </span>
            </li>
        <?php endforeach; ?>
    </ul>
</section>