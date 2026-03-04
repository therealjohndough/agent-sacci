<p style="margin-bottom:16px">
    <a href="<?= htmlspecialchars(\Config\appUrl('/campaigns')) ?>" class="app-link">&larr; Campaigns</a>
</p>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <h1 class="page-title" style="margin:0"><?= htmlspecialchars($campaign['name']) ?></h1>
    <a href="<?= htmlspecialchars(\Config\appUrl('/campaigns/edit?id=' . (int)$campaign['id'])) ?>" class="app-link">Edit</a>
</div>

<div class="card" style="margin-bottom:20px">
    <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($campaign['status'])) ?></p>
    <?php if (!empty($campaign['start_date']) || !empty($campaign['end_date'])): ?>
        <p><strong>Dates:</strong> <?= htmlspecialchars($campaign['start_date'] ?? '?') ?> &ndash; <?= htmlspecialchars($campaign['end_date'] ?? 'ongoing') ?></p>
    <?php endif; ?>
    <?php if (!empty($campaign['owner_name'])): ?>
        <p><strong>Owner:</strong> <?= htmlspecialchars($campaign['owner_name']) ?></p>
    <?php endif; ?>
    <?php if (!empty($campaign['description'])): ?>
        <p style="margin-top:12px"><?= nl2br(htmlspecialchars($campaign['description'])) ?></p>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars(\Config\appUrl('/campaigns/archive')) ?>" style="margin-top:16px">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int)$campaign['id'] ?>">
        <button type="submit" class="app-link" style="background:none;border:none;cursor:pointer;color:#f44336"
                onclick="return confirm('Archive this campaign?')">Archive</button>
    </form>
</div>

<h2 class="section-title">Linked Marketing Requests (<?= count($tickets) ?>)</h2>
<p class="meta-text" style="margin-bottom:12px">
    To link requests: open a marketing request ticket and note the campaign. (Ticket linking UI coming soon.)
</p>

<?php if (empty($tickets)): ?>
    <div class="card">No requests linked to this campaign yet.</div>
<?php else: ?>
    <div class="card" style="padding:0;overflow:hidden">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:var(--surface-2,#1a1a1a)">
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Request</th>
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Type</th>
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Priority</th>
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Status</th>
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Due</th>
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Assignee</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $t): ?>
                    <tr style="border-top:1px solid rgba(255,255,255,.06)">
                        <td style="padding:10px 14px">
                            <a href="<?= htmlspecialchars(\Config\appUrl('/tickets?id=' . (int)$t['id'])) ?>" class="app-link">
                                <?= htmlspecialchars($t['title']) ?>
                            </a>
                        </td>
                        <td style="padding:10px 14px"><span class="badge"><?= htmlspecialchars($t['request_type'] ?? '—') ?></span></td>
                        <td style="padding:10px 14px"><?= htmlspecialchars(ucfirst($t['priority'] ?? '—')) ?></td>
                        <td style="padding:10px 14px"><?= htmlspecialchars($t['status'] ?? '—') ?></td>
                        <td style="padding:10px 14px"><?= htmlspecialchars($t['due_date'] ?? '—') ?></td>
                        <td style="padding:10px 14px"><?= htmlspecialchars($t['assignee_name'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
