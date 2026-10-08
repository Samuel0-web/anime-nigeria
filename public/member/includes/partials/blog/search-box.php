<?php
/**
 * @var string|null $searchBoxValue Optional. Pre-fills the input on the search results page.
 */
$searchBoxValue ??= '';
?>
<div class="akd-blog-search" data-blog-search
    data-endpoint="/member/api/blog/search" data-search-page="/member/blog/search"
>
    <div class="akd-blog-search__field">
        <i class="fa-solid fa-magnifying-glass akd-blog-search__icon" aria-hidden="true"></i>

        <input type="search" class="akd-blog-search__input" placeholder="Search articles..."
            aria-label="Search articles" autocomplete="off"
            value="<?= htmlspecialchars($searchBoxValue) ?>" data-blog-search-input
        >
        
        <button type="button" class="akd-blog-search__clear" data-blog-search-clear aria-label="Clear search" <?= $searchBoxValue === '' ? 'hidden' : '' ?>>
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>

    <div class="akd-blog-search__results" data-blog-search-results role="listbox" aria-label="Search results" hidden></div>
</div>