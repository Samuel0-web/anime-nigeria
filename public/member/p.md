# Anime Nigeria — Achievements Improvements (Do NOT Rebuild Existing Profile)

This is an incremental update.

Do NOT regenerate or redesign the existing Profile page.

Instead, tell me exactly:

- which HTML blocks should be edited
- which SCSS selectors should be updated
- which JavaScript modules should be modified

Only generate the new pieces required below.


----------------------------------------------------
1. RENAME SECTION
----------------------------------------------------

Rename the profile section from:

Badges & Achievements

to simply:

Achievements

Keep everything else visually consistent.


----------------------------------------------------
2. PROFILE PAGE PREVIEW
----------------------------------------------------

The Profile page is only a preview.

Do NOT display every achievement.

Display exactly:

• 8 achievement cards

on every screen size.

No responsive reduction.

If more achievements exist, display a "View All" action in the section header.

Example:

Achievements                        View All →

The action should be lightweight and elegant.

Desktop:

Use normal text:

View All →

Mobile:

Allow the text to wrap naturally if necessary.

Do NOT replace it with an icon-only button.

Keeping the wording is more important than saving a few pixels.

The Profile page should never display more than eight achievements.


----------------------------------------------------
3. DEDICATED ACHIEVEMENTS PAGE
----------------------------------------------------

Build a brand new page:

Achievements

This page contains every earned achievement.

Do NOT include locked achievements.

Do NOT include rarity.

Do NOT include progress.

Do NOT include filters.

Do NOT include search.

Do NOT include pagination.

Use hardcoded placeholder data.

The page should continue the same premium design language used throughout the dashboard.

Layout:

• Page heading

• Small description

• Responsive achievement grid

Desktop:
4 columns

Tablet:
3 columns

Mobile:
2 columns

Each card keeps the same styling already designed for the Profile preview.

This page simply expands the collection.


----------------------------------------------------
4. NEW BADGE LABEL
----------------------------------------------------

The newest earned achievement should display a subtle

NEW

label.

Placement:

Top-left or top-right corner.

Whichever feels more balanced.

The label automatically disappears after seven days.

For now this logic can be simulated with placeholder data.

The label should feel elegant.

Not loud.

Not oversized.


----------------------------------------------------
5. REUSE THE SAME MODAL
----------------------------------------------------

Do NOT generate a separate modal for the Profile page and another one for the Achievements page.

Both pages should reuse the exact same modal component.

The visual design should remain identical.


----------------------------------------------------
6. BUILD THE MODAL USING JAVASCRIPT
----------------------------------------------------

I will remove the achievement modal HTML from the Profile page.

Instead, generate the modal entirely from JavaScript.

Reason:

The Profile page and the dedicated Achievements page both need the same modal.

Creating it dynamically avoids duplicating HTML across multiple pages.

The JavaScript should:

• Create the modal once

• Append it to document.body

• Reuse it everywhere

• Populate it dynamically based on the clicked achievement

The modal should include:

• Achievement artwork

• Achievement title

• Description

• Date earned

• Small achievement story/explanation

Use placeholder data only.

The modal styling should reuse my existing modal SCSS.

Do NOT generate duplicate CSS.

Only create the HTML structure from JavaScript.


----------------------------------------------------
7. TRIGGERS
----------------------------------------------------

Do not rely on IDs.

Multiple pages will contain achievement cards.

Use shared classes and data attributes instead.

Example:

.achievement-card

data-achievement-id

Every achievement card should call the exact same openAchievementModal() function.

One JavaScript module should power every page.


----------------------------------------------------
8. ACTIVITY TIMELINE
----------------------------------------------------

The Profile page Activity Timeline remains a preview.

Display exactly:

10 recent activities.

There is NO "View All" button.

Users do not need a dedicated activity history page at this stage.

Use hardcoded placeholder activities.

No pagination.

No infinite scrolling.

No filters.


----------------------------------------------------
GENERAL REQUIREMENTS
----------------------------------------------------

This is an enhancement pass.

Do NOT redesign the Profile page.

Do NOT regenerate code that already exists unless it must change.

Clearly indicate:

• Which HTML sections change

• Which SCSS selectors change

• Which JavaScript module(s) should be added or edited

Maintain the existing premium 2026 Anime Nigeria dashboard aesthetic.

Everything must remain responsive across mobile, tablet, and desktop.