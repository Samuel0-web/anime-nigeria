<?php
/** @var array<string,mixed> $comment */
$replies = $comment['replies'] ?? [];
$totalReplies = count($replies);
$initialVisible = min(2, $totalReplies);
?>
<article class="akd-comment" data-comment-id="<?= (int) $comment['id'] ?>">
    <span class="akd-comment-avatar" style="background-color: <?= htmlspecialchars($comment['avatar_color']) ?>;" aria-hidden="true">
        <?= htmlspecialchars($comment['initials']) ?>
    </span>
    <div class="akd-comment__body" data-comment-tap-target>
        <div class="akd-comment__meta">
            <span class="akd-comment__author"><?= htmlspecialchars($comment['fullname']) ?></span>
            <span class="akd-comment__username">@<?= htmlspecialchars($comment['username']) ?></span>
        </div>
        <p class="akd-comment__text"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
        <div class="akd-comment__actions">
            <span class="akd-comment__time"><?= htmlspecialchars(akd_blog_relative_time($comment['created_at'])) ?></span>
            <span class="akd-comment__dot" aria-hidden="true">&middot;</span>
            <button type="button" class="akd-comment__reply-btn" data-reply-toggle data-reply-name="<?= htmlspecialchars($comment['fullname']) ?>">Reply</button>
        </div>

        <div class="akd-reply-composer" data-reply-composer hidden>
            <div class="akd-comment-composer__row">
                <span class="akd-comment-avatar akd-comment-avatar--sm" data-reply-avatar aria-hidden="true"></span>
                <div class="akd-comment-composer__field">
                    <label class="visually-hidden" data-reply-label>Reply</label>
                    <textarea class="akd-comment-composer__textarea akd-reply-composer__textarea" data-reply-input maxlength="<?= AKD_BLOG_COMMENT_MAX_LENGTH ?>" placeholder="Write a reply..." rows="1"></textarea>
                    <div class="akd-comment-composer__meta">
                        <span class="akd-comment-composer__error" data-reply-error hidden></span>
                        <span class="akd-comment-composer__count" data-reply-count>0/<?= AKD_BLOG_COMMENT_MAX_LENGTH ?></span>
                    </div>
                </div>
            </div>
            <div class="akd-comment-composer__actions">
                <button type="button" class="akd-comment-composer__cancel" data-reply-cancel>Cancel</button>
                <button type="button" class="akd-comment-composer__submit" data-reply-submit>
                    <span data-reply-submit-label>Reply</span>
                </button>
            </div>
        </div>

        <?php if ($totalReplies > 0): ?>
            <div class="akd-comment-replies" data-reply-list data-total-replies="<?= $totalReplies ?>" data-visible-replies="<?= $initialVisible ?>">
                <?php foreach ($replies as $index => $reply): ?>
                    <article class="akd-comment akd-comment--reply<?= $index >= $initialVisible ? ' is-hidden-reply' : '' ?>" data-reply-index="<?= $index ?>">
                        <span class="akd-comment-avatar akd-comment-avatar--sm" style="background-color: <?= htmlspecialchars($reply['avatar_color']) ?>;" aria-hidden="true">
                            <?= htmlspecialchars($reply['initials']) ?>
                        </span>
                        <div class="akd-comment__body">
                            <div class="akd-comment__meta">
                                <span class="akd-comment__author"><?= htmlspecialchars($reply['fullname']) ?></span>
                                <span class="akd-comment__username">@<?= htmlspecialchars($reply['username']) ?></span>
                            </div>
                            <p class="akd-comment__text"><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
                            <div class="akd-comment__actions">
                                <span class="akd-comment__time"><?= htmlspecialchars(akd_blog_relative_time($reply['created_at'])) ?></span>
                                <span class="akd-comment__dot" aria-hidden="true">&middot;</span>
                                <button type="button" class="akd-comment__reply-btn" data-reply-toggle data-reply-name="<?= htmlspecialchars($reply['fullname']) ?>">Reply</button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalReplies > 2): $hiddenCount = $totalReplies - $initialVisible; ?>
                <button type="button" class="akd-comment-replies__toggle" data-reply-expand aria-expanded="false">
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                    <span data-reply-expand-label>View <?= $hiddenCount ?> <?= $hiddenCount === 1 ? 'reply' : 'replies' ?></span>
                </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</article>