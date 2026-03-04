<h1 class="page-title">Brand Hub</h1>

<?php if (!empty($storageWarning)): ?>
    <div class="card" style="border-color:#c0392b;background:#2d0a0a;color:#e74c3c;margin-bottom:1rem;">
        <strong>Security Warning:</strong> The <code>storage/</code> directory may be web-accessible.
        Ensure <code>storage/.htaccess</code> is in place denying all HTTP access.
    </div>
<?php endif; ?>

<!-- Cannabis Catalog Stats -->
<h2 class="section-title" style="margin-top:0">Catalog Overview</h2>
<div class="metric-grid">
    <div class="card metric-card">
        <p class="metric-label">Strains</p>
        <p class="metric-value"><?= (int) ($stats['strains'] ?? 0) ?></p>
        <p>
            <a href="<?= htmlspecialchars(\Config\appUrl('/strains')) ?>" class="app-link">View all</a>
            &nbsp;&middot;&nbsp;
            <a href="<?= htmlspecialchars(\Config\appUrl('/strains/import')) ?>" class="app-link">Import CSV</a>
        </p>
    </div>
    <div class="card metric-card">
        <p class="metric-label">Active Batches</p>
        <p class="metric-value"><?= (int) ($stats['batches'] ?? 0) ?></p>
        <p>
            <a href="<?= htmlspecialchars(\Config\appUrl('/batches')) ?>" class="app-link">View all</a>
            &nbsp;&middot;&nbsp;
            <a href="<?= htmlspecialchars(\Config\appUrl('/batches/coa-upload')) ?>" class="app-link">Upload COA</a>
        </p>
    </div>
    <div class="card metric-card">
        <p class="metric-label">Products</p>
        <p class="metric-value"><?= (int) ($stats['products'] ?? 0) ?></p>
        <p><a href="<?= htmlspecialchars(\Config\appUrl('/products/import')) ?>" class="app-link">Import CSV</a></p>
    </div>
    <div class="card metric-card">
        <p class="metric-label">COAs</p>
        <p class="metric-value"><?= (int) ($stats['coas'] ?? 0) ?></p>
        <p><a href="<?= htmlspecialchars(\Config\appUrl('/batches/coa-upload')) ?>" class="app-link">Upload PDF</a></p>
    </div>
</div>

<?php if (empty($stats['strains'])): ?>
    <!-- Setup Workflow Guide — shown until strains are imported -->
    <h2 class="section-title">Getting Started</h2>
    <div class="workflow-guide">
        <div class="workflow-step">
            <p class="workflow-step-num">1</p>
            <p class="workflow-step-label">Import Strains</p>
            <p class="workflow-step-desc">Upload your strain library CSV to populate the catalog with genetics, cannabinoid references, and dominant terpenes.</p>
            <a href="<?= htmlspecialchars(\Config\appUrl('/strains/import')) ?>" class="button-primary" style="display:inline-block;text-decoration:none;margin-top:0">Import Strains</a>
        </div>
        <div class="workflow-step">
            <p class="workflow-step-num">2</p>
            <p class="workflow-step-label">Import Products</p>
            <p class="workflow-step-desc">Upload the inventory CSV to create product SKUs (flower, pre-rolls, concentrates) linked to each strain.</p>
            <a href="<?= htmlspecialchars(\Config\appUrl('/products/import')) ?>" class="button-primary" style="display:inline-block;text-decoration:none;margin-top:0">Import Products</a>
        </div>
        <div class="workflow-step">
            <p class="workflow-step-num">3</p>
            <p class="workflow-step-label">Upload COA PDF</p>
            <p class="workflow-step-desc">For each harvest lot, upload the Certificate of Analysis. AI extracts batch codes, cannabinoid percentages, and terpene data automatically.</p>
            <a href="<?= htmlspecialchars(\Config\appUrl('/batches/coa-upload')) ?>" class="button-primary" style="display:inline-block;text-decoration:none;margin-top:0">Upload COA</a>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($recentBatches)): ?>
    <h2 class="section-title">Recent Batches</h2>
    <div class="section-grid">
        <?php foreach ($recentBatches as $b): ?>
            <div class="card section-card">
                <h3 class="card-title" style="margin-bottom:6px">
                    <a href="<?= htmlspecialchars(\Config\appUrl('/batches')) ?>?id=<?= urlencode((string) $b['id']) ?>" class="app-link">
                        <?= htmlspecialchars($b['batch_code']) ?>
                    </a>
                </h3>
                <p class="meta-text" style="margin:0 0 8px">
                    <?= htmlspecialchars($b['strain_name']) ?>
                    <?php if (!empty($b['mood_tag'])): ?>
                        &nbsp;<span class="badge badge-mood"><?= htmlspecialchars($b['mood_tag']) ?></span>
                    <?php endif; ?>
                </p>
                <p class="meta-text" style="margin:0">
                    <?php if ($b['thc_percent'] !== null): ?>
                        <strong>THC</strong> <?= htmlspecialchars((string) $b['thc_percent']) ?>%
                    <?php endif; ?>
                    <?php if ($b['cbd_percent'] !== null): ?>
                        &nbsp; <strong>CBD</strong> <?= htmlspecialchars((string) $b['cbd_percent']) ?>%
                    <?php endif; ?>
                    <?php if (!empty($b['terp_1_name'])): ?>
                        &nbsp;&middot; <?= htmlspecialchars($b['terp_1_name']) ?>
                        <?php if (!empty($b['terp_1_pct'])): ?>
                            <?= htmlspecialchars((string) $b['terp_1_pct']) ?>%
                        <?php endif; ?>
                    <?php endif; ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($strains)): ?>
    <h2 class="section-title">Strain Catalog</h2>
    <div class="strain-grid">
        <?php foreach ($strains as $s): ?>
            <div class="card strain-card">
                <?php if ($s['thc_ref'] !== null): ?>
                    <span class="thc-badge"><?= htmlspecialchars((string) $s['thc_ref']) ?>%</span>
                <?php endif; ?>
                <h3 class="card-title" style="margin-bottom:4px;padding-right:50px">
                    <a href="<?= htmlspecialchars(\Config\appUrl('/strains')) ?>?id=<?= urlencode((string) $s['id']) ?>" class="app-link">
                        <?= htmlspecialchars($s['name']) ?>
                    </a>
                </h3>
                <?php if (!empty($s['category'])): ?>
                    <p class="meta-text" style="margin:0 0 6px"><?= htmlspecialchars($s['category']) ?></p>
                <?php endif; ?>
                <?php if (!empty($s['terp_1_ref'])): ?>
                    <p class="meta-text" style="margin:0 0 6px;font-size:13px">
                        <?= htmlspecialchars($s['terp_1_ref']) ?>
                        <?php if (!empty($s['terp_2_ref'])): ?>
                            &middot; <?= htmlspecialchars($s['terp_2_ref']) ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
                <p class="meta-text" style="margin:0;font-size:12px">
                    <?= (int) $s['batch_count'] ?> batch<?= (int) $s['batch_count'] !== 1 ? 'es' : '' ?>
                    &middot; <?= (int) $s['product_count'] ?> product<?= (int) $s['product_count'] !== 1 ? 's' : '' ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Internal Team Tools -->
<?php if (!empty($metrics)): ?>
    <h2 class="section-title">Team</h2>
    <div class="metric-grid">
        <?php foreach ($metrics as $metric): ?>
            <div class="card metric-card">
                <p class="metric-label"><?= htmlspecialchars($metric['label']) ?></p>
                <p class="metric-value"><?= htmlspecialchars((string) $metric['value']) ?></p>
                <p><a href="<?= htmlspecialchars(\Config\appUrl($metric['link'])) ?>" class="app-link">Open</a></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h2 class="section-title">My Tickets</h2>
<?php if (empty($tickets)): ?>
    <div class="card">No tickets assigned to you.</div>
<?php else: ?>
    <?php foreach ($tickets as $t): ?>
        <div class="card">
            <h3 class="card-title">
                <a href="<?= htmlspecialchars(\Config\appUrl('/tickets')) ?>?id=<?= urlencode((string) $t['id']) ?>" class="app-link">
                    #<?= $t['id'] ?> - <?= htmlspecialchars($t['title']) ?>
                </a>
            </h3>
            <p><strong>Status:</strong> <?= htmlspecialchars($t['status']) ?> | <strong>Due:</strong> <?= htmlspecialchars($t['due_date']) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
