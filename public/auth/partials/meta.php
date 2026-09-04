<?php
require_once __DIR__ . '/../../../bootstrap.php';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$canonicalPath = rtrim($currentPath, '/') ?: '/';
$canonicalUrl = rtrim((string) ($_ENV['APP_URL'] ?? 'https://animenigeria.ng'), '/') . $canonicalPath;
$pageTitle       = $pageTitle       ?? "Anime Nigeria — Nigeria's Home for Otakus";
$pageDescription = $pageDescription ?? "Sign up or login to use our service.";
require __DIR__ . '/../../../includes/vite.php';
use App\Security\Csrf;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#000000">
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token()) ?>">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <link rel="icon" type="image/png" sizes="192x192" href="/uploads/logos/upscalemedia-transformed (1).png">
    <link rel="icon" type="image/png" sizes="32x32" href="/uploads/logos/upscalemedia-transformed (1).png">
    <link rel="apple-touch-icon" sizes="180x180" href="/uploads/logos/upscalemedia-transformed (1).png">

    <?php vite(); ?>
</head>
