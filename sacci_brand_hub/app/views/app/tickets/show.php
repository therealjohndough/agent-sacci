<h1 style="color: var(--color-accent); margin-bottom:20px;">Ticket #<?= htmlspecialchars($ticket['id']) ?></h1>
<div class="card">
    <h2 style="color: var(--color-primary);"><?= htmlspecialchars($ticket['title']) ?></h2>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($ticket['status']) ?></p>
    <p><strong>Priority:</strong> <?= htmlspecialchars($ticket['priority']) ?></p>
    <p><strong>Due Date:</strong> <?= htmlspecialchars($ticket['due_date']) ?></p>
</div>