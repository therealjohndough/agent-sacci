<?php
use Core\Csrf;
?>
<h1 style="color: var(--color-accent); margin-bottom:20px;">Login</h1>
<?php if (!empty($error)): ?>
    <div class="card" style="border-left:3px solid #c0392b; color:#e74c3c;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>
<form method="post" action="/login">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <div class="card">
        <label for="email">Email</label><br>
        <input type="email" id="email" name="email" required style="width:100%;padding:8px;">
        <br><br>
        <label for="password">Password</label><br>
        <input type="password" id="password" name="password" required style="width:100%;padding:8px;">
        <br><br>
        <button type="submit" style="padding:10px 20px; background:var(--color-accent); color:var(--color-bg); border:none; cursor:pointer;">Login</button>
    </div>
</form>