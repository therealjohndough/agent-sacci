<h1 class="page-title">Actions</h1>
<p><a href="<?= htmlspecialchars(\Config\appUrl('/actions/new')) ?>" class="app-link">Create new action item</a></p>
<form method="post" action="<?= htmlspecialchars(\Config\appUrl('/actions/sync-airtable')) ?>" class="inline-form">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
    <button type="submit" class="button-link">Sync from Airtable</button>
</form>
<?php if (!empty($syncMessage)): ?>
    <div class="card notice-card">
        <?= htmlspecialchars($syncMessage) ?>
    </div>
<?php endif; ?>
<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        The actions module is available in code, but the database tables are not ready yet. Run migrations `007` and `008` to enable it.
    </div>
<?php elseif (empty($actionItems)): ?>
    <div class="card">No action items have been recorded yet.</div>
<?php else: ?>
    <?php foreach ($actionItems as $item): ?>
        <div class="card">
            <h3 class="card-title"><?= htmlspecialchars($item['title']) ?></h3>
            <p><a href="<?= htmlspecialchars(\Config\appUrl('/actions/edit')) ?>?id=<?= urlencode((string) $item['id']) ?>" class="app-link">Edit action item</a></p>
            <form method="post" action="<?= htmlspecialchars(\Config\appUrl('/actions/archive')) ?>" class="inline-form">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string) $item['id']) ?>">
                <button type="submit" class="button-link">Archive action item</button>
            </form>
            <?php if (!empty($item['details'])): ?>
                <p><?= nl2br(htmlspecialchars($item['details'])) ?></p>
            <?php endif; ?>
            <p class="meta-text">
                <strong>Status:</strong> <?= htmlspecialchars($item['status']) ?>
                | <strong>Priority:</strong> <?= htmlspecialchars($item['priority']) ?>
                <?php if (!empty($item['department_name'])): ?>
                    | <strong>Department:</strong> <?= htmlspecialchars($item['department_name']) ?>
                <?php endif; ?>
                <?php if (!empty($item['owner_name'])): ?>
                    | <strong>Owner:</strong> <?= htmlspecialchars($item['owner_name']) ?>
                <?php endif; ?>
                <?php if (!empty($item['due_date'])): ?>
                    | <strong>Due:</strong> <?= htmlspecialchars($item['due_date']) ?>
                <?php endif; ?>
            </p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
