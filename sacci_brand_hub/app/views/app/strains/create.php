<h1 class="page-title"><?= !empty($isEdit) ? 'Edit Strain' : 'New Strain' ?></h1>
<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        The strain form is available in code, but the database tables are not ready yet. Run migration `014` to enable creation.
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="card error-card">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars(\Config\appUrl(!empty($isEdit) ? '/strains/update' : '/strains')) ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <?php if (!empty($isEdit)): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($values['id'] ?? '') ?>">
    <?php endif; ?>
    <div class="card">
        <label for="name" class="form-label">Name</label>
        <input type="text" id="name" name="name" required class="form-input" value="<?= htmlspecialchars($values['name'] ?? '') ?>">

        <label for="lineage" class="form-label">Lineage</label>
        <input type="text" id="lineage" name="lineage" class="form-input" value="<?= htmlspecialchars($values['lineage'] ?? '') ?>">

        <label for="category" class="form-label">Category</label>
        <input type="text" id="category" name="category" class="form-input" value="<?= htmlspecialchars($values['category'] ?? '') ?>">

        <label for="breeder" class="form-label">Breeder</label>
        <input type="text" id="breeder" name="breeder" class="form-input" value="<?= htmlspecialchars($values['breeder'] ?? '') ?>">

        <label for="status" class="form-label">Status</label>
        <select id="status" name="status" class="form-input">
            <?php foreach (['active', 'hold', 'archived'] as $status): ?>
                <option value="<?= htmlspecialchars($status) ?>" <?= (($values['status'] ?? 'active') === $status) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($status) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="description" class="form-label">Notes</label>
        <textarea id="description" name="description" class="form-input form-textarea"><?= htmlspecialchars($values['description'] ?? '') ?></textarea>

        <button type="submit" class="button-primary"><?= !empty($isEdit) ? 'Update Strain' : 'Create Strain' ?></button>
    </div>
</form>
