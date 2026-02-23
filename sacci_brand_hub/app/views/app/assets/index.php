<h1 style="color: var(--color-accent); margin-bottom:20px;">Assets</h1>
<?php if (empty($assets)): ?>
    <div class="card">No assets available.</div>
<?php else: ?>
    <?php foreach ($assets as $asset): ?>
        <div class="card">
            <h3 style="margin-top:0; color: var(--color-accent);">
                <a href="/assets?id=<?= $asset['id'] ?>" style="color: var(--color-accent); text-decoration:none;">
                    <?= htmlspecialchars($asset['name']) ?>
                </a>
            </h3>
            <p><?= htmlspecialchars($asset['description']) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>