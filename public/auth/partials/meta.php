<?php
$pageTitle       = $pageTitle       ?? "Anime Nigeria — Nigeria's Home for Otakus";
$pageDescription = $pageDescription ?? "Sign up or login to use our service.";

require __DIR__ . '/../../../includes/vite.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#000000">
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <link rel="icon" type="image/png" sizes="192x192" href="/uploads/upscalemedia-transformed (1).png">
    <link rel="icon" type="image/png" sizes="32x32" href="/uploads/upscalemedia-transformed (1).png">
    <link rel="apple-touch-icon" sizes="180x180" href="/uploads/upscalemedia-transformed (1).png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <?php vite(); ?>
</head>
<body>