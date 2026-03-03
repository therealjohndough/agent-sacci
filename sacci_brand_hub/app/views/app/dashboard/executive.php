<h1 class="page-title">Executive Overview</h1>
<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        The executive overview is available in code, but the supporting company-brain tables are not fully ready yet. Run migrations `003` through `012` to enable it.
    </div>
<?php endif; ?>

<div class="section-grid">
    <section class="card section-card">
        <h2 class="section-title compact-title">Recent Meetings</h2>
        <?php if (empty($recentMeetings)): ?>
            <p>No meetings available.</p>
        <?php else: ?>
            <ul class="simple-list">
                <?php foreach ($recentMeetings as $meeting): ?>
                    <li>
                        <a href="<?= htmlspecialchars(\Config\appUrl('/meetings')) ?>?id=<?= urlencode((string) $meeting['id']) ?>" class="app-link">
                            <?= htmlspecialchars($meeting['title']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="card section-card">
        <h2 class="section-title compact-title">Open Actions</h2>
        <?php if (empty($openActions)): ?>
            <p>No open action items.</p>
        <?php else: ?>
            <ul class="simple-list">
                <?php foreach ($openActions as $item): ?>
                    <li>
                        <?= htmlspecialchars($item['title']) ?>
                        <?php if (!empty($item['due_date'])): ?>
                            <span class="meta-text">(Due <?= htmlspecialchars($item['due_date']) ?>)</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="card section-card">
        <h2 class="section-title compact-title">Published Reports</h2>
        <?php if (empty($recentReports)): ?>
            <p>No reports available.</p>
        <?php else: ?>
            <ul class="simple-list">
                <?php foreach ($recentReports as $report): ?>
                    <li>
                        <a href="<?= htmlspecialchars(\Config\appUrl('/reports')) ?>?id=<?= urlencode((string) $report['id']) ?>" class="app-link">
                            <?= htmlspecialchars($report['title']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="card section-card">
        <h2 class="section-title compact-title">Active Documents</h2>
        <?php if (empty($recentDocuments)): ?>
            <p>No documents available.</p>
        <?php else: ?>
            <ul class="simple-list">
                <?php foreach ($recentDocuments as $document): ?>
                    <li>
                        <a href="<?= htmlspecialchars(\Config\appUrl('/documents')) ?>?id=<?= urlencode((string) $document['id']) ?>" class="app-link">
                            <?= htmlspecialchars($document['title']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>
