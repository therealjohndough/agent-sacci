<h1 class="page-title">Strains</h1>
<p><a href="<?= htmlspecialchars(\Config\appUrl('/strains/new')) ?>" class="app-link">Create new strain</a></p>
<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        The strains module is available in code, but the database tables are not ready yet. Run migration `014` to enable it.
    </div>
<?php elseif (empty($strains)): ?>
    <div class="card">No strains have been recorded yet.</div>
<?php else: ?>
    <?php foreach ($strains as $strain): ?>
        <div class="card">
            <h3 class="card-title">
                <a href="<?= htmlspecialchars(\Config\appUrl('/strains')) ?>?id=<?= urlencode((string) $strain['id']) ?>" class="app-link">
                    <?= htmlspecialchars($strain['name']) ?>
                </a>
            </h3>
            <p class="meta-text">
                <strong>Status:</strong> <?= htmlspecialchars($strain['status']) ?>
                <?php if (!empty($strain['category'])): ?>
                    | <strong>Category:</strong> <?= htmlspecialchars($strain['category']) ?>
                <?php endif; ?>
                <?php if (!empty($strain['lineage'])): ?>
                    | <strong>Lineage:</strong> <?= htmlspecialchars($strain['lineage']) ?>
                <?php endif; ?>
                | <strong>Batches:</strong> <?= htmlspecialchars((string) $strain['batch_count']) ?>
                | <strong>Products:</strong> <?= htmlspecialchars((string) $strain['product_count']) ?>
            </p>
            <?php if (!empty($strain['description'])): ?>
                <p><?= htmlspecialchars(substr($strain['description'], 0, 220)) ?><?= strlen($strain['description']) > 220 ? '...' : '' ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
