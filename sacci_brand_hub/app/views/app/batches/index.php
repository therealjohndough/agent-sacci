<h1 class="page-title">Batches</h1>
<p><a href="<?= htmlspecialchars(\Config\appUrl('/batches/new')) ?>" class="app-link">Create new batch</a></p>
<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        The batches module is available in code, but the database tables are not ready yet. Run migration `014` to enable it.
    </div>
<?php elseif (empty($batches)): ?>
    <div class="card">No batches have been recorded yet.</div>
<?php else: ?>
    <?php foreach ($batches as $batch): ?>
        <div class="card">
            <h3 class="card-title">
                <a href="<?= htmlspecialchars(\Config\appUrl('/batches')) ?>?id=<?= urlencode((string) $batch['id']) ?>" class="app-link">
                    <?= htmlspecialchars($batch['batch_code']) ?>
                </a>
            </h3>
            <p class="meta-text">
                <strong>Strain:</strong> <?= htmlspecialchars($batch['strain_name']) ?>
                | <strong>Status:</strong> <?= htmlspecialchars($batch['production_status']) ?>
                <?php if (!empty($batch['harvest_date'])): ?>
                    | <strong>Harvest:</strong> <?= htmlspecialchars($batch['harvest_date']) ?>
                <?php endif; ?>
                | <strong>COAs:</strong> <?= htmlspecialchars((string) $batch['coa_count']) ?>
                | <strong>Products:</strong> <?= htmlspecialchars((string) $batch['product_count']) ?>
            </p>
            <?php if ($batch['thc_percent'] !== null || $batch['cbd_percent'] !== null): ?>
                <p class="meta-text">
                    <?php if ($batch['thc_percent'] !== null): ?>
                        <strong>THC:</strong> <?= htmlspecialchars((string) $batch['thc_percent']) ?>%
                    <?php endif; ?>
                    <?php if ($batch['cbd_percent'] !== null): ?>
                        | <strong>CBD:</strong> <?= htmlspecialchars((string) $batch['cbd_percent']) ?>%
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($batch['notes'])): ?>
                <p><?= htmlspecialchars(substr($batch['notes'], 0, 220)) ?><?= strlen($batch['notes']) > 220 ? '...' : '' ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
