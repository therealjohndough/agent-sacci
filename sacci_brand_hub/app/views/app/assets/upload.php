<p style="margin-bottom:16px">
    <a href="<?= htmlspecialchars(\Config\appUrl('/assets')) ?>" class="app-link">&larr; Asset Library</a>
</p>

<h1 class="page-title">Upload Asset</h1>

<?php if (!empty($error)): ?>
    <div class="card error-card" style="margin-bottom:16px"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width:600px">
    <form method="post" action="<?= htmlspecialchars(\Config\appUrl('/assets/upload')) ?>" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

        <label class="form-label" for="name">Asset Name *</label>
        <input type="text" id="name" name="name" class="form-input" required
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="e.g. Puffin Z Sell Sheet Q1">

        <label class="form-label" style="margin-top:16px" for="category">Category *</label>
        <select id="category" name="category" class="form-input">
            <?php
            $cats = ['sell-sheet', 'social', 'photography', 'packaging', 'logo', 'video', 'document', 'other'];
            $selCat = $_POST['category'] ?? 'other';
            foreach ($cats as $cat):
            ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= ($selCat === $cat) ? 'selected' : '' ?>>
                    <?= htmlspecialchars(ucwords(str_replace('-', ' ', $cat))) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label class="form-label" style="margin-top:16px" for="brand">Brand</label>
        <input type="text" id="brand" name="brand" class="form-input"
               value="<?= htmlspecialchars($_POST['brand'] ?? '') ?>" placeholder="e.g. House of Sacci">

        <label class="form-label" style="margin-top:16px" for="description">Description</label>
        <textarea id="description" name="description" class="form-input form-textarea" placeholder="Brief description of this asset…"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

        <?php if (!empty($products)): ?>
            <label class="form-label" style="margin-top:16px" for="linked_product_id">Linked Product (optional)</label>
            <select id="linked_product_id" name="linked_product_id" class="form-input">
                <option value="">None</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= ((int)($_POST['linked_product_id'] ?? 0) === (int)$p['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['product_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <label class="form-label" style="margin-top:16px" for="visibility">Visibility *</label>
        <select id="visibility" name="visibility" class="form-input">
            <?php
            $visOptions = [
                'internal' => 'Internal (staff only)',
                'public'   => 'Public (retailers + staff)',
                'org'      => 'Org-restricted (retailer org only)',
            ];
            $selVis = $_POST['visibility'] ?? 'internal';
            foreach ($visOptions as $val => $label):
            ?>
                <option value="<?= htmlspecialchars($val) ?>" <?= ($selVis === $val) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label class="form-label" style="margin-top:16px" for="asset_file">File * <span class="meta-text">(max 50 MB)</span></label>
        <input type="file" id="asset_file" name="asset_file" class="form-input" required style="padding:8px">

        <p class="meta-text" style="margin-top:8px;font-size:12px">
            Accepted: JPG, PNG, GIF, WebP, SVG, PDF, MP4, MOV, AVI, ZIP, DOCX, PPTX
        </p>

        <button type="submit" class="button-primary">Upload Asset</button>
    </form>
</div>
