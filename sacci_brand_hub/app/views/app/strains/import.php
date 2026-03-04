<h1 class="page-title">Import Strains</h1>
<p><a href="<?= htmlspecialchars(\Config\appUrl('/strains')) ?>" class="app-link">&larr; Back to strains</a></p>

<?php if (!empty($error)): ?>
    <div class="card notice-card"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (!empty($result)): ?>
    <div class="card">
        <p class="meta-text">
            <strong>Inserted:</strong> <?= (int) $result['inserted'] ?>
            &nbsp;|&nbsp;
            <strong>Updated:</strong> <?= (int) $result['updated'] ?>
        </p>
        <?php if (!empty($result['errors'])): ?>
            <p><strong>Errors:</strong></p>
            <ul>
                <?php foreach ($result['errors'] as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="card">
    <p class="meta-text">
        Upload <code>strain_library_import.csv</code> to upsert strains by slug.
        Expected columns: <em>Strain Name, Category, Genetics A, Genetics B, Lineage/Notes,
        THC, CBG, CBN, Terp 1, Terp 2, Terp 3, Description/Awards</em>
    </p>
    <form method="post" action="<?= htmlspecialchars(\Config\appUrl('/strains/import')) ?>" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
        <p>
            <label for="csv">CSV file:</label><br>
            <input type="file" id="csv" name="csv" accept=".csv,text/csv" required>
        </p>
        <p><button type="submit" class="button-primary">Import Strains</button></p>
    </form>
</div>
