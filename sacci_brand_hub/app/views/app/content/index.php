<h1 style="color: var(--color-accent); margin-bottom:20px;">Content Blocks</h1>
<?php if (empty($blocks)): ?>
    <div class="card">No content blocks defined.</div>
<?php else: ?>
    <?php foreach ($blocks as $block): ?>
        <div class="card">
            <h3 style="margin-top:0; color: var(--color-accent);">
                <?= htmlspecialchars($block['title']) ?>
            </h3>
            <p><?= htmlspecialchars(substr(strip_tags($block['content']), 0, 100)) ?>...</p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>