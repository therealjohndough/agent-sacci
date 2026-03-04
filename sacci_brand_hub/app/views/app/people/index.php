<h1 class="page-title">People</h1>

<?php if (empty($people)): ?>
    <div class="card">No active team members found.</div>
<?php else: ?>
    <div class="section-grid">
        <?php foreach ($people as $person): ?>
            <div class="card">
                <h3 class="card-title"><?= htmlspecialchars($person['name'] ?? 'Unknown') ?></h3>
                <?php if (!empty($person['job_title'])): ?>
                    <p><strong>Role:</strong> <?= htmlspecialchars($person['job_title']) ?></p>
                <?php endif; ?>
                <?php if (!empty($person['departments'])): ?>
                    <p class="meta-text"><strong>Department:</strong> <?= htmlspecialchars($person['departments']) ?></p>
                <?php endif; ?>
                <?php if (!empty($person['profile_summary'])): ?>
                    <p><?= nl2br(htmlspecialchars($person['profile_summary'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($person['email'])): ?>
                    <p class="meta-text"><a href="mailto:<?= htmlspecialchars($person['email']) ?>"><?= htmlspecialchars($person['email']) ?></a></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
