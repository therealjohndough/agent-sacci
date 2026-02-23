<h1 style="color: var(--color-accent); margin-bottom:20px;">Dashboard</h1>
<p>Welcome, <?= htmlspecialchars($user['name'] ?? $user['email']) ?>!</p>
<h2 style="color: var(--color-primary); margin-top:30px;">My Tickets</h2>
<?php if (empty($tickets)): ?>
    <div class="card">No tickets assigned to you.</div>
<?php else: ?>
    <?php foreach ($tickets as $t): ?>
        <div class="card">
            <h3 style="margin-top:0; color: var(--color-accent);">
                <a href="/tickets?id=<?= $t['id'] ?>" style="color:var(--color-accent); text-decoration:none;">#<?= $t['id'] ?> - <?= htmlspecialchars($t['title']) ?></a>
            </h3>
            <p><strong>Status:</strong> <?= htmlspecialchars($t['status']) ?> | <strong>Due:</strong> <?= htmlspecialchars($t['due_date']) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>