<?php
/**
 * Expects the following to be defined by the parent view (nominations.php):
 *
 * @var array{
 *     categories: list<array{slug: string, name: string, accent: string, entryType: string, filter?: array<string, mixed>|null}>,
 *     eligibleEntries: array<string, list<array<string, mixed>>>,
 *     nomination: array{limitPerCategory: int},
 * } $awardsOverview
 *
 * All actual interactivity (search, select, review, submit, per-category
 * limits) runs client-side in resources/js/member/awards-nominations.js.
 * This partial's only job is to hand that script a pre-filtered dataset
 * via a JSON script tag, so the JS never needs to know about
 * entryType/filter matching rules.
 */

$categories      = $awardsOverview['categories'];
$eligibleEntries = $awardsOverview['eligibleEntries'];
$limit           = $awardsOverview['nomination']['limitPerCategory'];

$categoriesMeta  = [];
$categoryEntries = [];

foreach ($categories as $category) {
    $entries = akd_award_entries_for_category($category, $eligibleEntries);

    $categoriesMeta[] = [
        'slug'   => $category['slug'],
        'name'   => $category['name'],
        'accent' => $category['accent'],
    ];

    $categoryEntries[$category['slug']] = array_map(
        static fn(array $entry): array => [
            'id'       => $entry['id'],
            'title'    => $entry['title'],
            'subtitle' => akd_award_entry_subtitle($category['entryType'], $entry),
        ],
        $entries
    );
}

$workspaceData = [
    'categories'       => $categoriesMeta,
    'categoryEntries'  => $categoryEntries,
    'limitPerCategory' => $limit,
];
?>
<section class="akd-award-section akd-nomination-workspace" aria-labelledby="anaa-workspace-heading" data-nomination-workspace>
    <header class="akd-award-section__header">
        <div class="akd-award-section__heading-group">
            <p class="akd-award-section__eyebrow">Your Nomination</p>
            <h2 class="akd-award-section__title" id="anaa-workspace-heading">Choose a category</h2>
            <p class="akd-award-section__desc">Your nominations help shape the awards. Pick a category, search for an eligible entry, and submit.</p>
        </div>
    </header>

    <script type="application/json" data-nomination-data><?= json_encode($workspaceData, JSON_UNESCAPED_SLASHES) ?></script>

    <div class="akd-nomination-grid">
        <nav class="akd-nomination-categories" aria-label="Nomination categories">
            <ul class="akd-nomination-categories__list" data-nomination-category-list></ul>
        </nav>

        <div class="akd-nomination-panel">
            <div class="akd-nomination-panel__search">
                <label class="akd-nomination-panel__search-label" for="anaa-nomination-search">Search eligible entries</label>
                <div class="akd-nomination-panel__search-field">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input
                        type="text"
                        id="anaa-nomination-search"
                        class="akd-nomination-panel__search-input"
                        placeholder="Search by title..."
                        autocomplete="off"
                        data-nomination-search
                    >
                </div>
            </div>

            <ul class="akd-nomination-results" data-nomination-results></ul>

            <div class="akd-nomination-review" data-nomination-review hidden></div>
        </div>
    </div>

    <div class="akd-nomination-submitted" data-nomination-submitted hidden>
        <h3 class="akd-nomination-submitted__title">Your nominations</h3>
        <ul class="akd-nomination-submitted__list" data-nomination-submitted-list></ul>
    </div>
</section>