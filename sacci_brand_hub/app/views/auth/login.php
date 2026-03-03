<?php
use Core\Csrf;
?>
<h1 class="page-title">Login</h1>
<?php if (!empty($error)): ?>
    <div class="card error-card">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>
<form method="post" action="<?= htmlspecialchars(\Config\appUrl('/login')) ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <div class="card">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" required class="form-input">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password" required class="form-input">
        <button type="submit" class="button-primary">Login</button>
    </div>
</form>
