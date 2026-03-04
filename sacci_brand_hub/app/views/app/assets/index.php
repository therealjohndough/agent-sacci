<h1 class="page-title">Asset Library</h1>

<!-- Filter + upload bar -->
<div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;margin-bottom:24px">
    <form method="get" action="<?= htmlspecialchars(\Config\appUrl('/assets')) ?>" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
        <div>
            <label class="form-label" style="margin-bottom:4px;font-size:13px">Category</label>
            <select name="category" class="form-input" style="width:auto;padding:8px 12px">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= ($filterCategory === $cat) ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucwords(str_replace('-', ' ', $cat))) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label" style="margin-bottom:4px;font-size:13px">Search</label>
            <input type="text" name="q" class="form-input" style="width:180px;padding:8px 12px"
                   value="<?= htmlspecialchars($filterSearch ?? '') ?>" placeholder="Name…">
        </div>
        <button type="submit" class="button-primary" style="margin-top:0">Filter</button>
        <?php if ($filterCategory || $filterSearch): ?>
            <a href="<?= htmlspecialchars(\Config\appUrl('/assets')) ?>" class="app-link" style="line-height:2.4">Clear</a>
        <?php endif; ?>
    </form>
    <?php if (!empty($canUpload)): ?>
        <a href="<?= htmlspecialchars(\Config\appUrl('/assets/upload')) ?>" class="button-primary" style="margin-top:0;text-decoration:none;display:inline-block">+ Upload Asset</a>
    <?php endif; ?>
</div>

<?php if (empty($assets)): ?>
    <div class="card">No assets found.</div>
<?php else: ?>
    <p class="meta-text" style="margin-bottom:16px"><?= count($assets) ?> asset<?= count($assets) !== 1 ? 's' : '' ?></p>
    <div class="section-grid">
        <?php foreach ($assets as $a): ?>
            <div class="card section-card">
                <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--color-muted)">
                    <?= htmlspecialchars(ucwords(str_replace('-', ' ', $a['category'] ?? ''))) ?>
                    <?php if (!empty($a['visibility']) && $a['visibility'] !== 'public'): ?>
                        &middot; <span style="color:var(--color-accent)"><?= htmlspecialchars($a['visibility']) ?></span>
                    <?php endif; ?>
                </p>
                <h3 class="card-title" style="margin-bottom:4px"><?= htmlspecialchars($a['name']) ?></h3>
                <?php if (!empty($a['brand'])): ?>
                    <p class="meta-text" style="margin:0 0 6px;font-size:12px"><?= htmlspecialchars($a['brand']) ?></p>
                <?php endif; ?>
                <?php if (!empty($a['description'])): ?>
                    <p class="meta-text" style="margin:0 0 8px;font-size:13px"><?= htmlspecialchars(mb_strimwidth($a['description'], 0, 120, '…')) ?></p>
                <?php endif; ?>
                <a href="<?= htmlspecialchars(\Config\appUrl('/assets/download')) ?>?id=<?= urlencode((string) $a['id']) ?>" class="app-link" style="font-size:13px">Download</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
