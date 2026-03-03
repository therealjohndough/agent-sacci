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
                <a href="<?= htmlspecialchars(\Config\appUrl('/reports/entries/edit')) ?>?report_id=<?= urlencode((string) $report['id']) ?>&entry_id=<?= urlencode((string) $entry['id']) ?>" class="app-link">Edit entry</a>
            </p>
            <form method="post" action="<?= htmlspecialchars(\Config\appUrl('/reports/entries/delete')) ?>" class="inline-form">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
                <input type="hidden" name="report_id" value="<?= htmlspecialchars((string) $report['id']) ?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string) $entry['id']) ?>">
                <button type="submit" class="button-link">Delete entry</button>
            </form>
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

<h2 class="section-title"><?= !empty($isEntryEdit) ? 'Edit Entry' : 'Add Entry' ?></h2>
<?php if (!empty($entryError)): ?>
    <div class="card error-card">
        <?= htmlspecialchars($entryError) ?>
    </div>
<?php endif; ?>
<form method="post" action="<?= htmlspecialchars(\Config\appUrl(!empty($isEntryEdit) ? '/reports/entries/update' : '/reports/entries')) ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
    <input type="hidden" name="report_id" value="<?= htmlspecialchars($entryValues['report_id'] ?? (string) $report['id']) ?>">
    <?php if (!empty($isEntryEdit)): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($entryValues['id'] ?? '') ?>">
    <?php endif; ?>
    <div class="card">
        <label for="metric_key" class="form-label">Metric Key</label>
        <input type="text" id="metric_key" name="metric_key" required class="form-input" value="<?= htmlspecialchars($entryValues['metric_key'] ?? '') ?>">

        <label for="metric_label" class="form-label">Metric Label</label>
        <input type="text" id="metric_label" name="metric_label" required class="form-input" value="<?= htmlspecialchars($entryValues['metric_label'] ?? '') ?>">

        <label for="metric_value" class="form-label">Metric Value</label>
        <input type="text" id="metric_value" name="metric_value" class="form-input" value="<?= htmlspecialchars($entryValues['metric_value'] ?? '') ?>">

        <label for="metric_unit" class="form-label">Metric Unit</label>
        <input type="text" id="metric_unit" name="metric_unit" class="form-input" value="<?= htmlspecialchars($entryValues['metric_unit'] ?? '') ?>">

        <label for="sort_order" class="form-label">Sort Order</label>
        <input type="number" id="sort_order" name="sort_order" class="form-input" value="<?= htmlspecialchars($entryValues['sort_order'] ?? '') ?>">

        <label for="entry_notes" class="form-label">Notes</label>
        <textarea id="entry_notes" name="notes" class="form-input form-textarea"><?= htmlspecialchars($entryValues['notes'] ?? '') ?></textarea>

        <button type="submit" class="button-primary"><?= !empty($isEntryEdit) ? 'Update Entry' : 'Add Entry' ?></button>
    </div>
</form>
