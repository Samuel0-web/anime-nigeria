<?php
/**
 * The ANCA nomination wall: an editorial list of categories that each
 * expand inline into a nominee-selection workspace. All interactivity
 * (search, select, reason, review, submit, edit) runs client-side in
 * resources/js/member/community-awards-nominations.js. This partial's
 * job is to hand that script the category list and the pool of
 * nominatable community members via a JSON script tag, and render the
 * static shell each category row expands into.
 *
 * Expects the following to be defined by the parent view (nominations.php):
 *
 * @var array{
 *     categories: list<array{slug: string, name: string, blurb: string, prompt: string}>,
 *     nomination: array{reasonMaxLength: int},
 * } $ancaOverview
 * @var list<array{id: string, fullname: string, username: string, accent: string}> $nominationMembers
 */

$categories = $ancaOverview['categories'];
$reasonMax  = $ancaOverview['nomination']['reasonMaxLength'];

$boardData = [
    'categories' => array_map(
        static fn(array $c): array => [
            'slug'   => $c['slug'],
            'name'   => $c['name'],
            'prompt' => $c['prompt'],
        ],
        $categories
    ),
    'members'      => $nominationMembers,
    'reasonMaxLen' => $reasonMax,
];
?>
<section class="akd-anca-section" aria-labelledby="anca-board-heading">
    <header class="akd-anca-section__header">
        <p class="akd-anca-section__eyebrow">Nominations</p>
        <h2 class="akd-anca-section__title" id="anca-board-heading">Who deserves it this year</h2>
        <p class="akd-anca-section__desc">Pick a category, think of someone, and put their name forward. You can come back and change any nomination while nominations remain open.</p>
    </header>

    <div class="akd-anca-your-noms">
        <p class="akd-anca-your-noms__label">Your nominations</p>
        <ul class="akd-anca-your-noms__list" data-nom-summary-list aria-live="polite"></ul>
    </div>

    <div class="akd-anca-board" data-nom-board>
        <script type="application/json" data-nom-board-data><?= json_encode($boardData, JSON_UNESCAPED_SLASHES) ?></script>

        <?php foreach ($categories as $i => $category): ?>
            <div class="akd-anca-nom-row" data-nom-row data-category="<?= htmlspecialchars($category['slug']) ?>">
                <span class="akd-anca-nom-row__index" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>

                <div class="akd-anca-nom-row__main">
                    <div class="akd-anca-nom-row__collapsed" data-nom-collapsed>
                        <h3 class="akd-anca-nom-row__name"><?= htmlspecialchars($category['name']) ?></h3>
                        <p class="akd-anca-nom-row__blurb"><?= htmlspecialchars($category['blurb']) ?></p>
                        <div class="akd-anca-nom-row__action-area" data-nom-action-area>
                            <button type="button" class="akd-anca-nom-row__action" data-nom-action>
                                Nominate someone <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <div class="akd-anca-nom-row__expanded" data-nom-expanded hidden>
                        <div class="akd-anca-nom-row__expanded-content" data-nom-content></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>