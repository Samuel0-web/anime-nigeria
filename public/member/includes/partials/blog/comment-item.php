<?php
/** @var array<string,mixed> $comment */
?>
<article class="akd-comment" data-comment-id="<?= (int) $comment['id'] ?>">
    <span class="akd-comment-avatar" style="background-color: <?= htmlspecialchars($comment['avatar_color']) ?>;" aria-hidden="true">
        <?= htmlspecialchars($comment['initials']) ?>
    </span>
    <div class="akd-comment__body">
        <div class="akd-comment__meta">
            <span class="akd-comment__author"><?= htmlspecialchars($comment['fullname']) ?></span>
            <span class="akd-comment__username">@<?= htmlspecialchars($comment['username']) ?></span>
            <span class="akd-comment__dot" aria-hidden="true">&bull;</span>
            <span class="akd-comment__time"><?= htmlspecialchars(akd_blog_relative_time($comment['created_at'])) ?></span>
        </div>
        <p class="akd-comment__text"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
    </div>
</article>