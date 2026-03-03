<h1 class="page-title">Tickets</h1>
<?php if (empty($tickets)): ?>
    <div class="card">No tickets found.</div>
<?php else: ?>
    <?php foreach ($tickets as $ticket): ?>
        <div class="card">
            <h3 class="card-title">
                <a href="<?= htmlspecialchars(\Config\appUrl('/tickets')) ?>?id=<?= urlencode((string) $ticket['id']) ?>" class="app-link">#<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['title']) ?></a>
            </h3>
            <p><?= htmlspecialchars($ticket['description']) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
