<h1 class="page-title"><?= !empty($isEdit) ? 'Edit Action Item' : 'New Action Item' ?></h1>
<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        The action item form is available in code, but the database tables are not ready yet. Run migrations `007` and `008` to enable creation.
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="card error-card">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars(\Config\appUrl(!empty($isEdit) ? '/actions/update' : '/actions')) ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <?php if (!empty($isEdit)): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($values['id'] ?? '') ?>">
    <?php endif; ?>
    <div class="card">
        <label for="title" class="form-label">Title</label>
        <input type="text" id="title" name="title" required class="form-input" value="<?= htmlspecialchars($values['title'] ?? '') ?>">

        <label for="details" class="form-label">Details</label>
        <textarea id="details" name="details" class="form-input form-textarea"><?= htmlspecialchars($values['details'] ?? '') ?></textarea>

        <label for="status" class="form-label">Status</label>
        <select id="status" name="status" class="form-input">
            <?php foreach (['open', 'in_progress', 'blocked', 'done', 'archived'] as $status): ?>
                <option value="<?= htmlspecialchars($status) ?>" <?= (($values['status'] ?? 'open') === $status) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($status) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="priority" class="form-label">Priority</label>
        <select id="priority" name="priority" class="form-input">
            <?php foreach (['low', 'medium', 'high', 'urgent'] as $priority): ?>
                <option value="<?= htmlspecialchars($priority) ?>" <?= (($values['priority'] ?? 'medium') === $priority) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($priority) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="department_id" class="form-label">Department</label>
        <select id="department_id" name="department_id" class="form-input">
            <option value="">None</option>
            <?php foreach ($departments as $department): ?>
                <option value="<?= htmlspecialchars((string) $department['id']) ?>" <?= (($values['department_id'] ?? '') === (string) $department['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($department['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="meeting_id" class="form-label">Related Meeting</label>
        <select id="meeting_id" name="meeting_id" class="form-input">
            <option value="">None</option>
            <?php foreach ($meetings as $meeting): ?>
                <option value="<?= htmlspecialchars((string) $meeting['id']) ?>" <?= (($values['meeting_id'] ?? '') === (string) $meeting['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($meeting['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="due_date" class="form-label">Due Date</label>
        <input type="date" id="due_date" name="due_date" class="form-input" value="<?= htmlspecialchars($values['due_date'] ?? '') ?>">

        <button type="submit" class="button-primary"><?= !empty($isEdit) ? 'Update Action Item' : 'Create Action Item' ?></button>
    </div>
</form>
