<p style="margin-bottom:16px">
    <a href="<?= htmlspecialchars(\Config\appUrl('/campaigns')) ?>" class="app-link">&larr; Campaigns</a>
</p>

<h1 class="page-title"><?= $campaign ? 'Edit Campaign' : 'New Campaign' ?></h1>

<?php if (!empty($error)): ?>
    <div class="card error-card" style="margin-bottom:16px"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php $v = $values ?? $campaign ?? []; ?>

<div class="card" style="max-width:560px">
    <form method="post" action="<?= htmlspecialchars(\Config\appUrl($campaign ? '/campaigns/update' : '/campaigns')) ?>">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <?php if ($campaign): ?>
            <input type="hidden" name="id" value="<?= (int)$campaign['id'] ?>">
        <?php endif; ?>

        <label class="form-label" for="name">Campaign Name *</label>
        <input type="text" id="name" name="name" class="form-input"
               value="<?= htmlspecialchars($v['name'] ?? '') ?>" placeholder="e.g. Spring Launch 2026">

        <label class="form-label" style="margin-top:16px" for="status">Status</label>
        <select id="status" name="status" class="form-input">
            <?php foreach ($statuses as $s): ?>
                <option value="<?= htmlspecialchars($s) ?>" <?= (($v['status'] ?? 'draft') === $s) ? 'selected' : '' ?>>
                    <?= htmlspecialchars(ucfirst($s)) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:16px">
            <div>
                <label class="form-label" for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-input"
                       value="<?= htmlspecialchars($v['start_date'] ?? '') ?>">
            </div>
            <div>
                <label class="form-label" for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-input"
                       value="<?= htmlspecialchars($v['end_date'] ?? '') ?>">
            </div>
        </div>

        <?php if (!empty($users)): ?>
            <label class="form-label" style="margin-top:16px" for="owner_id">Owner</label>
            <select id="owner_id" name="owner_id" class="form-input">
                <option value="">— Unassigned —</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= (int)$u['id'] ?>"
                        <?= ((int)($v['owner_id'] ?? 0) === (int)$u['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <label class="form-label" style="margin-top:16px" for="description">Description</label>
        <textarea id="description" name="description" class="form-input form-textarea"
                  placeholder="Goals, target audience, key deliverables…"><?= htmlspecialchars($v['description'] ?? '') ?></textarea>

        <button type="submit" class="button-primary" style="margin-top:20px">
            <?= $campaign ? 'Save Changes' : 'Create Campaign' ?>
        </button>
    </form>
</div>
