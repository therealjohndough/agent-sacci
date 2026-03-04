<h1 class="page-title">Products</h1>

<!-- Filter bar -->
<form method="get" action="<?= htmlspecialchars(\Config\appUrl('/products')) ?>" style="margin-bottom:24px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
    <div>
        <label class="form-label" style="margin-bottom:4px;font-size:13px">Strain</label>
        <select name="strain_id" class="form-input" style="width:auto;padding:8px 12px">
            <option value="">All Strains</option>
            <?php foreach ($strains as $s): ?>
                <option value="<?= (int) $s['id'] ?>" <?= ((int)($filterStrainId ?? 0) === (int)$s['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="form-label" style="margin-bottom:4px;font-size:13px">Type</label>
        <select name="type" class="form-input" style="width:auto;padding:8px 12px">
            <option value="">All Types</option>
            <?php foreach (['flower', 'pre-roll', 'concentrate', 'vape'] as $t): ?>
                <option value="<?= $t ?>" <?= ($filterType === $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="button-primary" style="margin-top:0">Filter</button>
    <?php if ($filterStrainId || $filterType): ?>
        <a href="<?= htmlspecialchars(\Config\appUrl('/products')) ?>" class="app-link" style="line-height:2.4">Clear</a>
    <?php endif; ?>
</form>

<?php if (empty($products)): ?>
    <div class="card">No products found. <a href="<?= htmlspecialchars(\Config\appUrl('/products/import')) ?>" class="app-link">Import CSV</a> to get started.</div>
<?php else: ?>
    <p class="meta-text" style="margin-bottom:16px"><?= count($products) ?> product<?= count($products) !== 1 ? 's' : '' ?></p>
    <div class="section-grid">
        <?php foreach ($products as $p): ?>
            <div class="card section-card">
                <h3 class="card-title" style="margin-bottom:4px">
                    <a href="<?= htmlspecialchars(\Config\appUrl('/products')) ?>?id=<?= urlencode((string) $p['id']) ?>" class="app-link">
                        <?= htmlspecialchars($p['product_name']) ?>
                    </a>
                </h3>
                <p class="meta-text" style="margin:0 0 6px">
                    <a href="<?= htmlspecialchars(\Config\appUrl('/strains')) ?>?id=<?= urlencode((string) $p['strain_id']) ?>" class="app-link" style="color:var(--color-muted)">
                        <?= htmlspecialchars($p['strain_name']) ?>
                    </a>
                    &middot; <span style="text-transform:uppercase;font-size:12px"><?= htmlspecialchars($p['format']) ?></span>
                    <?php if (!empty($p['weight_label'])): ?>
                        &middot; <?= htmlspecialchars($p['weight_label']) ?>
                    <?php endif; ?>
                </p>
                <?php if (!empty($p['mood_tag'])): ?>
                    <span class="badge badge-mood"><?= htmlspecialchars($p['mood_tag']) ?></span>
                <?php endif; ?>
                <?php if (!empty($p['notes_label'])): ?>
                    <p class="meta-text" style="margin:6px 0 0;font-size:12px"><?= htmlspecialchars($p['notes_label']) ?></p>
                <?php endif; ?>
                <p class="meta-text" style="margin:4px 0 0;font-size:11px;opacity:0.6"><?= htmlspecialchars($p['sku']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
