<?php
/**
 * @var array<int,array<string,mixed>> $hydratedComments
 * @var array<string,mixed> $user
 * @var string $avatarColor
 * @var string $userInitials
 */
?>
<section class="akd-post-comments" id="comments" aria-labelledby="akdCommentsHeading">
    <h2 class="akd-post-comments__heading" id="akdCommentsHeading">Comments</h2>

    <form class="akd-comment-composer" data-comment-form novalidate>
        <div class="akd-comment-composer__row">
            <span class="akd-comment-avatar" style="background-color: <?= htmlspecialchars($avatarColor) ?>;" aria-hidden="true">
                <?= htmlspecialchars($userInitials) ?>
            </span>
            <div class="akd-comment-composer__field">
                <label for="akdCommentInput" class="visually-hidden">Write a comment</label>
                <textarea id="akdCommentInput" class="akd-comment-composer__textarea" placeholder="Join the discussion..." maxlength="<?= AKD_BLOG_COMMENT_MAX_LENGTH ?>" data-comment-input rows="2"></textarea>
                <div class="akd-comment-composer__meta">
                    <span class="akd-comment-composer__error" data-comment-error hidden></span>
                    <span class="akd-comment-composer__count" data-comment-count>0/<?= AKD_BLOG_COMMENT_MAX_LENGTH ?></span>
                </div>
            </div>
        </div>
        <div class="akd-comment-composer__actions">
            <button type="submit" class="akd-comment-composer__submit" data-comment-submit>
                <span data-comment-submit-label>Post comment</span>
            </button>
        </div>
    </form>

    <div class="akd-comment-list<?= count($hydratedComments) > 10 ? ' is-scrollable' : '' ?>" data-comment-list>
        <?php if (empty($hydratedComments)): ?>
            <div class="akd-blog-empty akd-comment-empty" data-comment-empty>
                <p class="akd-blog-empty__title">No comments yet.</p>
                <p class="akd-blog-empty__body">Be the first to share your thoughts.</p>
            </div>
        <?php else: ?>
            <?php foreach ($hydratedComments as $comment): ?>
                <?php require __DIR__ . '/comment-item.php'; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<script type="application/json" id="akdCurrentUser"><?= json_encode([
    'fullname' => $user['fullname'] ?? 'Member',
    'initials' => $userInitials,
    'avatarColor' => $avatarColor,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>