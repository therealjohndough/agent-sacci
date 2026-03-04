<p style="margin-bottom:16px">
    <a href="<?= htmlspecialchars(\Config\appUrl('/marketing')) ?>" class="app-link">&larr; Marketing Requests</a>
</p>

<h1 class="page-title">New Marketing Request</h1>

<?php if (!empty($error)): ?>
    <div class="card error-card" style="margin-bottom:16px"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width:600px">
    <form method="post" action="<?= htmlspecialchars(\Config\appUrl('/marketing/request')) ?>">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

        <label class="form-label" for="request_type">Request Type *</label>
        <select id="request_type" name="request_type" class="form-input">
            <?php foreach ($requestTypes as $val => $label): ?>
                <option value="<?= htmlspecialchars($val) ?>" <?= (($_POST['request_type'] ?? '') === $val) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label class="form-label" style="margin-top:16px" for="title">Brief Title</label>
        <input type="text" id="title" name="title" class="form-input"
               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
               placeholder="Short summary (auto-filled from type if left blank)">

        <label class="form-label" style="margin-top:16px" for="description">Description / Brief</label>
        <textarea id="description" name="description" class="form-input form-textarea"
                  placeholder="What do you need? Include any relevant details, references, copy, or links…"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

        <label class="form-label" style="margin-top:16px" for="priority">Priority</label>
        <select id="priority" name="priority" class="form-input">
            <?php foreach ($priorities as $p): ?>
                <option value="<?= htmlspecialchars($p) ?>" <?= (($_POST['priority'] ?? 'normal') === $p) ? 'selected' : '' ?>>
                    <?= htmlspecialchars(ucfirst($p)) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label class="form-label" style="margin-top:16px" for="due_date">Due Date</label>
        <input type="date" id="due_date" name="due_date" class="form-input"
               value="<?= htmlspecialchars($_POST['due_date'] ?? '') ?>">

        <?php if (!empty($strains)): ?>
            <label class="form-label" style="margin-top:16px" for="linked_strain_id">Linked Strain (optional)</label>
            <select id="linked_strain_id" name="linked_strain_id" class="form-input">
                <option value="">None</option>
                <?php foreach ($strains as $s): ?>
                    <option value="<?= (int) $s['id'] ?>" <?= ((int)($_POST['linked_strain_id'] ?? 0) === (int)$s['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if (!empty($products)): ?>
            <label class="form-label" style="margin-top:16px" for="linked_product_id">Linked Product (optional)</label>
            <select id="linked_product_id" name="linked_product_id" class="form-input">
                <option value="">None</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= ((int)($_POST['linked_product_id'] ?? 0) === (int)$p['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['product_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if (!empty($users)): ?>
            <label class="form-label" style="margin-top:16px" for="assigned_to">Assign To (optional)</label>
            <select id="assigned_to" name="assigned_to" class="form-input">
                <option value="">Unassigned</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= (int) $u['id'] ?>" <?= ((int)($_POST['assigned_to'] ?? 0) === (int)$u['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <button type="submit" class="button-primary">Submit Request</button>
    </form>
</div>
