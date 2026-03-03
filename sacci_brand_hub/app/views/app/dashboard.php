<h1 class="page-title">Dashboard</h1>
<p>Welcome, <?= htmlspecialchars($user['name'] ?? $user['email']) ?>!</p>

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
