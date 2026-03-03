<?php
// Layout template for Sacci Brand Hub
$currentPath = \Config\requestPath();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(Config\env('APP_NAME', 'Sacci Brand Hub')) ?></title>
    <meta name="description" content="House of Sacci internal brand hub for content, ticketing, and asset management.">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(\Config\appUrl('/static/brand-hub.css')) ?>">
</head>
<body>
    <div class="navbar">
        <div>
            <a href="<?= htmlspecialchars(\Config\appUrl('/app')) ?>" class="<?= (str_starts_with($currentPath, '/app') ? 'active' : '') ?>">Dashboard</a>
            <a href="<?= htmlspecialchars(\Config\appUrl('/meetings')) ?>" class="<?= (str_starts_with($currentPath, '/meetings') ? 'active' : '') ?>">Meetings</a>
            <a href="<?= htmlspecialchars(\Config\appUrl('/tickets')) ?>" class="<?= (str_starts_with($currentPath, '/tickets') ? 'active' : '') ?>">Tickets</a>
            <a href="<?= htmlspecialchars(\Config\appUrl('/assets')) ?>" class="<?= (str_starts_with($currentPath, '/assets') ? 'active' : '') ?>">Assets</a>
            <a href="<?= htmlspecialchars(\Config\appUrl('/portal')) ?>" class="<?= (str_starts_with($currentPath, '/portal') ? 'active' : '') ?>">Retail Portal</a>
        </div>
        <div>
            <?php if (Core\Auth::check()): ?>
            <a href="<?= htmlspecialchars(\Config\appUrl('/logout')) ?>" class="logout-link">Logout</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="container">
        <?= $content ?? '' ?>
    </div>
</body>
</html>
