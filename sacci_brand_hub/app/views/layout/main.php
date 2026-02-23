<?php
// Layout template for Sacci Brand Hub
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(Config\env('APP_NAME', 'Sacci Brand Hub')) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #31935f;
            --color-accent: #d4a837;
            --color-bg: #0d0d0d;
            --color-surface: #f5f0e8;
            --color-card: #1a1a1a;
            --color-muted: #888880;
        }
        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            background: var(--color-bg);
            color: var(--color-surface);
        }
        .navbar {
            background: #111;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--color-accent);
        }
        .navbar a {
            color: var(--color-muted);
            margin-right: 15px;
            text-decoration: none;
            font-size: 14px;
        }
        .navbar a.active {
            color: var(--color-accent);
        }
        .container {
            padding: 20px;
            max-width: 1100px;
            margin: 0 auto;
        }
        .card {
            background: var(--color-card);
            padding: 20px;
            border: 1px solid rgba(212,168,55,0.15);
            margin-bottom: 20px;
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div>
            <a href="/app" class="<?= (str_starts_with($_SERVER['REQUEST_URI'], '/app') ? 'active' : '') ?>">Dashboard</a>
            <a href="/tickets" class="<?= (str_starts_with($_SERVER['REQUEST_URI'], '/tickets') ? 'active' : '') ?>">Tickets</a>
            <a href="/assets" class="<?= (str_starts_with($_SERVER['REQUEST_URI'], '/assets') ? 'active' : '') ?>">Assets</a>
            <a href="/portal" class="<?= (str_starts_with($_SERVER['REQUEST_URI'], '/portal') ? 'active' : '') ?>">Retail Portal</a>
        </div>
        <div>
            <?php if (Core\Auth::check()): ?>
            <a href="/logout" style="color: var(--color-accent);">Logout</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="container">
        <?= $content ?? '' ?>
    </div>
</body>
</html>