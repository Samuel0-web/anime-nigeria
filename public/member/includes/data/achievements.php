<?php
/**
 * Shared achievement data — hardcoded placeholder until the backend
 * provides earned achievements. Used by both the Profile preview
 * (member/profile.php) and the full Achievements page (member/achievements.php).
 *
 * 'isNew' simulates the "earned within the last 7 days" state from the
 * design brief. It's a manual flag for now, not date-driven — flip it
 * per item once real earned-timestamps exist.
 */

return [
    ['id' => 'founding-member',     'icon' => 'fa-solid fa-star',         'name' => 'Founding Member',     'desc' => 'One of the first to join Anime Nigeria.',                'date' => 'Jan 2025', 'story' => 'You were here from day one, helping shape the community before it had a name for most people. Founding Members hold a permanent place in Anime Nigeria\'s history.', 'isNew' => false],
    ['id' => 'first-vote',          'icon' => 'fa-solid fa-check-square', 'name' => 'First Vote',          'desc' => 'Cast your first vote in the Anime Awards.',              'date' => 'Feb 2025', 'story' => 'Every great tradition starts with a single vote. Yours helped decide who took home Anime Nigeria\'s very first set of awards.', 'isNew' => false],
    ['id' => 'trivia-novice',       'icon' => 'fa-solid fa-brain',        'name' => 'Trivia Novice',       'desc' => 'Completed your first trivia round.',                     'date' => 'Mar 2025', 'story' => 'You stepped into the trivia arena for the first time and came out the other side a little sharper.', 'isNew' => false],
    ['id' => 'trivia-champion',     'icon' => 'fa-solid fa-crown',        'name' => 'Trivia Champion',     'desc' => 'Won a weekly trivia challenge.',                          'date' => 'May 2025', 'story' => 'Out of all participants, you came out on top. The crown is well earned.', 'isNew' => false],
    ['id' => 'community-voice',     'icon' => 'fa-solid fa-comments',     'name' => 'Community Voice',     'desc' => 'Left 25 comments in the community.',                     'date' => 'Jun 2025', 'story' => 'Conversations are what make a community feel alive, and you\'ve been showing up for them.', 'isNew' => false],
    ['id' => 'rising-star',         'icon' => 'fa-solid fa-bolt',         'name' => 'Rising Star',         'desc' => 'Reached Level 10.',                                       'date' => 'Aug 2025', 'story' => 'Ten levels in, and you\'re just getting started. Keep climbing.', 'isNew' => false],
    ['id' => 'perfect-score',       'icon' => 'fa-solid fa-bullseye',     'name' => 'Perfect Score',       'desc' => 'Answered every question correctly in a trivia round.',   'date' => 'Oct 2025', 'story' => 'Not a single miss. A flawless run through an entire trivia round.', 'isNew' => false],
    ['id' => 'gallery-contributor', 'icon' => 'fa-solid fa-image',        'name' => 'Gallery Contributor', 'desc' => 'Shared fan art in the community gallery.',               'date' => 'Nov 2025', 'story' => 'You added your own creativity to the gallery, giving the community something new to admire.', 'isNew' => false],
    ['id' => 'anniversary',         'icon' => 'fa-solid fa-cake-candles', 'name' => 'Anniversary',         'desc' => 'Celebrated one year with Anime Nigeria.',                'date' => 'Jan 2026', 'story' => 'A full year of votes, trivia nights and conversations. Here\'s to many more.', 'isNew' => true],
];