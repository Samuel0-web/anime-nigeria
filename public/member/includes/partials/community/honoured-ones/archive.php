<?php
/**
 * The interactive archive shell. Year switching and category filtering
 * both run entirely client-side in resources/js/member/honoured-ones.js
 * against the JSON payload below, the same "JSON script tag plus a
 * small render loop" pattern already used by the Nominations and
 * Voting pages, kept independent since this page has its own data
 * shape and no submission flow at all.
 *
 * @var list<array{year: int, edition: string, winners: list<array{category: string, fullname: string, username: string, accent: string, reason?: string|null}>}> $editions
 */
?>
<section class="akd-honoured-archive" data-honoured-archive>
    <script type="application/json" data-honoured-archive-data><?= json_encode($editions, JSON_UNESCAPED_SLASHES) ?></script>

    <div class="akd-honoured-years" role="tablist" aria-label="ANCA edition" data-honoured-years></div>

    <header class="akd-honoured-archive__header">
        <h2 class="akd-honoured-archive__edition" id="honoured-archive-heading" data-honoured-edition></h2>
        <p class="akd-honoured-archive__note" data-honoured-note></p>
    </header>

    <div class="akd-honoured-filter" data-honoured-filter></div>

    <p class="akd-honoured-count" data-honoured-count aria-live="polite"></p>

    <div class="akd-honoured-wall" role="tabpanel" aria-labelledby="honoured-archive-heading" data-honoured-wall></div>
</section>