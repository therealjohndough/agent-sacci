<h1 class="page-title"><?= htmlspecialchars($meeting['title']) ?></h1>
<p><a href="<?= htmlspecialchars(\Config\appUrl('/meetings/edit')) ?>?id=<?= urlencode((string) $meeting['id']) ?>" class="app-link">Edit meeting</a></p>
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

<h2 class="section-title">Action Items</h2>
<?php if (empty($actionItems)): ?>
    <div class="card">No action items are linked to this meeting yet.</div>
<?php else: ?>
    <?php foreach ($actionItems as $item): ?>
        <div class="card">
            <h3 class="card-title"><?= htmlspecialchars($item['title']) ?></h3>
            <?php if (!empty($item['details'])): ?>
                <p><?= nl2br(htmlspecialchars($item['details'])) ?></p>
            <?php endif; ?>
            <p class="meta-text">
                <strong>Status:</strong> <?= htmlspecialchars($item['status']) ?>
                | <strong>Priority:</strong> <?= htmlspecialchars($item['priority']) ?>
                <?php if (!empty($item['owner_name'])): ?>
                    | <strong>Owner:</strong> <?= htmlspecialchars($item['owner_name']) ?>
                <?php endif; ?>
                <?php if (!empty($item['due_date'])): ?>
                    | <strong>Due:</strong> <?= htmlspecialchars($item['due_date']) ?>
                <?php endif; ?>
            </p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
