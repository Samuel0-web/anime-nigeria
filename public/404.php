<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="192x192" href="/uploads/upscalemedia-transformed (1).png">
    <link rel="icon" type="image/png" sizes="32x32" href="/uploads/upscalemedia-transformed (1).png">
    <link rel="apple-touch-icon" sizes="180x180" href="/uploads/upscalemedia-transformed (1).png">
    <title>404 - Page Not Found</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Unified typography using only Atkinson Hyperlegible -->
    <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================================
           CORE ARCHITECTURE & CANVAS SETUP
           ========================================================================== */
        :root {
            --color-bg: #000000;
            --color-text-main: #ffffff;
            --color-text-muted: #666666;
            --color-accent-crimson: #ff2a44;
            --color-border-dark: #1a1a1a;
            
            --font-display: 'Atkinson Hyperlegible', sans-serif;
            --font-body: 'Atkinson Hyperlegible', sans-serif;
            
            --transition-premium: 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--color-bg);
            color: var(--color-text-main);
            font-family: var(--font-body);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden; 
            padding: 2rem;
            position: relative;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ==========================================================================
           AMBIENT DECORATION (SAKURA)
           ========================================================================== */
        .sakura-petal {
            position: absolute;
            background-color: #ffb7c5;
            border-radius: 15px 0 15px 0;
            opacity: 0.12;
            pointer-events: none;
            z-index: 0;
            filter: blur(1px);
        }

        .petal-1 {
            width: 12px;
            height: 12px;
            top: -5%;
            left: 25%;
            animation: drift 22s linear infinite;
        }

        .petal-2 {
            width: 9px;
            height: 9px;
            top: -10%;
            left: 65%;
            animation: drift 30s linear infinite 8s;
        }

        @keyframes drift {
            0% {
                transform: translate(0, 0) rotate(0deg);
                opacity: 0;
            }
            10% { opacity: 0.12; }
            90% { opacity: 0.12; }
            100% {
                transform: translate(150px, 110vh) rotate(360deg);
                opacity: 0;
            }
        }

        /* ==========================================================================
           MINIMAL TYPOGRAPHIC LAYOUT
           ========================================================================== */
        .error-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            opacity: 0;
            transform: translateY(6px);
            animation: premiumEntrance 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            z-index: 1;
        }

        /* Subtly textured background Japanese Kanji */
        .dramatic-code {
            font-family: var(--font-display);
            font-size: clamp(6.5rem, 16vw, 10rem);
            font-weight: 700;
            line-height: 0.9;
            color: var(--color-text-main);
            position: relative;
        }

        .dramatic-code::before {
            content: '四〇四';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: clamp(8rem, 28vw, 16rem);
            color: #ffffff;
            opacity: 0.04; /* Extremely low opacity acting as pure texture */
            z-index: -1;
            white-space: nowrap;
            pointer-events: none;
        }

        /* Elegant Japanese-inspired divider */
        .elegant-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin: 1.5rem 0;
            color: var(--color-accent-crimson);
            font-size: 0.65rem;
        }

        .elegant-divider::before,
        .elegant-divider::after {
            content: '';
            height: 1px;
            width: 24px;
            background-color: var(--color-accent-crimson);
            opacity: 0.5;
        }

        .title {
            font-family: var(--font-display);
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 700; /* Adjusted to 700 for Atkinson Hyperlegible availability */
            letter-spacing: -0.02em;
            margin-bottom: 0.75rem;
            color: var(--color-text-main);
        }

        .description {
            font-size: clamp(0.95rem, 2vw, 1.05rem);
            color: var(--color-text-muted);
            max-width: 400px;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        /* ==========================================================================
           PREMIUM INTERACTIVE NODES
           ========================================================================== */
        .action-axis {
            display: flex;
            gap: 1rem;
            align-items: center;
            justify-content: center;
        }

        .btn {
            font-family: var(--font-display);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            text-decoration: none;
            padding: 0.85rem 1.8rem;
            border-radius: 6px;
            transition: all var(--transition-premium);
            cursor: pointer;
        }

        .btn-primary {
            background-color: var(--color-accent-crimson);
            color: #ffffff;
            border: 1px solid var(--color-accent-crimson);
        }

        .btn-primary:hover {
            background-color: transparent;
            border-color: var(--color-text-main);
            color: var(--color-text-main);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: transparent;
            color: #aaaaaa;
            border: 1px solid var(--color-border-dark);
        }

        .btn-secondary:hover {
            border-color: var(--color-text-main);
            color: var(--color-text-main);
            transform: translateY(-2px);
        }

        .btn:focus-visible {
            outline: 2px solid var(--color-accent-crimson);
            outline-offset: 4px;
        }

        /* ==========================================================================
           RESPONSIVE RE-CONFIGURATIONS
           ========================================================================== */
        @media (max-width: 480px) {
            body { padding: 1.5rem; }

            .action-axis {
                flex-direction: column;
                width: 100%;
                max-width: 260px;
                gap: 0.85rem;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }

        @keyframes premiumEntrance {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

    <div class="sakura-petal petal-1" aria-hidden="true"></div>
    <div class="sakura-petal petal-2" aria-hidden="true"></div>

    <main class="error-wrapper">
        
        <h1 class="dramatic-code">404</h1>
        
        <div class="elegant-divider" aria-hidden="true">✧</div>

        <h2 class="title">Page not found.</h2>
        <p class="description">
            We couldn't find the page you were looking for. Maybe Zoro pointed you this way.
        </p>

        <div class="action-axis">
            <a href="/" class="btn btn-primary">Return Home</a>
            <a href="/our-community" class="btn btn-secondary">Explore Community</a>
        </div>

    </main>
</body>
</html>