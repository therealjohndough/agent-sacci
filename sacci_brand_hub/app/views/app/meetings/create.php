<h1 class="page-title"><?= !empty($isEdit) ? 'Edit Meeting' : 'New Meeting' ?></h1>
<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        The meeting form is available in code, but the database tables are not ready yet. Run migrations `003` through `005` to enable creation.
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="card error-card">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars(\Config\appUrl(!empty($isEdit) ? '/meetings/update' : '/meetings')) ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <?php if (!empty($isEdit)): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($values['id'] ?? '') ?>">
    <?php endif; ?>
    <div class="card">
        <label for="title" class="form-label">Title</label>
        <input type="text" id="title" name="title" required class="form-input" value="<?= htmlspecialchars($values['title'] ?? '') ?>">

        <label for="meeting_type" class="form-label">Meeting Type</label>
        <input type="text" id="meeting_type" name="meeting_type" class="form-input" value="<?= htmlspecialchars($values['meeting_type'] ?? 'general') ?>">

        <label for="department_id" class="form-label">Department</label>
        <select id="department_id" name="department_id" class="form-input">
            <option value="">None</option>
            <?php foreach ($departments as $department): ?>
                <option value="<?= htmlspecialchars((string) $department['id']) ?>" <?= (($values['department_id'] ?? '') === (string) $department['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($department['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="scheduled_for" class="form-label">Scheduled For</label>
        <input type="datetime-local" id="scheduled_for" name="scheduled_for" class="form-input" value="<?= htmlspecialchars($values['scheduled_for'] ?? '') ?>">

        <label for="occurred_at" class="form-label">Occurred At</label>
        <input type="datetime-local" id="occurred_at" name="occurred_at" class="form-input" value="<?= htmlspecialchars($values['occurred_at'] ?? '') ?>">

        <label for="status" class="form-label">Status</label>
        <select id="status" name="status" class="form-input">
            <?php foreach (['draft', 'published', 'archived'] as $status): ?>
                <option value="<?= htmlspecialchars($status) ?>" <?= (($values['status'] ?? 'draft') === $status) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($status) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="summary" class="form-label">Summary</label>
        <textarea id="summary" name="summary" class="form-input form-textarea"><?= htmlspecialchars($values['summary'] ?? '') ?></textarea>

        <label for="notes" class="form-label">Notes</label>
        <textarea id="notes" name="notes" required class="form-input form-textarea"><?= htmlspecialchars($values['notes'] ?? '') ?></textarea>

        <label for="source_url" class="form-label">Source URL</label>
        <input type="url" id="source_url" name="source_url" class="form-input" value="<?= htmlspecialchars($values['source_url'] ?? '') ?>">

        <button type="submit" class="button-primary"><?= !empty($isEdit) ? 'Update Meeting' : 'Create Meeting' ?></button>
    </div>
</form>
