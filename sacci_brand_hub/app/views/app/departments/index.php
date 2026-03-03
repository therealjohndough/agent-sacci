<h1 class="page-title">Departments</h1>
<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        The departments directory is available in code, but the database tables are not ready yet. Run migration `003` to enable it.
    </div>
<?php elseif (empty($departments)): ?>
    <div class="card">No departments have been recorded yet.</div>
<?php else: ?>
    <?php foreach ($departments as $department): ?>
        <div class="card">
            <h3 class="card-title"><?= htmlspecialchars($department['name']) ?></h3>
            <p class="meta-text"><strong>Slug:</strong> <?= htmlspecialchars($department['slug']) ?></p>
            <?php if (!empty($department['description'])): ?>
                <p><?= nl2br(htmlspecialchars($department['description'])) ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
