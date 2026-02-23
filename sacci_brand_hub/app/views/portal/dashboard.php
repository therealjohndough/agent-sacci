<h1 style="color: var(--color-accent); margin-bottom:20px;">Retail Partner Portal</h1>
<?php if (empty($assets)): ?>
    <div class="card">No assets available for your organization.</div>
<?php else: ?>
    <?php foreach ($assets as $asset): ?>
        <div class="card">
            <h3 style="margin-top:0; color: var(--color-accent);">
                <?= htmlspecialchars($asset['name']) ?>
            </h3>
            <p><?= htmlspecialchars($asset['description']) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>