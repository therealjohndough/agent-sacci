<h1 class="page-title">Content Blocks</h1>
<?php if (empty($blocks)): ?>
    <div class="card">No content blocks defined.</div>
<?php else: ?>
    <?php foreach ($blocks as $block): ?>
        <div class="card">
            <h3 class="card-title">
                <?= htmlspecialchars($block['title']) ?>
            </h3>
            <p><?= htmlspecialchars(substr(strip_tags($block['content']), 0, 100)) ?>...</p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
