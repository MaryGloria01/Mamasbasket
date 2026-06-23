<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/upload.php';
require_vendor();
$vid = current_vendor_id();
$id  = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$error = ''; $ok = '';
$categories = [];
$product = null;
try {
    $categories = db()->query("SELECT * FROM categories ORDER BY sort_order, name")->fetchAll();
    $stmt = db()->prepare("SELECT * FROM products WHERE id = ? AND vendor_id = ?");
    $stmt->execute([$id, $vid]);
    $product = $stmt->fetch();
} catch (Throwable $e) {}

if (!$product) {
    require __DIR__ . '/_nav.php';
    $dashTitle = 'Edit product'; $dashActive = 'products';
    require __DIR__ . '/../includes/dash_header.php';
    echo '<div class="dash-head"><h1>Product not found</h1></div><a href="/vendor/products.php" class="btn btn-green">Back to products</a>';
    require __DIR__ . '/../includes/dash_footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $cat   = (int)($_POST['category_id'] ?? 0) ?: null;
    $unit  = trim($_POST['unit'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $stock = isset($_POST['in_stock']) ? 1 : 0;
    $feat  = isset($_POST['featured']) ? 1 : 0;

    if (!csrf_check())    { $error = 'Session expired. Please try again.'; }
    elseif ($name === '') { $error = 'Please enter a product name.'; }
    elseif ($price <= 0)  { $error = 'Please enter a valid price.'; }
    else {
        $img = $product['image_url'];
        $imgErr = null;
        $newImg = save_image('image', 'products', $imgErr, false); // optional on edit
        if ($imgErr) { $error = $imgErr; }
        else {
            if ($newImg) { delete_image($product['image_url']); $img = $newImg; }
            try {
                db()->prepare(
                    "UPDATE products SET category_id=?, name=?, description=?, price=?, unit=?, image_url=?, in_stock=?, featured=?
                     WHERE id=? AND vendor_id=?"
                )->execute([$cat, $name, $desc, $price, $unit, $img, $stock, $feat, $id, $vid]);
                $ok = 'Product updated.';
                $stmt = db()->prepare("SELECT * FROM products WHERE id = ? AND vendor_id = ?");
                $stmt->execute([$id, $vid]);
                $product = $stmt->fetch();
            } catch (Throwable $e) { $error = 'Could not update the product.'; }
        }
    }
}

$dashTitle = 'Edit product';
$dashActive = 'products';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../includes/dash_header.php';
?>
<div class="dash-head">
    <div><h1>Edit product</h1><p><?= e($product['name']) ?></p></div>
    <a href="/vendor/products.php" class="btn btn-ghost"><?= icon('arrow', 'icon', 18) ?> Back</a>
</div>

<?php if ($ok): ?><div class="alert alert-ok" style="margin-bottom:20px"><?= e($ok) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-err" style="margin-bottom:20px"><?= e($error) ?></div><?php endif; ?>

<div class="pay-step" style="max-width:640px">
    <form method="post" action="/vendor/product-edit.php?id=<?= (int)$id ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$id ?>">
        <div class="field">
            <label for="name">Product name</label>
            <input class="input" type="text" id="name" name="name" required value="<?= e($product['name']) ?>">
        </div>
        <div class="form-grid-2">
            <div class="field">
                <label for="price">Price (<?= CURRENCY ?>)</label>
                <input class="input" type="number" id="price" name="price" min="1" step="1" required value="<?= e((string)(int)$product['price']) ?>">
            </div>
            <div class="field">
                <label for="unit">Unit</label>
                <input class="input" type="text" id="unit" name="unit" value="<?= e($product['unit'] ?? '') ?>">
            </div>
        </div>
        <div class="field">
            <label for="category_id">Category</label>
            <select class="select" id="category_id" name="category_id">
                <option value="">Choose a category</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= ($product['category_id'] == $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="description">Description</label>
            <textarea class="textarea" id="description" name="description"><?= e($product['description'] ?? '') ?></textarea>
        </div>
        <div class="field">
            <label>Product image</label>
            <label class="img-drop" id="imgDrop">
                <?php if ($product['image_url']): ?><img src="<?= e(asset($product['image_url'])) ?>" alt=""><?php else: ?><b>Tap to upload</b><?php endif; ?>
                <div class="hint" style="margin-top:8px">Upload a new image to replace it (optional)</div>
                <input type="file" name="image" accept="image/png,image/jpeg,image/webp" data-preview="imgDrop" hidden>
            </label>
        </div>
        <div style="display:flex;gap:18px;margin:6px 0 16px">
            <label style="display:flex;align-items:center;gap:8px;font-size:.92rem"><input type="checkbox" name="in_stock" <?= $product['in_stock'] ? 'checked' : '' ?>> In stock</label>
            <label style="display:flex;align-items:center;gap:8px;font-size:.92rem"><input type="checkbox" name="featured" <?= $product['featured'] ? 'checked' : '' ?>> Feature on home</label>
        </div>
        <button class="btn btn-green btn-block btn-lg" type="submit"><?= icon('check', 'icon', 20) ?> Save changes</button>
    </form>
</div>

<?php require __DIR__ . '/../includes/dash_footer.php'; ?>
