<h1 class="page-title">Dashboard</h1>
<p>Welcome, <?= htmlspecialchars($user['name'] ?? $user['email']) ?>!</p>

<?php if (!empty($storageWarning)): ?>
    <div class="card" style="border-color:#c0392b;background:#2d0a0a;color:#e74c3c;margin-bottom:1rem;">
        <strong>Security Warning:</strong> The <code>storage/</code> directory appears to be
        inside the web document root and may be directly accessible via HTTP. Move it above
        the document root or ensure the <code>storage/.htaccess</code> denying all access is
        in place and that <code>AllowOverride All</code> is configured in Apache.
    </div>
<?php endif; ?>

<?php if (!empty($metrics)): ?>
    <div class="metric-grid">
        <?php foreach ($metrics as $metric): ?>
            <div class="card metric-card">
                <p class="metric-label"><?= htmlspecialchars($metric['label']) ?></p>
                <p class="metric-value"><?= htmlspecialchars((string) $metric['value']) ?></p>
                <p><a href="<?= htmlspecialchars(\Config\appUrl($metric['link'])) ?>" class="app-link">Open</a></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h2 class="section-title">My Tickets</h2>
<?php if (empty($tickets)): ?>
    <div class="card">No tickets assigned to you.</div>
<?php else: ?>
    <?php foreach ($tickets as $t): ?>
        <div class="card">
            <h3 class="card-title">
                <a href="<?= htmlspecialchars(\Config\appUrl('/tickets')) ?>?id=<?= urlencode((string) $t['id']) ?>" class="app-link">#<?= $t['id'] ?> - <?= htmlspecialchars($t['title']) ?></a>
            </h3>
            <p><strong>Status:</strong> <?= htmlspecialchars($t['status']) ?> | <strong>Due:</strong> <?= htmlspecialchars($t['due_date']) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
