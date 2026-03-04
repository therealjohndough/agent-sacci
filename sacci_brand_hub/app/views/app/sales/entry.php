<p style="margin-bottom:16px">
    <a href="<?= htmlspecialchars(\Config\appUrl('/sales')) ?>" class="app-link">&larr; Sales</a>
</p>

<h1 class="page-title">Log Sales Entry</h1>

<?php if (!empty($error)): ?>
    <div class="card error-card" style="margin-bottom:16px"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width:520px">
    <form method="post" action="<?= htmlspecialchars(\Config\appUrl('/sales')) ?>">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

        <label class="form-label" for="product_id">Product *</label>
        <?php if (empty($products)): ?>
            <p class="meta-text">No active products found. Import products first.</p>
        <?php else: ?>
            <select id="product_id" name="product_id" class="form-input">
                <option value="">— Select product —</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= (int)$p['id'] ?>"
                        <?= ((int)($_POST['product_id'] ?? 0) === (int)$p['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['sku'] . ' — ' . $p['product_name']) ?>
                        <?php if (!empty($p['format'])): ?>(<?= htmlspecialchars($p['format']) ?>)<?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <label class="form-label" style="margin-top:16px" for="reporting_date">Reporting Date *</label>
        <input type="date" id="reporting_date" name="reporting_date" class="form-input"
               value="<?= htmlspecialchars($_POST['reporting_date'] ?? date('Y-m-d')) ?>">

        <label class="form-label" style="margin-top:16px" for="units_sold">Units Sold *</label>
        <input type="number" id="units_sold" name="units_sold" class="form-input" min="0"
               value="<?= htmlspecialchars($_POST['units_sold'] ?? '0') ?>">

        <label class="form-label" style="margin-top:16px" for="revenue">Revenue (optional)</label>
        <input type="text" id="revenue" name="revenue" class="form-input"
               placeholder="e.g. 1250.00"
               value="<?= htmlspecialchars($_POST['revenue'] ?? '') ?>">

        <label class="form-label" style="margin-top:16px" for="channel">Channel (optional)</label>
        <input type="text" id="channel" name="channel" class="form-input"
               placeholder="e.g. LeafLink, Direct, Sitru"
               value="<?= htmlspecialchars($_POST['channel'] ?? '') ?>">

        <label class="form-label" style="margin-top:16px" for="notes">Notes (optional)</label>
        <textarea id="notes" name="notes" class="form-input form-textarea"
                  placeholder="Any context for this entry…"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>

        <button type="submit" class="button-primary" style="margin-top:20px">Save Entry</button>
    </form>
</div>
