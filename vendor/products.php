<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/upload.php';
require_vendor();
$vid = current_vendor_id();

$error = ''; $ok = '';
$showAdd = isset($_GET['add']);

$categories = [];
try { $categories = db()->query("SELECT * FROM categories ORDER BY sort_order, name")->fetchAll(); } catch (Throwable $e) {}

/* ---- Create product ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $showAdd = true;
    $name  = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $cat   = (int)($_POST['category_id'] ?? 0) ?: null;
    $unit  = trim($_POST['unit'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $stock = isset($_POST['in_stock']) ? 1 : 0;
    $feat  = isset($_POST['featured']) ? 1 : 0;

    if (!csrf_check())        { $error = 'Session expired. Please try again.'; }
    elseif ($name === '')     { $error = 'Please enter a product name.'; }
    elseif ($price <= 0)      { $error = 'Please enter a valid price.'; }
    else {
        $imgErr = null;
        $img = save_image('image', 'products', $imgErr, true);
        if (!$img) { $error = $imgErr ?: 'Please add a product image.'; }
        else {
            try {
                $stmt = db()->prepare(
                    "INSERT INTO products (vendor_id, category_id, name, description, price, unit, image_url, in_stock, featured)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$vid, $cat, $name, $desc, $price, $unit, $img, $stock, $feat]);
                $ok = 'Product added successfully.';
                $showAdd = false;
                $_POST = [];
            } catch (Throwable $e) { $error = 'Could not save the product. Please try again.'; }
        }
    }
}

/* ---- List ---- */
$products = [];
try {
    $stmt = db()->prepare(
        "SELECT p.*, c.name AS category_name FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.vendor_id = ? ORDER BY p.created_at DESC"
    );
    $stmt->execute([$vid]);
    $products = $stmt->fetchAll();
} catch (Throwable $e) {}

$dashTitle = 'Products';
$dashActive = 'products';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../includes/dash_header.php';
?>
<div class="dash-head">
    <div><h1>Products</h1><p>Add and manage the goods shoppers can buy.</p></div>
    <a href="/vendor/products.php<?= $showAdd ? '' : '?add=1' ?>" class="btn btn-green"><?= icon('plus', 'icon', 20) ?> Add product</a>
</div>

<?php if ($ok): ?><div class="alert alert-ok" style="margin-bottom:20px"><?= e($ok) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-err" style="margin-bottom:20px"><?= e($error) ?></div><?php endif; ?>

<div class="dash-grid-2">
    <div class="table-wrap">
        <div class="th"><h3>Your products (<?= count($products) ?>)</h3></div>
        <?php if ($products): ?>
        <table class="data">
            <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td style="display:flex;align-items:center;gap:12px">
                    <?php if ($p['image_url']): ?><img class="t-thumb" src="<?= e(asset($p['image_url'])) ?>" alt=""><?php else: ?><span class="t-thumb" style="display:grid;place-items:center;color:var(--line)"><?= icon('image', 'icon', 20) ?></span><?php endif; ?>
                    <b style="color:var(--green-900)"><?= e($p['name']) ?></b>
                </td>
                <td><?= e($p['category_name'] ?: '-') ?></td>
                <td><?= money($p['price']) ?></td>
                <td><?= $p['in_stock'] ? '<span class="badge badge-approved">In stock</span>' : '<span class="badge badge-pending">Sold out</span>' ?></td>
                <td>
                    <div class="t-actions">
                        <a class="icon-btn" href="/vendor/product-edit.php?id=<?= (int)$p['id'] ?>" title="Edit"><?= icon('arrow', 'icon', 18) ?></a>
                        <form method="post" action="/vendor/product-delete.php" data-confirm="Delete this product? This cannot be undone." style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button class="icon-btn danger" title="Delete"><?= icon('trash', 'icon', 18) ?></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="padding:44px;text-align:center"><p class="hint">No products yet. Use the form to add your first item.</p></div>
        <?php endif; ?>
    </div>

    <div class="pay-step" style="<?= $showAdd ? '' : 'opacity:.55' ?>">
        <div class="pay-step-head"><span class="pay-num"><?= icon('plus', 'icon', 18) ?></span><div><h3>Add a product</h3><p>It appears on the shop once you are approved.</p></div></div>
        <form method="post" action="/vendor/products.php" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="field">
                <label for="name">Product name</label>
                <input class="input" type="text" id="name" name="name" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="e.g. Long grain rice 5kg">
            </div>
            <div class="form-grid-2">
                <div class="field">
                    <label for="price">Price (<?= CURRENCY ?>)</label>
                    <input class="input" type="number" id="price" name="price" min="1" step="1" required value="<?= e($_POST['price'] ?? '') ?>" placeholder="5000">
                </div>
                <div class="field">
                    <label for="unit">Unit <span style="color:var(--muted);font-weight:400">(optional)</span></label>
                    <input class="input" type="text" id="unit" name="unit" value="<?= e($_POST['unit'] ?? '') ?>" placeholder="per bag">
                </div>
            </div>
            <div class="field">
                <label for="category_id">Category</label>
                <select class="select" id="category_id" name="category_id">
                    <option value="">Choose a category</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= (($_POST['category_id'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="description">Description <span style="color:var(--muted);font-weight:400">(optional)</span></label>
                <textarea class="textarea" id="description" name="description" placeholder="Short description shoppers will see"><?= e($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="field">
                <label>Product image</label>
                <label class="img-drop" id="imgDrop">
                    <span class="ui" style="margin:0 auto 8px;display:grid;place-items:center;width:46px;height:46px;border-radius:12px;background:var(--green-050);color:var(--green-700)"><?= icon('image', 'icon', 22) ?></span>
                    <b style="font-family:var(--font-head);color:var(--green-900)">Tap to upload</b>
                    <div class="hint">JPG, PNG or WEBP, up to 2 MB</div>
                    <input type="file" name="image" accept="image/png,image/jpeg,image/webp" data-preview="imgDrop" required hidden>
                </label>
            </div>
            <div style="display:flex;gap:18px;margin:6px 0 16px">
                <label style="display:flex;align-items:center;gap:8px;font-size:.92rem"><input type="checkbox" name="in_stock" checked> In stock</label>
                <label style="display:flex;align-items:center;gap:8px;font-size:.92rem"><input type="checkbox" name="featured"> Feature on home</label>
            </div>
            <button class="btn btn-green btn-block btn-lg" type="submit"><?= icon('check', 'icon', 20) ?> Save product</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/dash_footer.php'; ?>
