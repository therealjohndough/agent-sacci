<h1 class="page-title"><?= !empty($isEdit) ? 'Edit Document' : 'New Document' ?></h1>
<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        The document form is available in code, but the database tables are not ready yet. Run migrations `009` and `010` to enable creation.
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="card error-card">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars(\Config\appUrl(!empty($isEdit) ? '/documents/update' : '/documents')) ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <?php if (!empty($isEdit)): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($values['id'] ?? '') ?>">
    <?php endif; ?>
    <div class="card">
        <label for="title" class="form-label">Title</label>
        <input type="text" id="title" name="title" required class="form-input" value="<?= htmlspecialchars($values['title'] ?? '') ?>">

        <label for="document_type" class="form-label">Document Type</label>
        <input type="text" id="document_type" name="document_type" class="form-input" value="<?= htmlspecialchars($values['document_type'] ?? 'reference') ?>">

        <label for="department_id" class="form-label">Department</label>
        <select id="department_id" name="department_id" class="form-input">
            <option value="">None</option>
            <?php foreach ($departments as $department): ?>
                <option value="<?= htmlspecialchars((string) $department['id']) ?>" <?= (($values['department_id'] ?? '') === (string) $department['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($department['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="status" class="form-label">Status</label>
        <select id="status" name="status" class="form-input">
            <?php foreach (['draft', 'active', 'archived'] as $status): ?>
                <option value="<?= htmlspecialchars($status) ?>" <?= (($values['status'] ?? 'draft') === $status) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($status) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="version_label" class="form-label">Version Label</label>
        <input type="text" id="version_label" name="version_label" class="form-input" value="<?= htmlspecialchars($values['version_label'] ?? '') ?>">

        <label for="source_url" class="form-label">Source URL</label>
        <input type="url" id="source_url" name="source_url" class="form-input" value="<?= htmlspecialchars($values['source_url'] ?? '') ?>">

        <label for="content" class="form-label">Content</label>
        <textarea id="content" name="content" required class="form-input form-textarea"><?= htmlspecialchars($values['content'] ?? '') ?></textarea>

        <button type="submit" class="button-primary"><?= !empty($isEdit) ? 'Update Document' : 'Create Document' ?></button>
    </div>
</form>
