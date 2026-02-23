<h1 style="color: var(--color-accent); margin-bottom:20px;">Asset: <?= htmlspecialchars($asset['name']) ?></h1>
<div class="card">
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($asset['description'])) ?></p>
    <p><strong>Category:</strong> <?= htmlspecialchars($asset['category']) ?></p>
    <p><strong>File Type:</strong> <?= htmlspecialchars($asset['file_type']) ?></p>
    <p><strong>Uploaded At:</strong> <?= htmlspecialchars($asset['created_at']) ?></p>
    <p><a href="/assets/download?id=<?= $asset['id'] ?>" style="color: var(--color-accent);">Download</a></p>
</div>