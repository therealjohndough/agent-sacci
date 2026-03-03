<h1 class="page-title"><?= htmlspecialchars($meeting['title']) ?></h1>
<div class="card">
    <p class="meta-text">
        <strong>Type:</strong> <?= htmlspecialchars($meeting['meeting_type']) ?>
        <?php if (!empty($meeting['department_name'])): ?>
            | <strong>Department:</strong> <?= htmlspecialchars($meeting['department_name']) ?>
        <?php endif; ?>
        <?php if (!empty($meeting['scheduled_for'])): ?>
            | <strong>Scheduled:</strong> <?= htmlspecialchars($meeting['scheduled_for']) ?>
        <?php endif; ?>
        <?php if (!empty($meeting['occurred_at'])): ?>
            | <strong>Occurred:</strong> <?= htmlspecialchars($meeting['occurred_at']) ?>
        <?php endif; ?>
    </p>

    <?php if (!empty($meeting['summary'])): ?>
        <p><strong>Summary:</strong> <?= nl2br(htmlspecialchars($meeting['summary'])) ?></p>
    <?php endif; ?>

    <h2 class="section-title">Notes</h2>
    <p><?= nl2br(htmlspecialchars($meeting['notes'])) ?></p>

    <?php if (!empty($meeting['source_url'])): ?>
        <p><strong>Source:</strong> <a href="<?= htmlspecialchars($meeting['source_url']) ?>" class="app-link"><?= htmlspecialchars($meeting['source_url']) ?></a></p>
    <?php endif; ?>
 </div>

<h2 class="section-title">Decisions</h2>
<?php if (empty($decisions)): ?>
    <div class="card">No decisions have been recorded for this meeting.</div>
<?php else: ?>
    <?php foreach ($decisions as $decision): ?>
        <div class="card">
            <p><?= nl2br(htmlspecialchars($decision['decision'])) ?></p>
            <p class="meta-text">
                <?php if (!empty($decision['owner_name'])): ?>
                    <strong>Owner:</strong> <?= htmlspecialchars($decision['owner_name']) ?>
                <?php endif; ?>
                <?php if (!empty($decision['effective_date'])): ?>
                    <?php if (!empty($decision['owner_name'])): ?> | <?php endif; ?>
                    <strong>Effective:</strong> <?= htmlspecialchars($decision['effective_date']) ?>
                <?php endif; ?>
            </p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
