<h1 class="page-title"><?= !empty($isEdit) ? 'Edit Report' : 'New Report' ?></h1>
<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        The report form is available in code, but the database tables are not ready yet. Run migrations `011` and `012` to enable creation.
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="card error-card">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars(\Config\appUrl(!empty($isEdit) ? '/reports/update' : '/reports')) ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <?php if (!empty($isEdit)): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($values['id'] ?? '') ?>">
    <?php endif; ?>
    <div class="card">
        <label for="title" class="form-label">Title</label>
        <input type="text" id="title" name="title" required class="form-input" value="<?= htmlspecialchars($values['title'] ?? '') ?>">

        <label for="report_type" class="form-label">Report Type</label>
        <input type="text" id="report_type" name="report_type" class="form-input" value="<?= htmlspecialchars($values['report_type'] ?? 'general') ?>">

        <label for="department_id" class="form-label">Department</label>
        <select id="department_id" name="department_id" class="form-input">
            <option value="">None</option>
            <?php foreach ($departments as $department): ?>
                <option value="<?= htmlspecialchars((string) $department['id']) ?>" <?= (($values['department_id'] ?? '') === (string) $department['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($department['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="reporting_period_start" class="form-label">Period Start</label>
        <input type="date" id="reporting_period_start" name="reporting_period_start" class="form-input" value="<?= htmlspecialchars($values['reporting_period_start'] ?? '') ?>">

        <label for="reporting_period_end" class="form-label">Period End</label>
        <input type="date" id="reporting_period_end" name="reporting_period_end" class="form-input" value="<?= htmlspecialchars($values['reporting_period_end'] ?? '') ?>">

        <label for="status" class="form-label">Status</label>
        <select id="status" name="status" class="form-input">
            <?php foreach (['draft', 'published', 'archived'] as $status): ?>
                <option value="<?= htmlspecialchars($status) ?>" <?= (($values['status'] ?? 'draft') === $status) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($status) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="source_url" class="form-label">Source URL</label>
        <input type="url" id="source_url" name="source_url" class="form-input" value="<?= htmlspecialchars($values['source_url'] ?? '') ?>">

        <label for="summary" class="form-label">Summary</label>
        <textarea id="summary" name="summary" class="form-input form-textarea"><?= htmlspecialchars($values['summary'] ?? '') ?></textarea>

        <button type="submit" class="button-primary"><?= !empty($isEdit) ? 'Update Report' : 'Create Report' ?></button>
    </div>
</form>
