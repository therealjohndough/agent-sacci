<p style="margin-bottom:16px">
    <a href="<?= htmlspecialchars(\Config\appUrl('/admin/users')) ?>" class="app-link">&larr; Users</a>
</p>

<h1 class="page-title">Edit Roles — <?= htmlspecialchars($userData['name'] ?? $userData['email']) ?></h1>
<p class="meta-text" style="margin-bottom:20px"><?= htmlspecialchars($userData['email']) ?></p>

<div class="card" style="max-width:480px">
    <form method="post" action="<?= htmlspecialchars(\Config\appUrl('/admin/users/roles')) ?>">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="user_id" value="<?= (int)$userData['id'] ?>">

        <?php if (empty($allRoles)): ?>
            <p class="meta-text">No roles defined. Run migration 002 to seed roles.</p>
        <?php else: ?>
            <p style="margin-bottom:14px"><strong>Assign roles:</strong></p>
            <?php foreach ($allRoles as $role): ?>
                <label style="display:flex;align-items:flex-start;gap:10px;margin-bottom:12px;cursor:pointer">
                    <input type="checkbox" name="roles[]" value="<?= (int)$role['id'] ?>"
                           <?= in_array((int)$role['id'], $currentRoleIds, true) ? 'checked' : '' ?>
                           style="margin-top:3px">
                    <span>
                        <strong><?= htmlspecialchars($role['name']) ?></strong>
                        <?php if (!empty($role['description'])): ?>
                            <br><span class="meta-text"><?= htmlspecialchars($role['description']) ?></span>
                        <?php endif; ?>
                    </span>
                </label>
            <?php endforeach; ?>
        <?php endif; ?>

        <button type="submit" class="button-primary" style="margin-top:8px">Save Roles</button>
    </form>
</div>
