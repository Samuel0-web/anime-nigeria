<?php
/**
 * @var array<int,array{id:string,text:string}> $tocItems
 * @var string $publicShareUrl
 * @var array<string,mixed> $article
 */
$shareTitle = $article['title'];
$shareUrl = $publicShareUrl;
?>
<aside class="akd-post__sidebar">
    <?php if (count($tocItems) >= 2): ?>
        <div class="akd-post-toc" data-post-toc>
            <h2 class="akd-post-toc__heading">In this article</h2>
            <nav aria-label="Table of contents">
                <ul class="akd-post-toc__list">
                    <?php foreach ($tocItems as $item): ?>
                        <li><a href="#<?= htmlspecialchars($item['id']) ?>"><?= htmlspecialchars($item['text']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>

    <div class="akd-post-share">
        <h2 class="akd-post-share__heading">Share</h2>
        <div class="akd-post-share__actions">
            <a class="akd-post-share__btn" href="https://wa.me/?text=<?= urlencode($shareTitle . ' ' . $shareUrl) ?>" target="_blank" rel="noopener" aria-label="Share on WhatsApp">
                <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
            </a>
            <a class="akd-post-share__btn" href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>" target="_blank" rel="noopener" aria-label="Share on Facebook">
                <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
            </a>
            <a class="akd-post-share__btn" href="https://twitter.com/intent/tweet?url=<?= urlencode($shareUrl) ?>&text=<?= urlencode($shareTitle) ?>" target="_blank" rel="noopener" aria-label="Share on X">
                <i class="fa-brands fa-x-twitter" aria-hidden="true"></i>
            </a>
            <button type="button" class="akd-post-share__btn" data-copy-link="<?= htmlspecialchars($shareUrl) ?>" aria-label="Copy link">
                <i class="fa-solid fa-link" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</aside>