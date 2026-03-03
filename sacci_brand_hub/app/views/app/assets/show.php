<h1 class="page-title">Asset: <?= htmlspecialchars($asset['name']) ?></h1>
<div class="card">
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($asset['description'])) ?></p>
    <p><strong>Category:</strong> <?= htmlspecialchars($asset['category']) ?></p>
    <p><strong>File Type:</strong> <?= htmlspecialchars($asset['file_type']) ?></p>
    <p><strong>Uploaded At:</strong> <?= htmlspecialchars($asset['created_at']) ?></p>
    <p><a href="<?= htmlspecialchars(\Config\appUrl('/assets/download')) ?>?id=<?= urlencode((string) $asset['id']) ?>" class="app-link">Download</a></p>
</div>
