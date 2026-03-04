<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <h1 class="page-title" style="margin:0">Sales</h1>
    <a href="<?= htmlspecialchars(\Config\appUrl('/sales/new')) ?>" class="button-primary">+ Log Entry</a>
</div>

<?php if (!empty($summary)): ?>
<h2 class="section-title">Last 90 Days — By Product</h2>
<div class="card" style="padding:0;overflow:hidden;margin-bottom:24px">
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="background:var(--surface-2,#1a1a1a)">
                <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">SKU</th>
                <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Product</th>
                <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Format</th>
                <th style="padding:10px 14px;text-align:right;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Units</th>
                <th style="padding:10px 14px;text-align:right;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Revenue</th>
                <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Last Entry</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($summary as $row): ?>
                <tr style="border-top:1px solid rgba(255,255,255,.06)">
                    <td style="padding:10px 14px;font-family:monospace"><?= htmlspecialchars($row['sku']) ?></td>
                    <td style="padding:10px 14px"><?= htmlspecialchars($row['product_name']) ?></td>
                    <td style="padding:10px 14px"><span class="badge"><?= htmlspecialchars($row['format'] ?? '—') ?></span></td>
                    <td style="padding:10px 14px;text-align:right;font-weight:600"><?= number_format((int)$row['total_units']) ?></td>
                    <td style="padding:10px 14px;text-align:right">
                        <?= $row['total_revenue_cents'] !== null
                            ? '$' . number_format((int)$row['total_revenue_cents'] / 100, 2)
                            : '—' ?>
                    </td>
                    <td style="padding:10px 14px;font-size:.85rem;opacity:.7"><?= htmlspecialchars($row['last_entry']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php elseif (empty($recent)): ?>
    <div class="card">No sales entries yet. <a href="<?= htmlspecialchars(\Config\appUrl('/sales/new')) ?>" class="app-link">Log the first entry.</a></div>
<?php endif; ?>

<?php if (!empty($recent)): ?>
<h2 class="section-title">Recent Entries</h2>
<div class="card" style="padding:0;overflow:hidden">
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="background:var(--surface-2,#1a1a1a)">
                <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Date</th>
                <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Product</th>
                <th style="padding:10px 14px;text-align:right;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Units</th>
                <th style="padding:10px 14px;text-align:right;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Revenue</th>
                <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Channel</th>
                <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">By</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent as $row): ?>
                <tr style="border-top:1px solid rgba(255,255,255,.06)">
                    <td style="padding:10px 14px"><?= htmlspecialchars($row['reporting_date']) ?></td>
                    <td style="padding:10px 14px">
                        <?= htmlspecialchars($row['product_name']) ?>
                        <span class="meta-text" style="font-family:monospace;font-size:.8rem"> <?= htmlspecialchars($row['sku']) ?></span>
                    </td>
                    <td style="padding:10px 14px;text-align:right"><?= number_format((int)$row['units_sold']) ?></td>
                    <td style="padding:10px 14px;text-align:right">
                        <?= $row['revenue_cents'] !== null
                            ? '$' . number_format((int)$row['revenue_cents'] / 100, 2)
                            : '—' ?>
                    </td>
                    <td style="padding:10px 14px"><?= htmlspecialchars($row['channel'] ?? '—') ?></td>
                    <td style="padding:10px 14px;opacity:.7"><?= htmlspecialchars($row['recorded_by_name'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
