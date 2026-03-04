<p style="margin-bottom:16px">
    <a href="<?= htmlspecialchars(\Config\appUrl('/products')) ?>" class="app-link">&larr; All Products</a>
</p>

<h1 class="page-title" style="margin-bottom:4px"><?= htmlspecialchars($product['product_name']) ?></h1>
<p class="meta-text" style="margin:0 0 24px">
    SKU: <?= htmlspecialchars($product['sku']) ?>
    <?php if (!empty($product['internal_status'])): ?>
        &middot; <span style="text-transform:uppercase;font-size:12px"><?= htmlspecialchars($product['internal_status']) ?></span>
    <?php endif; ?>
</p>

<div class="section-grid" style="margin-bottom:24px">

    <!-- Product details -->
    <div class="card section-card">
        <h2 class="section-title" style="margin-top:0">Product Details</h2>
        <p class="meta-text"><strong style="color:var(--color-surface)">Type:</strong> <?= htmlspecialchars(ucfirst($product['format'])) ?></p>
        <?php if (!empty($product['weight_label'])): ?>
            <p class="meta-text"><strong style="color:var(--color-surface)">Size:</strong> <?= htmlspecialchars($product['weight_label']) ?></p>
        <?php endif; ?>
        <?php if (!empty($product['mood_tag'])): ?>
            <p class="meta-text"><strong style="color:var(--color-surface)">Mood:</strong> <span class="badge badge-mood"><?= htmlspecialchars($product['mood_tag']) ?></span></p>
        <?php endif; ?>
        <?php if (!empty($product['notes_label'])): ?>
            <p class="meta-text"><strong style="color:var(--color-surface)">Notes:</strong> <?= htmlspecialchars($product['notes_label']) ?></p>
        <?php endif; ?>
        <?php if (!empty($product['description'])): ?>
            <p style="margin-top:12px;line-height:1.6;font-size:14px"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        <?php endif; ?>
    </div>

    <!-- Linked strain -->
    <div class="card section-card">
        <h2 class="section-title" style="margin-top:0">
            <a href="<?= htmlspecialchars(\Config\appUrl('/strains')) ?>?id=<?= urlencode((string) $product['strain_id']) ?>" class="app-link">
                <?= htmlspecialchars($product['strain_name']) ?>
            </a>
        </h2>
        <?php if (!empty($product['strain_category'])): ?>
            <p class="meta-text" style="margin:0 0 8px"><?= htmlspecialchars($product['strain_category']) ?></p>
        <?php endif; ?>
        <?php if ($product['thc_ref'] !== null): ?>
            <p class="meta-text"><strong style="color:var(--color-surface)">THC ref:</strong> <?= htmlspecialchars((string) $product['thc_ref']) ?>%</p>
        <?php endif; ?>
        <?php if ($product['cbg_ref'] !== null): ?>
            <p class="meta-text"><strong style="color:var(--color-surface)">CBG ref:</strong> <?= htmlspecialchars((string) $product['cbg_ref']) ?>%</p>
        <?php endif; ?>
        <?php if (!empty($product['terp_1_ref'])): ?>
            <p class="meta-text">
                <strong style="color:var(--color-surface)">Top terpenes:</strong>
                <?= htmlspecialchars($product['terp_1_ref']) ?>
                <?php if (!empty($product['terp_2_ref'])): ?>, <?= htmlspecialchars($product['terp_2_ref']) ?><?php endif; ?>
                <?php if (!empty($product['terp_3_ref'])): ?>, <?= htmlspecialchars($product['terp_3_ref']) ?><?php endif; ?>
            </p>
        <?php endif; ?>
        <?php if (!empty($product['strain_description'])): ?>
            <p style="margin-top:12px;font-size:13px;color:var(--color-muted);line-height:1.5"><?= htmlspecialchars(mb_strimwidth($product['strain_description'], 0, 200, '…')) ?></p>
        <?php endif; ?>
    </div>

</div>

<!-- Active batches for this strain -->
<?php if (!empty($batches)): ?>
    <h2 class="section-title">Active Batches (<?= htmlspecialchars($product['strain_name']) ?>)</h2>
    <div class="section-grid">
        <?php foreach ($batches as $b): ?>
            <div class="card section-card">
                <h3 class="card-title" style="margin-bottom:6px">
                    <a href="<?= htmlspecialchars(\Config\appUrl('/batches')) ?>?id=<?= urlencode((string) $b['id']) ?>" class="app-link">
                        <?= htmlspecialchars($b['batch_code']) ?>
                    </a>
                </h3>
                <p class="meta-text" style="margin:0 0 8px;text-transform:uppercase;font-size:11px"><?= htmlspecialchars($b['production_status']) ?></p>
                <p class="meta-text" style="margin:0">
                    <?php if ($b['thc_percent'] !== null): ?>
                        <strong>THC</strong> <?= htmlspecialchars((string) $b['thc_percent']) ?>%
                    <?php endif; ?>
                    <?php if ($b['cbd_percent'] !== null): ?>
                        &nbsp;<strong>CBD</strong> <?= htmlspecialchars((string) $b['cbd_percent']) ?>%
                    <?php endif; ?>
                    <?php if ($b['cbg_percent'] !== null): ?>
                        &nbsp;<strong>CBG</strong> <?= htmlspecialchars((string) $b['cbg_percent']) ?>%
                    <?php endif; ?>
                </p>
                <?php if (!empty($b['terp_1_name'])): ?>
                    <p class="meta-text" style="margin:6px 0 0;font-size:12px">
                        <?= htmlspecialchars($b['terp_1_name']) ?>
                        <?php if (!empty($b['terp_1_pct'])): ?> <?= htmlspecialchars((string) $b['terp_1_pct']) ?>%<?php endif; ?>
                        <?php if (!empty($b['terp_2_name'])): ?> &middot; <?= htmlspecialchars($b['terp_2_name']) ?><?php endif; ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($b['mood_tag'])): ?>
                    <p style="margin-top:8px"><span class="badge badge-mood"><?= htmlspecialchars($b['mood_tag']) ?></span></p>
                <?php endif; ?>
                <?php if (!empty($b['coa_id'])): ?>
                    <p style="margin-top:8px">
                        <a href="<?= htmlspecialchars(\Config\appUrl('/assets/download')) ?>?id=<?= urlencode((string) $b['coa_id']) ?>" class="app-link" style="font-size:13px">Download COA</a>
                    </p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="card notice-card">No active batches for this strain yet. <a href="<?= htmlspecialchars(\Config\appUrl('/batches/coa-upload')) ?>" class="app-link">Upload a COA</a> to create one.</div>
<?php endif; ?>
