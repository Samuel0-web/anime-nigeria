<?php
$page_title       = 'Community Challenges';
$page_description = 'Submit, listen, and vote in the current Anime Nigeria community challenge.';

$breadcrumbs = [
    ['label' => 'Dashboard',  'url' => '/dashboard'],
    ['label' => 'Community',  'url' => null],
    ['label' => 'Challenges', 'url' => null],
];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/data/challenges-support.php';

/** @var array<string, mixed> $challenge */
$challenge = require __DIR__ . '/../includes/data/challenges-data.php';

$displayRoundId = akd_challenge_display_round_id($challenge);
$partialsDir    = __DIR__ . '/../includes/partials/community/challenges';

// Maps each phase to the partial that owns its main experience.
// 'final' reuses the voting partial (same interaction, different
// copy/entries); 'completed' reuses the winner partial (adds a
// finalist recap). Nothing here duplicates challenge-data.php's
// own 'phase' value — it only decides which file to include.
$phasePartials = [
    'coming_soon' => 'phase-coming-soon.php',
    'submissions' => 'phase-submissions.php',
    'review'      => 'phase-review.php',
    'voting'      => 'phase-voting.php',
    'tie_break'   => 'phase-tie-break.php',
    'final'       => 'phase-voting.php',
    'winner'      => 'phase-winner.php',
    'completed'   => 'phase-winner.php',
];

$phasePartial = $phasePartials[$challenge['phase']] ?? 'phase-coming-soon.php';
$showRounds   = in_array($challenge['phase'], ['review', 'voting', 'final', 'completed'], true);
$showTimeline = $challenge['phase'] !== 'coming_soon';

$accentName  = $challenge['accent'] ?? 'gold';
$accentStyle = sprintf(
    '--challenge-accent: %s; --challenge-accent-dim: %s; --challenge-accent-border: %s;',
    akd_challenge_accent_hex($accentName),
    akd_challenge_accent_rgba($accentName, 0.12),
    akd_challenge_accent_rgba($accentName, 0.35)
);
?>

<main class="akd-content">
    <div class="akd-challenge-page" data-challenge-app 
        data-challenge-phase="<?= htmlspecialchars($challenge['phase']) ?>"
        style="<?= htmlspecialchars($accentStyle) ?>"
    >
        <?php require $partialsDir . '/hero.php'; ?>

        <?php if ($showTimeline): ?>
            <?php require $partialsDir . '/timeline.php'; ?>
        <?php endif; ?>

        <?php if ($showRounds): ?>
            <?php require $partialsDir . '/rounds.php'; ?>
        <?php endif; ?>

        <?php require $partialsDir . '/' . $phasePartial; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>