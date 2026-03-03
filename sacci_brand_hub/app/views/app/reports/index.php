<h1 class="page-title">Reports</h1>
<p><a href="<?= htmlspecialchars(\Config\appUrl('/reports/new')) ?>" class="app-link">Create new report</a></p>
<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        The reports module is available in code, but the database tables are not ready yet. Run migrations `011` and `012` to enable it.
    </div>
<?php elseif (empty($reports)): ?>
    <div class="card">No reports have been recorded yet.</div>
<?php else: ?>
    <?php foreach ($reports as $report): ?>
        <div class="card">
            <h3 class="card-title">
                <a href="<?= htmlspecialchars(\Config\appUrl('/reports')) ?>?id=<?= urlencode((string) $report['id']) ?>" class="app-link">
                    <?= htmlspecialchars($report['title']) ?>
                </a>
            </h3>
            <p class="meta-text">
                <strong>Type:</strong> <?= htmlspecialchars($report['report_type']) ?>
                | <strong>Status:</strong> <?= htmlspecialchars($report['status']) ?>
                <?php if (!empty($report['department_name'])): ?>
                    | <strong>Department:</strong> <?= htmlspecialchars($report['department_name']) ?>
                <?php endif; ?>
                <?php if (!empty($report['owner_name'])): ?>
                    | <strong>Owner:</strong> <?= htmlspecialchars($report['owner_name']) ?>
                <?php endif; ?>
            </p>
            <?php if (!empty($report['reporting_period_start']) || !empty($report['reporting_period_end'])): ?>
                <p class="meta-text">
                    <strong>Period:</strong>
                    <?= htmlspecialchars($report['reporting_period_start'] ?: 'N/A') ?>
                    to
                    <?= htmlspecialchars($report['reporting_period_end'] ?: 'N/A') ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($report['summary'])): ?>
                <p><?= htmlspecialchars($report['summary']) ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
