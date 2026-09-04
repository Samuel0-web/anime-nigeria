<?php

/**
 * Renders one submission. Media (audio vs image) is driven by the
 * challenge's own type via $mediaKind, set by whichever phase partial
 * includes this file, so this markup adapts to any challenge type
 * without checking $entry itself.
 *
 * @var array{
 *     id: string,
 *     user: array{id: int, username: string},
 *     description: string,
 *     file: string,
 * } $entry
 * @var bool $votingActive Whether the Vote control should render for this card.
 * @var string $mediaKind 'audio' or 'image'.
 */

$caption = akd_challenge_submission_caption($entry);
?>
<article class="akd-challenge-submission akd-challenge-submission--<?= htmlspecialchars($mediaKind) ?>"
    data-submission-id="<?= htmlspecialchars($entry['id']) ?>"
>
    <header class="akd-challenge-submission__header">
        <h3 class="akd-challenge-submission__title"><?= htmlspecialchars($entry['description']) ?></h3>
        <p class="akd-challenge-submission__by">by <?= htmlspecialchars($caption) ?></p>
    </header>

    <?php if ($mediaKind === 'audio'): ?>
        <div class="akd-challenge-submission__media akd-challenge-submission__media--audio">
            <audio controls preload="none" class="akd-challenge-submission__audio">
                <source src="<?= htmlspecialchars($entry['file']) ?>">
                Your browser does not support the audio element.
            </audio>
        </div>
    <?php else: ?>
        <div class="akd-challenge-submission__media akd-challenge-submission__media--image">
            <img src="<?= htmlspecialchars($entry['file']) ?>" alt="<?= htmlspecialchars($entry['description']) ?>"
                class="akd-challenge-submission__image" loading="lazy"
            >
        </div>
    <?php endif; ?>

    <?php if ($votingActive): ?>
        <div class="akd-challenge-submission__footer">
            <button type="button" class="akd-challenge-vote-btn" data-vote-btn
                data-submission-id="<?= htmlspecialchars($entry['id']) ?>"
                aria-pressed="false"
            >
                <i class="fa-solid fa-check akd-challenge-vote-btn__icon" aria-hidden="true"></i>
                <span class="akd-challenge-vote-btn__label">Vote</span>
            </button>
        </div>
    <?php endif; ?>
</article>