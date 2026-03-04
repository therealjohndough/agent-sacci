<h1 class="page-title"><?= htmlspecialchars($batch['batch_code']) ?></h1>
<p>
    <a href="<?= htmlspecialchars(\Config\appUrl('/batches/edit')) ?>?id=<?= urlencode((string) $batch['id']) ?>" class="app-link">Edit batch</a>
</p>
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
        <?php if (!empty($batch['mood_tag'])): ?>
            | <span class="badge badge-mood"><?= htmlspecialchars($batch['mood_tag']) ?></span>
        <?php endif; ?>
    </p>

    <h2 class="section-title">Cannabinoids</h2>
    <p class="meta-text">
        <?php if ($batch['thc_percent'] !== null): ?>
            <strong>THC:</strong> <?= htmlspecialchars((string) $batch['thc_percent']) ?>%
        <?php endif; ?>
        <?php if ($batch['cbd_percent'] !== null): ?>
            &nbsp;| <strong>CBD:</strong> <?= htmlspecialchars((string) $batch['cbd_percent']) ?>%
        <?php endif; ?>
        <?php if (!empty($batch['cbg_percent'])): ?>
            &nbsp;| <strong>CBG:</strong> <?= htmlspecialchars((string) $batch['cbg_percent']) ?>%
        <?php endif; ?>
        <?php if (!empty($batch['cbn_percent'])): ?>
            &nbsp;| <strong>CBN:</strong> <?= htmlspecialchars((string) $batch['cbn_percent']) ?>%
        <?php endif; ?>
    </p>

    <?php if (!empty($batch['terp_1_name']) || !empty($batch['terp_total'])): ?>
        <h2 class="section-title">Terpenes</h2>
        <p class="meta-text">
            <?php if (!empty($batch['terp_total'])): ?>
                <strong>Total:</strong> <?= htmlspecialchars((string) $batch['terp_total']) ?>%
                &nbsp;|&nbsp;
            <?php endif; ?>
            <?php if (!empty($batch['terp_1_name'])): ?>
                <?= htmlspecialchars($batch['terp_1_name']) ?>
                <?php if (!empty($batch['terp_1_pct'])): ?>
                    (<?= htmlspecialchars((string) $batch['terp_1_pct']) ?>%)
                <?php endif; ?>
            <?php endif; ?>
            <?php if (!empty($batch['terp_2_name'])): ?>
                &nbsp;&middot;
                <?= htmlspecialchars($batch['terp_2_name']) ?>
                <?php if (!empty($batch['terp_2_pct'])): ?>
                    (<?= htmlspecialchars((string) $batch['terp_2_pct']) ?>%)
                <?php endif; ?>
            <?php endif; ?>
            <?php if (!empty($batch['terp_3_name'])): ?>
                &nbsp;&middot;
                <?= htmlspecialchars($batch['terp_3_name']) ?>
                <?php if (!empty($batch['terp_3_pct'])): ?>
                    (<?= htmlspecialchars((string) $batch['terp_3_pct']) ?>%)
                <?php endif; ?>
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <p class="meta-text">
        <strong>COAs:</strong> <?= htmlspecialchars((string) $batch['coa_count']) ?>
        &nbsp;| <strong>Products linked:</strong> <?= htmlspecialchars((string) $batch['product_count']) ?>
    </p>

    <?php if (!empty($batch['notes'])): ?>
        <h2 class="section-title">Notes</h2>
        <p><?= nl2br(htmlspecialchars($batch['notes'])) ?></p>
    <?php endif; ?>
</div>
