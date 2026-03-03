<h1 class="page-title">Retail Partner Portal</h1>
<?php if (empty($assets)): ?>
    <div class="card">No assets available for your organization.</div>
<?php else: ?>
    <?php foreach ($assets as $asset): ?>
        <div class="card">
            <h3 class="card-title">
                <?= htmlspecialchars($asset['name']) ?>
            </h3>
            <p><?= htmlspecialchars($asset['description']) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
