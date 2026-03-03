<h1 class="page-title"><?= !empty($isEdit) ? 'Edit Batch' : 'New Batch' ?></h1>
<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        The batch form is available in code, but the database tables are not ready yet. Run migration `014` to enable creation.
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="card error-card">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars(\Config\appUrl(!empty($isEdit) ? '/batches/update' : '/batches')) ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <?php if (!empty($isEdit)): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($values['id'] ?? '') ?>">
    <?php endif; ?>
    <div class="card">
        <label for="strain_id" class="form-label">Strain</label>
        <select id="strain_id" name="strain_id" required class="form-input">
            <option value="">Select a strain</option>
            <?php foreach ($strains as $strain): ?>
                <option value="<?= htmlspecialchars((string) $strain['id']) ?>" <?= (($values['strain_id'] ?? '') === (string) $strain['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($strain['name']) ?> (<?= htmlspecialchars($strain['status']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="batch_code" class="form-label">Batch Code</label>
        <input type="text" id="batch_code" name="batch_code" required class="form-input" value="<?= htmlspecialchars($values['batch_code'] ?? '') ?>">

        <label for="harvest_date" class="form-label">Harvest Date</label>
        <input type="date" id="harvest_date" name="harvest_date" class="form-input" value="<?= htmlspecialchars($values['harvest_date'] ?? '') ?>">

        <label for="production_status" class="form-label">Production Status</label>
        <select id="production_status" name="production_status" class="form-input">
            <?php foreach (['planned', 'active', 'testing', 'approved', 'archived'] as $status): ?>
                <option value="<?= htmlspecialchars($status) ?>" <?= (($values['production_status'] ?? 'planned') === $status) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($status) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="thc_percent" class="form-label">THC %</label>
        <input type="number" id="thc_percent" name="thc_percent" step="0.01" min="0" class="form-input" value="<?= htmlspecialchars($values['thc_percent'] ?? '') ?>">

        <label for="cbd_percent" class="form-label">CBD %</label>
        <input type="number" id="cbd_percent" name="cbd_percent" step="0.01" min="0" class="form-input" value="<?= htmlspecialchars($values['cbd_percent'] ?? '') ?>">

        <label for="notes" class="form-label">Notes</label>
        <textarea id="notes" name="notes" class="form-input form-textarea"><?= htmlspecialchars($values['notes'] ?? '') ?></textarea>

        <button type="submit" class="button-primary"><?= !empty($isEdit) ? 'Update Batch' : 'Create Batch' ?></button>
    </div>
</form>
