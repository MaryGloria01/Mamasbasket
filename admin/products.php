<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/upload.php';
require_admin();
$ok = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { $error = 'Session expired. Please try again.'; }
    else {
        $id = (int)($_POST['id'] ?? 0);
        $do = $_POST['do'] ?? '';
        try {
            if ($do === 'feature')      { db()->prepare("UPDATE products SET featured=1-featured WHERE id=?")->execute([$id]); $ok = 'Updated.'; }
            elseif ($do === 'delete')   {
                $s = db()->prepare("SELECT image_url FROM products WHERE id=?"); $s->execute([$id]); $r = $s->fetch();
                db()->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
                if ($r) delete_image($r['image_url']);
                $ok = 'Product removed.';
            }
        } catch (Throwable $e) { $error = 'Action failed.'; }
    }
}

$q = trim($_GET['q'] ?? '');
$products = [];
try {
    $sql = "SELECT p.*, v.shop_name, c.name AS category_name FROM products p
            JOIN vendors v ON v.id=p.vendor_id LEFT JOIN categories c ON c.id=p.category_id";
    $params = [];
    if ($q !== '') { $sql .= " WHERE p.name LIKE ? OR v.shop_name LIKE ?"; $params = ["%$q%", "%$q%"]; }
    $sql .= " ORDER BY p.created_at DESC LIMIT 300";
    $st = db()->prepare($sql); $st->execute($params); $products = $st->fetchAll();
} catch (Throwable $e) {}

$dashTitle = 'Products';
$dashActive = 'products';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../includes/dash_header.php';
?>
<div class="dash-head">
    <div><h1>All products</h1><p>Every product across all vendors.</p></div>
    <form method="get" action="/admin/products.php" style="display:flex;gap:8px">
        <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Search products or vendors" style="min-width:240px">
        <button class="btn btn-green">Search</button>
    </form>
</div>

<?php if ($ok): ?><div class="alert alert-ok" style="margin-bottom:20px"><?= e($ok) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-err" style="margin-bottom:20px"><?= e($error) ?></div><?php endif; ?>

<div class="table-wrap">
    <div class="th"><h3><?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?></h3></div>
    <?php if ($products): ?>
    <table class="data">
        <thead><tr><th>Product</th><th>Vendor</th><th>Category</th><th>Price</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
            <td style="display:flex;align-items:center;gap:12px">
                <?php if ($p['image_url']): ?><img class="t-thumb" src="<?= e(asset($p['image_url'])) ?>" alt=""><?php else: ?><span class="t-thumb" style="display:grid;place-items:center;color:var(--line)"><?= icon('image', 'icon', 20) ?></span><?php endif; ?>
                <span><b style="color:var(--green-900)"><?= e($p['name']) ?></b><?php if ($p['featured']): ?> <span class="badge badge-approved">Featured</span><?php endif; ?><?php if (!$p['in_stock']): ?> <span class="badge badge-pending">Sold out</span><?php endif; ?></span>
            </td>
            <td><?= e($p['shop_name']) ?></td>
            <td><?= e($p['category_name'] ?: '-') ?></td>
            <td><?= money($p['price']) ?></td>
            <td>
                <div class="t-actions">
                    <form method="post" action="/admin/products.php" style="display:inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="do" value="feature"><button class="btn btn-ghost btn-sm"><?= $p['featured'] ? 'Unfeature' : 'Feature' ?></button></form>
                    <form method="post" action="/admin/products.php" data-confirm="Delete this product?" style="display:inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="do" value="delete"><button class="icon-btn danger"><?= icon('trash', 'icon', 18) ?></button></form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?><div style="padding:44px;text-align:center"><p class="hint">No products found.</p></div><?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/dash_footer.php'; ?>
