<?php
/** @var array<int,string> $tags */
if (!empty($tags)): ?>
<div class="akd-post-tags">
    <h2 class="akd-post-tags__heading">Tags</h2>
    <div class="akd-post-tags__list">
        <?php foreach ($tags as $tag): ?>
            <span class="akd-post-tags__item"><?= htmlspecialchars($tag) ?></span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>