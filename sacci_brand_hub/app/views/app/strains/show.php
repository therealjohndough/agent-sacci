<h1 class="page-title"><?= htmlspecialchars($strain['name']) ?></h1>
<p><a href="<?= htmlspecialchars(\Config\appUrl('/strains/edit')) ?>?id=<?= urlencode((string) $strain['id']) ?>" class="app-link">Edit strain</a></p>
<form method="post" action="<?= htmlspecialchars(\Config\appUrl('/strains/archive')) ?>" class="inline-form">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $strain['id']) ?>">
    <button type="submit" class="button-link">Archive strain</button>
</form>
<div class="card">
    <p class="meta-text">
        <strong>Status:</strong> <?= htmlspecialchars($strain['status']) ?>
        <?php if (!empty($strain['category'])): ?>
            | <strong>Category:</strong> <?= htmlspecialchars($strain['category']) ?>
        <?php endif; ?>
        <?php if (!empty($strain['lineage'])): ?>
            | <strong>Lineage:</strong> <?= htmlspecialchars($strain['lineage']) ?>
        <?php endif; ?>
        <?php if (!empty($strain['breeder'])): ?>
            | <strong>Breeder:</strong> <?= htmlspecialchars($strain['breeder']) ?>
        <?php endif; ?>
    </p>

    <p class="meta-text">
        <strong>Batch Count:</strong> <?= htmlspecialchars((string) $strain['batch_count']) ?>
        | <strong>Product Count:</strong> <?= htmlspecialchars((string) $strain['product_count']) ?>
    </p>

    <?php if (!empty($strain['description'])): ?>
        <h2 class="section-title">Notes</h2>
        <p><?= nl2br(htmlspecialchars($strain['description'])) ?></p>
    <?php endif; ?>
</div>
