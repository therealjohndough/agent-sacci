<h1 class="page-title">Admin — Users &amp; Roles</h1>

<?php if (empty($users)): ?>
    <div class="card">No users found.</div>
<?php else: ?>
    <div class="card" style="padding:0;overflow:hidden">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:var(--surface-2,#1a1a1a)">
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Name</th>
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Email</th>
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Roles</th>
                    <th style="padding:10px 14px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr style="border-top:1px solid rgba(255,255,255,.06)">
                        <td style="padding:10px 14px"><?= htmlspecialchars($u['name'] ?? '—') ?></td>
                        <td style="padding:10px 14px"><?= htmlspecialchars($u['email']) ?></td>
                        <td style="padding:10px 14px">
                            <?php if (!empty($u['role_names'])): ?>
                                <?php foreach (explode(', ', $u['role_names']) as $rn): ?>
                                    <span class="badge" style="margin-right:4px"><?= htmlspecialchars($rn) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="meta-text">No roles</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:10px 14px">
                            <a href="<?= htmlspecialchars(\Config\appUrl('/admin/users/roles?id=' . (int)$u['id'])) ?>"
                               class="app-link">Edit roles</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
