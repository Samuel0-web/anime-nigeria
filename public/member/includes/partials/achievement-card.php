<?php /** @var array $achievement — expects one achievement row from achievements.php */ ?>
<button
    type="button"
    class="akd-achievement-card"
    data-achievement-id="<?= htmlspecialchars($achievement['id']) ?>"
    data-achievement-icon="<?= htmlspecialchars($achievement['icon']) ?>"
    data-achievement-name="<?= htmlspecialchars($achievement['name']) ?>"
    data-achievement-desc="<?= htmlspecialchars($achievement['desc']) ?>"
    data-achievement-date="<?= htmlspecialchars($achievement['date']) ?>"
    data-achievement-story="<?= htmlspecialchars($achievement['story']) ?>"
>
    <?php if (!empty($achievement['isNew'])): ?>
        <span class="akd-achievement-card__new">New</span>
    <?php endif; ?>
    <span class="akd-achievement-card__icon-wrap"><i class="<?= htmlspecialchars($achievement['icon']) ?>"></i></span>
    <span class="akd-achievement-card__name"><?= htmlspecialchars($achievement['name']) ?></span>
    <span class="akd-achievement-card__desc"><?= htmlspecialchars($achievement['desc']) ?></span>
    <span class="akd-achievement-card__date">Earned <?= htmlspecialchars($achievement['date']) ?></span>
</button>