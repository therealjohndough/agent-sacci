<h1 class="page-title">Upload COA PDF</h1>
<p><a href="<?= htmlspecialchars(\Config\appUrl('/batches')) ?>" class="app-link">&larr; Back to batches</a></p>

<?php if (!empty($error)): ?>
    <div class="card notice-card"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <p class="meta-text">
        Upload a Certificate of Analysis (PDF, max 10 MB). The document will be parsed
        automatically by AI to extract the batch code, cannabinoid percentages, and
        terpene profile. A batch record will be created and the mood tag auto-set.
    </p>
    <form method="post" action="<?= htmlspecialchars(\Config\appUrl('/batches/coa-upload')) ?>" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
        <p>
            <label for="strain_id">Strain:</label><br>
            <select id="strain_id" name="strain_id" required>
                <option value="">— select strain —</option>
                <?php foreach ($strains as $s): ?>
                    <option value="<?= htmlspecialchars((string) $s['id']) ?>"
                        <?= (int) ($selected_strain ?? 0) === (int) $s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="coa_pdf">COA PDF:</label><br>
            <input type="file" id="coa_pdf" name="coa_pdf" accept=".pdf,application/pdf" required>
        </p>
        <p><button type="submit" class="button-primary">Parse &amp; Import COA</button></p>
    </form>
</div>
