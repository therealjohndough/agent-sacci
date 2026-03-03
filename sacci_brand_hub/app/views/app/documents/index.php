<h1 class="page-title">Documents</h1>
<p><a href="<?= htmlspecialchars(\Config\appUrl('/documents/new')) ?>" class="app-link">Create new document</a></p>
<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        The documents module is available in code, but the database tables are not ready yet. Run migrations `009` and `010` to enable it.
    </div>
<?php elseif (empty($documents)): ?>
    <div class="card">No internal documents have been recorded yet.</div>
<?php else: ?>
    <?php foreach ($documents as $document): ?>
        <div class="card">
            <h3 class="card-title">
                <a href="<?= htmlspecialchars(\Config\appUrl('/documents')) ?>?id=<?= urlencode((string) $document['id']) ?>" class="app-link">
                    <?= htmlspecialchars($document['title']) ?>
                </a>
            </h3>
            <p class="meta-text">
                <strong>Type:</strong> <?= htmlspecialchars($document['document_type']) ?>
                | <strong>Status:</strong> <?= htmlspecialchars($document['status']) ?>
                <?php if (!empty($document['department_name'])): ?>
                    | <strong>Department:</strong> <?= htmlspecialchars($document['department_name']) ?>
                <?php endif; ?>
                <?php if (!empty($document['owner_name'])): ?>
                    | <strong>Owner:</strong> <?= htmlspecialchars($document['owner_name']) ?>
                <?php endif; ?>
                <?php if (!empty($document['version_label'])): ?>
                    | <strong>Version:</strong> <?= htmlspecialchars($document['version_label']) ?>
                <?php endif; ?>
            </p>
            <p><?= htmlspecialchars(substr(strip_tags($document['content']), 0, 220)) ?>...</p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
