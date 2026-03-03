<h1 class="page-title">Meetings</h1>
<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        The meetings module is available in code, but the database tables are not ready yet. Run migrations `003`, `004`, and `005` to enable it.
    </div>
<?php elseif (empty($meetings)): ?>
    <div class="card">No meetings have been recorded yet.</div>
<?php else: ?>
    <?php foreach ($meetings as $meeting): ?>
        <div class="card">
            <h3 class="card-title">
                <a href="<?= htmlspecialchars(\Config\appUrl('/meetings')) ?>?id=<?= urlencode((string) $meeting['id']) ?>" class="app-link">
                    <?= htmlspecialchars($meeting['title']) ?>
                </a>
            </h3>
            <p class="meta-text">
                <strong>Type:</strong> <?= htmlspecialchars($meeting['meeting_type']) ?>
                <?php if (!empty($meeting['department_name'])): ?>
                    | <strong>Department:</strong> <?= htmlspecialchars($meeting['department_name']) ?>
                <?php endif; ?>
                <?php if (!empty($meeting['occurred_at'])): ?>
                    | <strong>Occurred:</strong> <?= htmlspecialchars($meeting['occurred_at']) ?>
                <?php endif; ?>
            </p>
            <?php if (!empty($meeting['summary'])): ?>
                <p><?= htmlspecialchars($meeting['summary']) ?></p>
            <?php else: ?>
                <p><?= htmlspecialchars(substr(strip_tags($meeting['notes']), 0, 180)) ?>...</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
