<?php
/**
 * @var array<string,mixed> $comment
 * @var array<string,mixed> $user
 */
$flatReplies = akd_blog_flatten_replies($comment['replies'] ?? []);
usort($flatReplies, static function (array $a, array $b): int {
    return strtotime($a['created_at']) <=> strtotime($b['created_at']);
});
$totalReplies = count($flatReplies);
$initialVisible = min(2, $totalReplies);
$currentUsername = $user['username'] ?? '';
$isOwnComment = $currentUsername !== '' && strcasecmp($comment['username'], $currentUsername) === 0;
?>
<article class="akd-comment akd-comment-thread" data-comment-id="<?= (int) $comment['id'] ?>">
    <?php if ($isOwnComment): ?>
        <span class="akd-comment-avatar" style="background-color: <?= htmlspecialchars($comment['avatar_color']) ?>;" aria-hidden="true">
            <?= htmlspecialchars($comment['initials']) ?>
        </span>
    <?php else: ?>
        <a class="akd-comment-avatar" style="background-color: <?= htmlspecialchars($comment['avatar_color']) ?>;" href="/member/player/<?= htmlspecialchars($comment['username']) ?>" aria-hidden="true">
            <?= htmlspecialchars($comment['initials']) ?>
        </a>
    <?php endif; ?>
    <div class="akd-comment__body" data-comment-tap-target>
        <div class="akd-comment__meta">
            <?php if ($isOwnComment): ?>
                <span class="akd-comment__profile-link akd-comment__profile-link--self">
                    <span class="akd-comment__author"><?= htmlspecialchars($comment['fullname']) ?></span>
                    <span class="akd-comment__username">@<?= htmlspecialchars($comment['username']) ?></span>
                </span>
            <?php else: ?>
                <a class="akd-comment__profile-link" href="/member/player/<?= htmlspecialchars($comment['username']) ?>">
                    <span class="akd-comment__author"><?= htmlspecialchars($comment['fullname']) ?></span>
                    <span class="akd-comment__username">@<?= htmlspecialchars($comment['username']) ?></span>
                </a>
            <?php endif; ?>
        </div>
        <p class="akd-comment__text"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
        <div class="akd-comment__actions">
            <span class="akd-comment__time"><?= htmlspecialchars(akd_blog_relative_time($comment['created_at'])) ?></span>
            <span class="akd-comment__dot" aria-hidden="true">&middot;</span>
            <button type="button" class="akd-comment__reply-btn" data-reply-toggle data-reply-name="<?= htmlspecialchars($comment['fullname']) ?>" data-reply-username="<?= htmlspecialchars($comment['username']) ?>">Reply</button>
            <?php if ($isOwnComment): ?>
                <span class="akd-comment__dot" aria-hidden="true">&middot;</span>
                <button type="button" class="akd-comment__delete-btn" data-comment-delete data-delete-target="comment">Delete</button>
            <?php endif; ?>
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
                <?php foreach ($flatReplies as $index => $reply): ?>
                    <?php $isOwnReply = $currentUsername !== '' && strcasecmp($reply['username'], $currentUsername) === 0; ?>
                    <article
                        class="akd-comment akd-comment--reply<?= $reply['reply_to_username'] ? ' akd-comment--nested-reply' : '' ?><?= $index >= $initialVisible ? ' is-hidden-reply' : '' ?>"
                        data-comment-id="<?= (int) $reply['id'] ?>"
                        data-parent-id="<?= (int) $comment['id'] ?>"
                        data-reply-to-username="<?= htmlspecialchars($reply['reply_to_username'] ?? '') ?>"
                        data-reply-index="<?= $index ?>"
                    >
                        <?php if ($isOwnReply): ?>
                            <span class="akd-comment-avatar akd-comment-avatar--sm" style="background-color: <?= htmlspecialchars($reply['avatar_color']) ?>;" aria-hidden="true">
                                <?= htmlspecialchars($reply['initials']) ?>
                            </span>
                        <?php else: ?>
                            <a class="akd-comment-avatar akd-comment-avatar--sm" style="background-color: <?= htmlspecialchars($reply['avatar_color']) ?>;" href="/member/player/<?= htmlspecialchars($reply['username']) ?>" aria-hidden="true">
                                <?= htmlspecialchars($reply['initials']) ?>
                            </a>
                        <?php endif; ?>
                        <div class="akd-comment__body">
                            <div class="akd-comment__meta">
                                <?php if ($isOwnReply): ?>
                                    <span class="akd-comment__profile-link akd-comment__profile-link--self">
                                        <span class="akd-comment__author"><?= htmlspecialchars($reply['fullname']) ?></span>
                                        <span class="akd-comment__username">@<?= htmlspecialchars($reply['username']) ?></span>
                                    </span>
                                <?php else: ?>
                                    <a class="akd-comment__profile-link" href="/member/player/<?= htmlspecialchars($reply['username']) ?>">
                                        <span class="akd-comment__author"><?= htmlspecialchars($reply['fullname']) ?></span>
                                        <span class="akd-comment__username">@<?= htmlspecialchars($reply['username']) ?></span>
                                    </a>
                                <?php endif; ?>
                                <?php if ($reply['reply_to_username']): ?>
                                    <span class="akd-comment__reply-target">
                                        <i class="fa-solid fa-caret-right" aria-hidden="true"></i> @<?= htmlspecialchars($reply['reply_to_username']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="akd-comment__text"><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
                            <div class="akd-comment__actions">
                                <span class="akd-comment__time"><?= htmlspecialchars(akd_blog_relative_time($reply['created_at'])) ?></span>
                                <span class="akd-comment__dot" aria-hidden="true">&middot;</span>
                                <button type="button" class="akd-comment__reply-btn" data-reply-toggle data-reply-name="<?= htmlspecialchars($reply['fullname']) ?>" data-reply-username="<?= htmlspecialchars($reply['username']) ?>">Reply</button>
                                <?php if ($isOwnReply): ?>
                                    <span class="akd-comment__dot" aria-hidden="true">&middot;</span>
                                    <button type="button" class="akd-comment__delete-btn" data-comment-delete data-delete-target="reply">Delete</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="akd-comment-replies__controls" data-reply-controls<?= $totalReplies <= 2 ? ' hidden' : '' ?>>
                <?php $hiddenCount = $totalReplies - $initialVisible; ?>
                <button type="button" class="akd-comment-replies__toggle" data-reply-view aria-expanded="false">
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                    <span data-reply-view-label>View <?= $hiddenCount ?> <?= $hiddenCount === 1 ? 'reply' : 'replies' ?></span>
                </button>
                <button type="button" class="akd-comment-replies__hide" data-reply-hide hidden>Hide</button>
            </div>
        <?php endif; ?>
    </div>
</article>