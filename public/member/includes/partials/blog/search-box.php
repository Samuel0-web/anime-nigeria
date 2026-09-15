<?php
/**
 * @var array<int,array<string,mixed>> $blogArticles
 * @var string|null $searchBoxValue Optional. Pre-fills the input, used
 *      on the search results page to reflect the current query.
 */
$searchBoxValue ??= '';
?>
<div class="akd-blog-search" data-blog-search>
    <div class="akd-blog-search__field">
        <i class="fa-solid fa-magnifying-glass akd-blog-search__icon" aria-hidden="true"></i>
        <input
            type="search"
            class="akd-blog-search__input"
            placeholder="Search articles..."
            aria-label="Search articles"
            autocomplete="off"
            value="<?= htmlspecialchars($searchBoxValue) ?>"
            data-blog-search-input
        >
        <button type="button" class="akd-blog-search__clear" data-blog-search-clear aria-label="Clear search" <?= $searchBoxValue === '' ? 'hidden' : '' ?>>
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>

    <div class="akd-blog-search__results" data-blog-search-results role="listbox" aria-label="Search results" hidden></div>
</div>

<script type="application/json" id="akdBlogDataset"><?= json_encode(array_map(static function (array $a) {
    return [
        'slug' => $a['slug'],
        'title' => $a['title'],
        'excerpt' => $a['excerpt'],
        'category' => $a['category'],
        'tags' => $a['tags'],
    ];
}, $blogArticles), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>