<h1 class="page-title"><?= htmlspecialchars($report['title']) ?></h1>
<p><a href="<?= htmlspecialchars(\Config\appUrl('/reports/edit')) ?>?id=<?= urlencode((string) $report['id']) ?>" class="app-link">Edit report</a></p>
<div class="card">
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
        <p><strong>Summary:</strong> <?= nl2br(htmlspecialchars($report['summary'])) ?></p>
    <?php endif; ?>

    <?php if (!empty($report['source_url'])): ?>
        <p><strong>Source:</strong> <a href="<?= htmlspecialchars($report['source_url']) ?>" class="app-link"><?= htmlspecialchars($report['source_url']) ?></a></p>
    <?php endif; ?>
</div>

<h2 class="section-title">Entries</h2>
<?php if (empty($entries)): ?>
    <div class="card">No report entries have been recorded for this report.</div>
<?php else: ?>
    <?php foreach ($entries as $entry): ?>
        <div class="card">
            <h3 class="card-title"><?= htmlspecialchars($entry['metric_label']) ?></h3>
            <p>
                <strong>Value:</strong>
                <?= htmlspecialchars($entry['metric_value'] ?? 'N/A') ?>
                <?php if (!empty($entry['metric_unit'])): ?>
                    <?= htmlspecialchars($entry['metric_unit']) ?>
                <?php endif; ?>
            </p>
            <?php if (!empty($entry['notes'])): ?>
                <p><?= nl2br(htmlspecialchars($entry['notes'])) ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
