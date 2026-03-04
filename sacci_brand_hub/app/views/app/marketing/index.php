<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <h1 class="page-title" style="margin:0">Marketing Requests</h1>
    <a href="<?= htmlspecialchars(\Config\appUrl('/marketing/request')) ?>" class="button-primary" style="text-decoration:none;display:inline-block;margin:0">+ New Request</a>
</div>

<?php
$statusLabels = [
    'open'        => 'Open',
    'in-progress' => 'In Progress',
    'done'        => 'Done',
    'other'       => 'Other',
];
$priorityColors = [
    'urgent' => '#e74c3c',
    'high'   => '#d4a837',
    'normal' => 'var(--color-muted)',
    'low'    => 'var(--color-muted)',
];
$totalCount = array_sum(array_map('count', $grouped));
?>

<?php if ($totalCount === 0): ?>
    <div class="card">No marketing requests yet. <a href="<?= htmlspecialchars(\Config\appUrl('/marketing/request')) ?>" class="app-link">Submit the first one.</a></div>
<?php else: ?>

    <?php foreach (['open', 'in-progress', 'done', 'other'] as $status): ?>
        <?php if (empty($grouped[$status])): continue; endif; ?>
        <h2 class="section-title"><?= htmlspecialchars($statusLabels[$status]) ?> <span class="meta-text" style="font-size:16px;font-weight:400">(<?= count($grouped[$status]) ?>)</span></h2>
        <div class="section-grid" style="margin-bottom:24px">
            <?php foreach ($grouped[$status] as $t): ?>
                <div class="card section-card">
                    <!-- Type + priority badge row -->
                    <p style="margin:0 0 6px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                        <?php if (!empty($t['request_type']) && isset($requestTypes[$t['request_type']])): ?>
                            <span class="badge" style="background:rgba(212,168,55,.15);color:var(--color-accent);border:1px solid rgba(212,168,55,.3)">
                                <?= htmlspecialchars($requestTypes[$t['request_type']]) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($t['priority']) && $t['priority'] !== 'normal'): ?>
                            <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:<?= $priorityColors[$t['priority']] ?? 'inherit' ?>">
                                <?= htmlspecialchars($t['priority']) ?>
                            </span>
                        <?php endif; ?>
                    </p>

                    <h3 class="card-title" style="margin-bottom:4px">
                        <a href="<?= htmlspecialchars(\Config\appUrl('/tickets')) ?>?id=<?= urlencode((string) $t['id']) ?>" class="app-link">
                            <?= htmlspecialchars($t['title']) ?>
                        </a>
                    </h3>

                    <?php if (!empty($t['strain_name']) || !empty($t['product_name'])): ?>
                        <p class="meta-text" style="margin:0 0 6px;font-size:12px">
                            <?php if (!empty($t['strain_name'])): ?>
                                <a href="<?= htmlspecialchars(\Config\appUrl('/strains')) ?>?id=<?= urlencode((string) $t['linked_strain_id']) ?>" class="app-link" style="color:var(--color-muted)">
                                    <?= htmlspecialchars($t['strain_name']) ?>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($t['product_name'])): ?>
                                <?php if (!empty($t['strain_name'])): ?> &middot; <?php endif; ?>
                                <a href="<?= htmlspecialchars(\Config\appUrl('/products')) ?>?id=<?= urlencode((string) $t['linked_product_id']) ?>" class="app-link" style="color:var(--color-muted)">
                                    <?= htmlspecialchars($t['product_name']) ?>
                                </a>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <p class="meta-text" style="margin:0;font-size:12px">
                        <?php if (!empty($t['due_date'])): ?>
                            Due <?= htmlspecialchars($t['due_date']) ?>
                        <?php endif; ?>
                        <?php if (!empty($t['assignee_name'])): ?>
                            <?php if (!empty($t['due_date'])): ?>&middot;<?php endif; ?>
                            <?= htmlspecialchars($t['assignee_name']) ?>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

<?php endif; ?>
