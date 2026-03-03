<?php
// Layout template for Brand Hub
$currentPath = \Config\requestPath();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(Config\env('APP_NAME', 'Brand Hub')) ?></title>
    <meta name="description" content="House of Sacci internal brand hub for content, ticketing, and asset management.">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(\Config\appUrl('/static/brand-hub.css')) ?>">
</head>
<body>
    <div class="navbar">
        <div>
            <a href="<?= htmlspecialchars(\Config\appUrl('/app')) ?>" class="<?= (str_starts_with($currentPath, '/app') ? 'active' : '') ?>">Dashboard</a>
            <a href="<?= htmlspecialchars(\Config\appUrl('/dashboard/executive')) ?>" class="<?= (str_starts_with($currentPath, '/dashboard/executive') ? 'active' : '') ?>">Executive</a>
            <a href="<?= htmlspecialchars(\Config\appUrl('/search')) ?>" class="<?= (str_starts_with($currentPath, '/search') ? 'active' : '') ?>">Search</a>
            <a href="<?= htmlspecialchars(\Config\appUrl('/people')) ?>" class="<?= (str_starts_with($currentPath, '/people') ? 'active' : '') ?>">People</a>
            <a href="<?= htmlspecialchars(\Config\appUrl('/departments')) ?>" class="<?= (str_starts_with($currentPath, '/departments') ? 'active' : '') ?>">Departments</a>
            <a href="<?= htmlspecialchars(\Config\appUrl('/meetings')) ?>" class="<?= (str_starts_with($currentPath, '/meetings') ? 'active' : '') ?>">Meetings</a>
            <a href="<?= htmlspecialchars(\Config\appUrl('/actions')) ?>" class="<?= (str_starts_with($currentPath, '/actions') ? 'active' : '') ?>">Actions</a>
            <a href="<?= htmlspecialchars(\Config\appUrl('/reports')) ?>" class="<?= (str_starts_with($currentPath, '/reports') ? 'active' : '') ?>">Reports</a>
            <a href="<?= htmlspecialchars(\Config\appUrl('/documents')) ?>" class="<?= (str_starts_with($currentPath, '/documents') ? 'active' : '') ?>">Documents</a>
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
