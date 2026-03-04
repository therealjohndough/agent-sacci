<h1 class="page-title">Compliance — COA Status</h1>

<div class="stat-row" style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:24px">
    <div class="card" style="flex:1;min-width:120px;text-align:center">
        <div style="font-size:2rem;font-weight:700"><?= $total ?></div>
        <div class="meta-text">Active Batches</div>
    </div>
    <div class="card" style="flex:1;min-width:120px;text-align:center;border-left:4px solid #4caf50">
        <div style="font-size:2rem;font-weight:700;color:#4caf50"><?= $approved ?></div>
        <div class="meta-text">COA Approved</div>
    </div>
    <div class="card" style="flex:1;min-width:120px;text-align:center;border-left:4px solid #f44336">
        <div style="font-size:2rem;font-weight:700;color:#f44336"><?= $missing ?></div>
        <div class="meta-text">Missing COA</div>
    </div>
    <div class="card" style="flex:1;min-width:120px;text-align:center;border-left:4px solid #ff9800">
        <div style="font-size:2rem;font-weight:700;color:#ff9800"><?= $flagged ?></div>
        <div class="meta-text">Pending / Expired</div>
    </div>
</div>

<?php if (empty($batches)): ?>
    <div class="card">No active batches found.</div>
<?php else: ?>
    <div class="card" style="padding:0;overflow:hidden">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:var(--surface-2,#1a1a1a)">
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Batch</th>
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Strain</th>
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Status</th>
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">COA</th>
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Lab</th>
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Tested</th>
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">File</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($batches as $b): ?>
                    <?php
                        $coaStatus = $b['coa_status'] ?? null;
                        $coaColor  = match($coaStatus) {
                            'approved' => '#4caf50',
                            'received' => '#2196f3',
                            'pending'  => '#ff9800',
                            'expired'  => '#f44336',
                            'archived' => '#888',
                            default    => '#f44336',
                        };
                        $coaLabel  = $coaStatus ? ucfirst($coaStatus) : 'No COA';
                    ?>
                    <tr style="border-top:1px solid rgba(255,255,255,.06)">
                        <td style="padding:10px 14px">
                            <a href="<?= htmlspecialchars(\Config\appUrl('/batches?id=' . (int)$b['batch_id'])) ?>"
                               class="app-link"><?= htmlspecialchars($b['batch_code']) ?></a>
                        </td>
                        <td style="padding:10px 14px"><?= htmlspecialchars($b['strain_name']) ?></td>
                        <td style="padding:10px 14px">
                            <span class="badge"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $b['production_status']))) ?></span>
                        </td>
                        <td style="padding:10px 14px">
                            <span style="color:<?= $coaColor ?>;font-weight:600"><?= htmlspecialchars($coaLabel) ?></span>
                        </td>
                        <td style="padding:10px 14px"><?= htmlspecialchars($b['lab_name'] ?? '—') ?></td>
                        <td style="padding:10px 14px"><?= htmlspecialchars($b['tested_date'] ?? '—') ?></td>
                        <td style="padding:10px 14px">
                            <?php if (!empty($b['file_path'])): ?>
                                <a href="<?= htmlspecialchars(\Config\appUrl('/batches/coa-upload?id=' . (int)$b['batch_id'])) ?>"
                                   class="app-link">View</a>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars(\Config\appUrl('/batches/coa-upload')) ?>"
                                   class="app-link" style="opacity:.6">Upload</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
