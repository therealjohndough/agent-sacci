<h1 class="page-title">People</h1>
<?php if (!empty($setupRequired)): ?>
    <div class="card notice-card">
        The people directory is available in code, but the seed data is not ready yet. Run migration `004` to enable it.
    </div>
<?php elseif (empty($people)): ?>
    <div class="card">No team directory records are available yet.</div>
<?php else: ?>
    <?php foreach ($people as $person): ?>
        <div class="card">
            <h3 class="card-title"><?= htmlspecialchars($person['name'] ?? 'Unknown') ?></h3>
            <?php if (!empty($person['job_title'])): ?>
                <p><strong>Role:</strong> <?= htmlspecialchars($person['job_title']) ?></p>
            <?php endif; ?>
            <?php if (!empty($person['department_slug'])): ?>
                <p class="meta-text"><strong>Department:</strong> <?= htmlspecialchars($person['department_slug']) ?></p>
            <?php endif; ?>
            <?php if (!empty($person['responsibilities']) && is_array($person['responsibilities'])): ?>
                <p><strong>Responsibilities:</strong></p>
                <ul class="simple-list">
                    <?php foreach ($person['responsibilities'] as $responsibility): ?>
                        <li><?= htmlspecialchars($responsibility) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
