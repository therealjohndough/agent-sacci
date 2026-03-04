<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <h1 class="page-title" style="margin:0">Campaigns</h1>
    <a href="<?= htmlspecialchars(\Config\appUrl('/campaigns/new')) ?>" class="button-primary">+ New Campaign</a>
</div>

<?php if (empty($campaigns)): ?>
    <div class="card">No active campaigns. <a href="<?= htmlspecialchars(\Config\appUrl('/campaigns/new')) ?>" class="app-link">Create one.</a></div>
<?php else: ?>
    <?php
    $statusColor = [
        'active'    => '#4caf50',
        'draft'     => '#888',
        'completed' => '#2196f3',
        'archived'  => '#555',
    ];
    ?>
    <div class="section-grid">
        <?php foreach ($campaigns as $c): ?>
            <div class="card">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                    <span style="width:8px;height:8px;border-radius:50%;background:<?= $statusColor[$c['status']] ?? '#888' ?>;flex-shrink:0"></span>
                    <h3 class="card-title" style="margin:0">
                        <a href="<?= htmlspecialchars(\Config\appUrl('/campaigns?id=' . (int)$c['id'])) ?>" class="app-link">
                            <?= htmlspecialchars($c['name']) ?>
                        </a>
                    </h3>
                </div>
                <p class="meta-text"><?= htmlspecialchars(ucfirst($c['status'])) ?> &middot; <?= (int)$c['ticket_count'] ?> request(s)</p>
                <?php if (!empty($c['start_date']) || !empty($c['end_date'])): ?>
                    <p class="meta-text">
                        <?= htmlspecialchars($c['start_date'] ?? '?') ?> &ndash; <?= htmlspecialchars($c['end_date'] ?? 'ongoing') ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($c['owner_name'])): ?>
                    <p class="meta-text">Owner: <?= htmlspecialchars($c['owner_name']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
