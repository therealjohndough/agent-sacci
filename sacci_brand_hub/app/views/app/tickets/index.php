<h1 style="color: var(--color-accent); margin-bottom:20px;">Tickets</h1>
<?php if (empty($tickets)): ?>
    <div class="card">No tickets found.</div>
<?php else: ?>
    <?php foreach ($tickets as $ticket): ?>
        <div class="card">
            <h3 style="margin-top:0; color: var(--color-accent);">
                <a href="/tickets?id=<?= $ticket['id'] ?>" style="color:var(--color-accent); text-decoration:none;">#<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['title']) ?></a>
            </h3>
            <p><?= htmlspecialchars($ticket['description']) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>