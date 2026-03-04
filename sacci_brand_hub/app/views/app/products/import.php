<h1 class="page-title">Import Products</h1>
<p><a href="<?= htmlspecialchars(\Config\appUrl('/batches')) ?>" class="app-link">&larr; Back to batches</a></p>

<?php if (!empty($error)): ?>
    <div class="card notice-card"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (!empty($result)): ?>
    <div class="card">
        <p class="meta-text">
            <strong>Inserted:</strong> <?= (int) $result['inserted'] ?>
            &nbsp;|&nbsp;
            <strong>Updated:</strong> <?= (int) $result['updated'] ?>
            &nbsp;|&nbsp;
            <strong>Skipped (no strain match):</strong> <?= (int) $result['skipped'] ?>
        </p>
        <?php if (!empty($result['errors'])): ?>
            <p><strong>Errors / warnings:</strong></p>
            <ul>
                <?php foreach ($result['errors'] as $err): ?>
                    <li><?= $err ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="card">
    <p class="meta-text">
        Upload the inventory CSV to upsert products. Strains must already be imported.<br>
        Expected columns: <em>Product Name, Variety/Note, Weight/Size, Category, Notes,
        THC, Genetics A, Genetics B, Effects, Flavor, Consumer Psychology,
        DESCRIPTION, Dough's New Copy, Vibe</em>
    </p>
    <p class="meta-text">
        SKU is generated as <code>{strain-slug}-{format}-{normalized-weight}</code>.
        Existing products with the same SKU are updated; new SKUs are inserted.
    </p>
    <form method="post" action="<?= htmlspecialchars(\Config\appUrl('/products/import')) ?>" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
        <p>
            <label for="csv">CSV file:</label><br>
            <input type="file" id="csv" name="csv" accept=".csv,text/csv" required>
        </p>
        <p><button type="submit" class="button-primary">Import Products</button></p>
    </form>
</div>
