<?php

/**
 * Expects the following to be defined by the parent view (challenges.php):
 *
 * @var array{
 *     phases: array<string, array<string, mixed>>,
 *     phase: string,
 *     submission: array{instructions: list<string>},
 *     submissions: array{total: int, max: int},
 *     type: string,
 *     rules: array{title: array{min: int, max: int}},
 * } $challenge
 */

$phase       = akd_challenge_phase_config($challenge['phases'], $challenge['phase']);
$capacity    = akd_challenge_submission_capacity($challenge);
$media       = akd_challenge_media_config($challenge['type']);
$titleRules  = $challenge['rules']['title'];
$percentFull = min(100, (int) round($capacity['total'] / max(1, $capacity['max']) * 100));
?>
<section class="akd-challenge-panel akd-challenge-panel--submissions" id="submit-entry"
    aria-labelledby="challenge-panel-heading"
>
    <h2 class="akd-challenge-panel__heading" id="challenge-panel-heading"><?= htmlspecialchars($phase['heading']) ?></h2>
    <p class="akd-challenge-panel__desc"><?= htmlspecialchars($phase['description']) ?></p>

    <div class="akd-challenge-capacity<?= $capacity['isFull'] ? ' akd-challenge-capacity--full' : '' ?>">
        <div class="akd-challenge-capacity__bar" aria-hidden="true">
            <div class="akd-challenge-capacity__bar-fill" style="width: <?= $percentFull ?>%"></div>
        </div>
        <p class="akd-challenge-capacity__text">
            <?php if ($capacity['isFull']): ?>
                Submissions are full (<?= (int) $capacity['max'] ?> / <?= (int) $capacity['max'] ?>)
            <?php else: ?>
                <?= (int) $capacity['total'] ?> / <?= (int) $capacity['max'] ?> submissions
                &bull; <?= (int) $capacity['remaining'] ?> spots remaining
            <?php endif; ?>
        </p>
    </div>

    <?php if ($capacity['isFull']): ?>
        <p class="akd-challenge-panel__note">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            New submissions are no longer being accepted for this challenge. Review and voting will follow.
        </p>
    <?php else: ?>
        <div class="akd-challenge-submit">
            <ul class="akd-challenge-submit__instructions">
                <?php foreach ($challenge['submission']['instructions'] as $instruction): ?>
                    <li><?= htmlspecialchars($instruction) ?></li>
                <?php endforeach; ?>
            </ul>

            <div class="akd-challenge-submit__panel" data-submission-flow>
                <form class="akd-challenge-submit__form" data-mock-submit-form novalidate>
                    <div class="akd-challenge-submit__field">
                        <label for="challenge-entry-title">Entry title</label>
                        <input type="text" id="challenge-entry-title" name="title"
                            placeholder="e.g. Domain Expansion: Infinite Void"
                            minlength="<?= (int) $titleRules['min'] ?>" maxlength="<?= (int) $titleRules['max'] ?>"
                            data-title-input required
                        >
                        <div class="akd-challenge-field-meta">
                            <p class="akd-challenge-field-error" data-title-error hidden></p>
                            <p class="akd-challenge-char-count" data-char-count>0 / <?= (int) $titleRules['max'] ?></p>
                        </div>
                    </div>

                    <div class="akd-challenge-file-field" data-file-field
                        data-accept="<?= htmlspecialchars(implode(',', $media['accept'])) ?>"
                        data-max-size-mb="<?= (int) $media['maxSizeMb'] ?>"
                        data-accept-label="<?= htmlspecialchars($media['acceptLabel']) ?>"
                        data-previewable="<?= $media['previewable'] ? '1' : '0' ?>"
                    >
                        <label class="akd-challenge-file-field__label" for="challenge-entry-file"><?= htmlspecialchars($media['fieldLabel']) ?></label>
                        <p class="akd-challenge-file-field__rules">
                            Accepted formats: <?= htmlspecialchars($media['acceptLabel']) ?><br>
                            Maximum size: <?= (int) $media['maxSizeMb'] ?> MB
                        </p>

                        <div class="akd-challenge-dropzone" data-dropzone tabindex="0" role="button"
                            aria-label="<?= htmlspecialchars($media['dropzoneLabel']) ?> or drag and drop"
                        >
                            <i class="fa-solid <?= htmlspecialchars($media['uploadIcon']) ?> akd-challenge-dropzone__icon" aria-hidden="true"></i>
                            <span class="akd-challenge-dropzone__text"><?= htmlspecialchars($media['dropzoneLabel']) ?><br>or drag and drop</span>
                            <input type="file" id="challenge-entry-file" name="file"
                                accept="<?= htmlspecialchars(implode(',', $media['accept'])) ?>"
                                class="akd-challenge-dropzone__input" data-file-input required
                            >
                        </div>

                        <div class="akd-challenge-file-selected" data-file-selected hidden>
                            <?php if ($media['previewable']): ?>
                                <img class="akd-challenge-file-selected__preview" data-file-preview alt="" hidden>
                            <?php endif; ?>
                            <i class="fa-solid <?= htmlspecialchars($media['fileIcon']) ?> akd-challenge-file-selected__icon" data-file-icon aria-hidden="true"></i>
                            <div class="akd-challenge-file-selected__body">
                                <p class="akd-challenge-file-selected__name" data-file-name></p>
                                <p class="akd-challenge-file-selected__meta" data-file-meta></p>
                            </div>
                            <button type="button" class="akd-challenge-file-selected__remove" data-file-remove aria-label="Remove file">
                                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            </button>
                        </div>

                        <p class="akd-challenge-field-error" data-file-error hidden></p>
                    </div>

                    <button type="submit" class="akd-challenge-btn akd-challenge-btn--primary" data-mock-submit-btn>
                        Submit Entry
                    </button>
                </form>

                <div class="akd-challenge-submit-success" data-submission-success hidden>
                    <i class="fa-solid fa-circle-check akd-challenge-submit-success__icon" aria-hidden="true"></i>
                    <h3 class="akd-challenge-submit-success__title">Submission received</h3>
                    <p class="akd-challenge-submit-success__desc">Your entry has been submitted successfully.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>