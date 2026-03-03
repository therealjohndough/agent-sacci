<h1 class="page-title"><?= htmlspecialchars($document['title']) ?></h1>
<p><a href="<?= htmlspecialchars(\Config\appUrl('/documents/edit')) ?>?id=<?= urlencode((string) $document['id']) ?>" class="app-link">Edit document</a></p>
<div class="card">
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

    <h2 class="section-title">Content</h2>
    <p><?= nl2br(htmlspecialchars($document['content'])) ?></p>

    <?php if (!empty($document['source_url'])): ?>
        <p><strong>Source:</strong> <a href="<?= htmlspecialchars($document['source_url']) ?>" class="app-link"><?= htmlspecialchars($document['source_url']) ?></a></p>
    <?php endif; ?>
</div>
