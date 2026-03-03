<h1 class="page-title"><?= htmlspecialchars($batch['batch_code']) ?></h1>
<p><a href="<?= htmlspecialchars(\Config\appUrl('/batches/edit')) ?>?id=<?= urlencode((string) $batch['id']) ?>" class="app-link">Edit batch</a></p>
<form method="post" action="<?= htmlspecialchars(\Config\appUrl('/batches/archive')) ?>" class="inline-form">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $batch['id']) ?>">
    <button type="submit" class="button-link">Archive batch</button>
</form>
<div class="card">
    <p class="meta-text">
        <strong>Strain:</strong>
        <a href="<?= htmlspecialchars(\Config\appUrl('/strains')) ?>?id=<?= urlencode((string) $batch['strain_id']) ?>" class="app-link">
            <?= htmlspecialchars($batch['strain_name']) ?>
        </a>
        | <strong>Status:</strong> <?= htmlspecialchars($batch['production_status']) ?>
        <?php if (!empty($batch['harvest_date'])): ?>
            | <strong>Harvest:</strong> <?= htmlspecialchars($batch['harvest_date']) ?>
        <?php endif; ?>
    </p>

    <p class="meta-text">
        <?php if ($batch['thc_percent'] !== null): ?>
            <strong>THC:</strong> <?= htmlspecialchars((string) $batch['thc_percent']) ?>%
        <?php endif; ?>
        <?php if ($batch['cbd_percent'] !== null): ?>
            | <strong>CBD:</strong> <?= htmlspecialchars((string) $batch['cbd_percent']) ?>%
        <?php endif; ?>
    </p>

    <p class="meta-text">
        <strong>COA Count:</strong> <?= htmlspecialchars((string) $batch['coa_count']) ?>
        | <strong>Product Count:</strong> <?= htmlspecialchars((string) $batch['product_count']) ?>
    </p>

    <?php if (!empty($batch['notes'])): ?>
        <h2 class="section-title">Notes</h2>
        <p><?= nl2br(htmlspecialchars($batch['notes'])) ?></p>
    <?php endif; ?>
</div>
