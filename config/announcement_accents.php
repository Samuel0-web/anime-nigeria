<?php
/**
 * Accent palette for announcement categories.
 *
 * Key  = the token stored in announcement_categories.accent. The member page
 *        maps it to colour with .akd-announce-row__category--{key}.
 * hex  = swatch colour used by the admin UI only.
 *
 * To add an accent: add an entry here AND the matching
 * .akd-announce-row__category--{key} modifier in member/_announcements.scss.
 * Nothing else changes.
 */
return [
    'gold'    => ['label' => 'Gold',    'hex' => '#DF9A1B'],
    'teal'    => ['label' => 'Teal',    'hex' => '#6BBFBA'],
    'violet'  => ['label' => 'Violet',  'hex' => '#A88CD8'],
    'ember'   => ['label' => 'Ember',   'hex' => '#E0935F'],
    'crimson' => ['label' => 'Crimson', 'hex' => '#D73B5D'],
    'sakura'  => ['label' => 'Sakura',  'hex' => '#E8A3B3'],
    'sky'     => ['label' => 'Sky',     'hex' => '#6AA9E0'],
    'lime'    => ['label' => 'Lime',    'hex' => '#9AC96B'],
    'indigo'  => ['label' => 'Indigo',  'hex' => '#7D86E8'],
    'slate'   => ['label' => 'Slate',   'hex' => '#8F9AA8'],
];