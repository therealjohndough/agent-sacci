<h1 class="page-title">Retail Partner Portal</h1>

<?php if (empty($assets)): ?>
    <div class="card">No assets are currently available for your organization.</div>
<?php else: ?>
    <div class="section-grid">
        <?php foreach ($assets as $asset): ?>
            <div class="card">
                <h3 class="card-title"><?= htmlspecialchars($asset['name']) ?></h3>
                <?php if (!empty($asset['category'])): ?>
                    <span class="badge"><?= htmlspecialchars(ucfirst($asset['category'])) ?></span>
                <?php endif; ?>
                <?php if (!empty($asset['brand'])): ?>
                    <p class="meta-text"><?= htmlspecialchars($asset['brand']) ?></p>
                <?php endif; ?>
                <?php if (!empty($asset['description'])): ?>
                    <p><?= htmlspecialchars($asset['description']) ?></p>
                <?php endif; ?>
                <p style="margin-top:12px">
                    <a href="<?= htmlspecialchars(\Config\appUrl('/assets/download?id=' . (int)$asset['id'])) ?>"
                       class="app-link">Download</a>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
