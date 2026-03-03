<h1 class="page-title">Search</h1>
<form method="get" action="<?= htmlspecialchars(\Config\appUrl('/search')) ?>" class="card search-form">
    <label for="q" class="form-label">Search across meetings, actions, reports, and documents</label>
    <input type="text" id="q" name="q" value="<?= htmlspecialchars($query ?? '') ?>" class="form-input" placeholder="Search by keyword">
    <button type="submit" class="button-primary">Search</button>
</form>

<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        Search is available in code, but the supporting company-brain tables are not fully ready yet. Run the Phase 1 migrations to enable it.
    </div>
<?php elseif (($query ?? '') === ''): ?>
    <div class="card">Enter a keyword to search the company records.</div>
<?php else: ?>
    <p class="meta-text">Showing results for: <strong><?= htmlspecialchars($query) ?></strong></p>

    <h2 class="section-title">Meetings</h2>
    <?php if (empty($results['meetings'])): ?>
        <div class="card">No meeting matches.</div>
    <?php else: ?>
        <?php foreach ($results['meetings'] as $meeting): ?>
            <div class="card">
                <h3 class="card-title">
                    <a href="<?= htmlspecialchars(\Config\appUrl('/meetings')) ?>?id=<?= urlencode((string) $meeting['id']) ?>" class="app-link">
                        <?= htmlspecialchars($meeting['title']) ?>
                    </a>
                </h3>
                <?php if (!empty($meeting['summary'])): ?>
                    <p><?= htmlspecialchars($meeting['summary']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <h2 class="section-title">Actions</h2>
    <?php if (empty($results['actions'])): ?>
        <div class="card">No action item matches.</div>
    <?php else: ?>
        <?php foreach ($results['actions'] as $item): ?>
            <div class="card">
                <h3 class="card-title"><?= htmlspecialchars($item['title']) ?></h3>
                <?php if (!empty($item['details'])): ?>
                    <p><?= htmlspecialchars($item['details']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <h2 class="section-title">Reports</h2>
    <?php if (empty($results['reports'])): ?>
        <div class="card">No report matches.</div>
    <?php else: ?>
        <?php foreach ($results['reports'] as $report): ?>
            <div class="card">
                <h3 class="card-title">
                    <a href="<?= htmlspecialchars(\Config\appUrl('/reports')) ?>?id=<?= urlencode((string) $report['id']) ?>" class="app-link">
                        <?= htmlspecialchars($report['title']) ?>
                    </a>
                </h3>
                <?php if (!empty($report['summary'])): ?>
                    <p><?= htmlspecialchars($report['summary']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <h2 class="section-title">Documents</h2>
    <?php if (empty($results['documents'])): ?>
        <div class="card">No document matches.</div>
    <?php else: ?>
        <?php foreach ($results['documents'] as $document): ?>
            <div class="card">
                <h3 class="card-title">
                    <a href="<?= htmlspecialchars(\Config\appUrl('/documents')) ?>?id=<?= urlencode((string) $document['id']) ?>" class="app-link">
                        <?= htmlspecialchars($document['title']) ?>
                    </a>
                </h3>
                <p><?= htmlspecialchars(substr(strip_tags($document['content']), 0, 220)) ?>...</p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>
