<h1 class="page-title">Assets</h1>
<?php if (empty($assets)): ?>
    <div class="card">No assets available.</div>
<?php else: ?>
    <?php foreach ($assets as $asset): ?>
        <div class="card">
            <h3 class="card-title">
                <a href="<?= htmlspecialchars(\Config\appUrl('/assets')) ?>?id=<?= urlencode((string) $asset['id']) ?>" class="app-link">
                    <?= htmlspecialchars($asset['name']) ?>
                </a>
            </h3>
            <p><?= htmlspecialchars($asset['description']) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
