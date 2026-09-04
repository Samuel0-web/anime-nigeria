<?php

/**
 * Coming Soon overview/rundown. Deliberately has no timeline and no
 * rounds (enforced in challenges.php), this is a preview of the
 * lifecycle, not a partially-active challenge. The "what you'll need"
 * list reads entirely from akd_challenge_media_config($challenge['type']),
 * so it adapts automatically for any challenge type.
 *
 * Expects the following to be defined by the parent view (challenges.php):
 *
 * @var array{
 *     phases: array<string, array<string, mixed>>,
 *     phase: string,
 *     lifecycle: list<array{key: string, title: string, description: string}>,
 *     type: string,
 *     rules: array{title: array{min: int, max: int}},
 * } $challenge
 */

$phase      = akd_challenge_phase_config($challenge['phases'], $challenge['phase']);
$media      = akd_challenge_media_config($challenge['type']);
$titleRules = $challenge['rules']['title'];
?>
<section class="akd-challenge-panel akd-challenge-panel--overview" aria-labelledby="challenge-panel-heading">
    <h2 class="akd-challenge-panel__heading" id="challenge-panel-heading"><?= htmlspecialchars($phase['heading']) ?></h2>
    <p class="akd-challenge-panel__desc"><?= htmlspecialchars($phase['description']) ?></p>

    <?php if (!empty($phase['note'])): ?>
        <p class="akd-challenge-panel__note">
            <i class="fa-solid fa-calendar" aria-hidden="true"></i>
            <?= htmlspecialchars($phase['note']) ?>
        </p>
    <?php endif; ?>

    <p class="akd-challenge-panel__sub-heading">What to expect</p>
    <ol class="akd-challenge-overview__steps">
        <?php foreach ($challenge['lifecycle'] as $index => $step): ?>
            <li class="akd-challenge-overview__step">
                <span class="akd-challenge-overview__step-index" aria-hidden="true"><?= $index + 1 ?></span>
                <div class="akd-challenge-overview__step-body">
                    <p class="akd-challenge-overview__step-title"><?= htmlspecialchars($step['title']) ?></p>
                    <p class="akd-challenge-overview__step-desc"><?= htmlspecialchars($step['description']) ?></p>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>

    <p class="akd-challenge-panel__sub-heading">What you'll need</p>
    <ul class="akd-challenge-overview__rules">
        <li class="akd-challenge-overview__rule">
            <i class="fa-solid <?= htmlspecialchars($media['fileIcon']) ?>" aria-hidden="true"></i>
            <?= htmlspecialchars($media['acceptLabel']) ?>
        </li>
        <li class="akd-challenge-overview__rule">
            <i class="fa-solid fa-weight-hanging" aria-hidden="true"></i>
            Up to <?= (int) $media['maxSizeMb'] ?> MB
        </li>
        <li class="akd-challenge-overview__rule">
            <i class="fa-solid fa-heading" aria-hidden="true"></i>
            <?= (int) $titleRules['min'] ?> to <?= (int) $titleRules['max'] ?> character title
        </li>
    </ul>
</section>