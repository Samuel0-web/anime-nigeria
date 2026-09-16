<?php
/** @var array<int,array<string,mixed>> $contentBlocks */
?>
<div class="akd-post-content">
    <?php foreach ($contentBlocks as $block): ?>
        <?php switch ($block['type'] ?? null):
            case 'heading': ?>
                <h2 id="<?= htmlspecialchars($block['id'] ?? '') ?>" class="akd-post-content__heading"><?= htmlspecialchars($block['text']) ?></h2>
                <?php break;

            case 'paragraph': ?>
                <p class="akd-post-content__paragraph"><?= $block['text'] ?></p>
                <?php break;

            case 'list': ?>
                <?php $listTag = ($block['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul'; ?>
                <<?= $listTag ?> class="akd-post-content__list">
                    <?php foreach ($block['items'] as $item): ?>
                        <li><?= $item ?></li>
                    <?php endforeach; ?>
                </<?= $listTag ?>>
                <?php break;

            case 'quote': ?>
                <blockquote class="akd-post-content__quote">
                    <p><?= $block['text'] ?></p>
                    <?php if (!empty($block['cite'])): ?>
                        <?= htmlspecialchars($block['cite']) ?>
                    <?php endif; ?>
                </blockquote>
                <?php break;

            case 'image': ?>
                <figure class="akd-post-content__figure">
                    <?php /* Anchor is the Fancybox trigger; href is the full-size URL. */ ?>
                    <a href="<?= htmlspecialchars($block['src']) ?>"
                        data-fancybox
                        <?php if (!empty($block['caption'])): ?>data-caption="<?= htmlspecialchars($block['caption'], ENT_QUOTES) ?>"<?php endif; ?>
                    >
                        <img src="<?= htmlspecialchars($block['src']) ?>"
                            alt="<?= htmlspecialchars($block['alt'] ?? '') ?>"
                            class="akd-post-content__image" loading="lazy"
                        >
                    </a>
                    <?php if (!empty($block['caption'])): ?>
                        <figcaption class="akd-post-content__caption"><?= htmlspecialchars($block['caption']) ?></figcaption>
                    <?php endif; ?>
                </figure>
                <?php break;

            case 'separator': ?>
                <hr class="akd-post-content__separator">
                <?php break;
        endswitch; ?>
    <?php endforeach; ?>
</div>