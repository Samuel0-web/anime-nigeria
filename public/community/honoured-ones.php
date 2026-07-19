<?php
declare(strict_types=1);
$pageTitle = "Honoured Ones | Anime Nigeria";
$pageDescription = "Meet the fans, creators, and community members recognized for their outstanding contributions to Anime Nigeria.";
require_once __DIR__ . '/../../includes/header.php';

$honoredOnes = [

    2025 => [

        [
            'category' => 'Most Active Male',
            'winner' => 'Tony'
        ],

        [
            'category' => 'Community MVP',
            'winner' => 'Tony'
        ],

        [
            'category' => 'Most Active Female',
            'winner' => 'Jumoke'
        ],

        [
            'category' => 'Best Newcomer',
            'winner' => 'John David'
        ],

        [
            'category' => 'Top Contributor',
            'winner' => 'SmiffCity'
        ],

        [
            'category' => 'Funniest',
            'winner' => 'Jedd'
        ],

        [
            'category' => 'Most Mature',
            'winner' => 'SmiffCity'
        ],

        [
            'category' => 'Most Knowledgeable',
            'winner' => 'SmiffCity'
        ],

        [
            'category' => 'Trivia Champion',
            'winner' => 'Tony'
        ],

        [
            'category' => 'Best Event Participant',
            'winner' => 'Tony'
        ],

        [
            'category' => 'Most Friendly Admin',
            'winner' => 'Jumoke'
        ],

        [
            'category' => 'Most Active Admin',
            'winner' => 'Jumoke'
        ]

    ],

    2024 => [

        [
            'category' => 'Most Active Male',
            'winner' => 'Tony'
        ],

        [
            'category' => 'Community MVP',
            'winner' => 'Tony'
        ],

        [
            'category' => 'Most Active Female',
            'winner' => 'Jumoke'
        ],

        [
            'category' => 'Best Newcomer',
            'winner' => 'John David'
        ],

        [
            'category' => 'Top Contributor',
            'winner' => 'SmiffCity'
        ],

        [
            'category' => 'Funniest',
            'winner' => 'Jedd'
        ],

        [
            'category' => 'Most Mature',
            'winner' => 'SmiffCity'
        ],

        [
            'category' => 'Most Knowledgeable',
            'winner' => 'SmiffCity'
        ],

        [
            'category' => 'Trivia Champion',
            'winner' => 'Tony'
        ],

        [
            'category' => 'Best Event Participant',
            'winner' => 'Tony'
        ],

        [
            'category' => 'Most Friendly Admin',
            'winner' => 'Jumoke'
        ],

        [
            'category' => 'Most Active Admin',
            'winner' => 'Jumoke'
        ]

    ]

];

$years = array_keys($honoredOnes);
rsort($years);
$defaultYear = $years[0];
?>

<main id="main-content">
    <section class="an-honored-hero">
        <div class="an-container">
            <span class="an-eyebrow"><i class="fa-solid fa-trophy"></i>Community Recognition</span>
            <h1 class="an-honored-hero__heading">The Honored Ones</h1>

            <p class="an-honored-hero__description">
                Celebrating the members whose passion, kindness and dedication helped shape the Anime Nigeria community.
            </p>
        </div>
    </section>

    <section class="an-honored">
        <div class="an-container">
            <div class="an-honored__years">
                <?php foreach ($years as $index => $year): ?>
                    <button class="an-honored__year <?= $index === 0 ? 'is-active' : '' ?>"
                        data-year="<?= $year ?>">
                        <?= $year ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="an-honored__stats">
                <div>
                    <span class="an-honored__stat-value" id="awardCount">
                        <?= count($honoredOnes[$defaultYear]) ?>
                    </span>

                    <span class="an-honored__stat-label">Awards</span>
                </div>

                <?php
                    $winnerCounts = array_count_values(array_column($honoredOnes[$defaultYear], 'winner'));
                    arsort($winnerCounts);
                    $topHonoree = array_key_first($winnerCounts);
                    $topHonoreeAwards = reset($winnerCounts);
                ?>

                <div>
                    <span class="an-honored__stat-value" id="topHonoree">
                        <?= htmlspecialchars($topHonoree) ?>
                    </span>

                    <span class="an-honored__stat-label">
                        Top Honoree
                    </span>

                    <span class="an-honored__stat-meta" id="topHonoreeAwards">
                        <?= $topHonoreeAwards ?> Awards
                    </span>
                </div>
            </div>

            <div class="an-honored__grid" id="honoredGrid"></div>
        </div>
    </section>
</main>

<script>const honoredOnes = <?= json_encode($honoredOnes, JSON_PRETTY_PRINT); ?>;</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>