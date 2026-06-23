<?php
require_once __DIR__ . '/../includes/functions.php';
require_vendor();
$vid = current_vendor_id();

$pending = ($_SESSION['vendor_status'] ?? '') === 'pending';

$stats = ['products' => 0, 'in_stock' => 0, 'orders' => 0, 'revenue' => 0];
$recent = [];
try {
    $vendor = db()->prepare("SELECT * FROM vendors WHERE id = ?");
    $vendor->execute([$vid]);
    $vendor = $vendor->fetch();
    $_SESSION['vendor_status'] = $vendor['status'];
    $pending = $vendor['status'] === 'pending';

    $c = db()->prepare("SELECT COUNT(*) c, SUM(in_stock) s FROM products WHERE vendor_id = ?");
    $c->execute([$vid]);
    $row = $c->fetch();
    $stats['products'] = (int)$row['c'];
    $stats['in_stock'] = (int)$row['s'];

    $recent = db()->prepare("SELECT * FROM products WHERE vendor_id = ? ORDER BY created_at DESC LIMIT 5");
    $recent->execute([$vid]);
    $recent = $recent->fetchAll();
} catch (Throwable $e) {}

$dashTitle = 'Overview';
$dashActive = 'overview';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../includes/dash_header.php';
?>
<div class="dash-head">
    <div>
        <h1>Welcome, <?= e($_SESSION['vendor_name'] ?? 'Vendor') ?></h1>
        <p>Here is how your shop is doing today.</p>
    </div>
    <a href="/vendor/products.php?add=1" class="btn btn-green"><?= icon('plus', 'icon', 20) ?> Add product</a>
</div>

<?php if ($pending): ?>
<div class="alert" style="background:#fef3e0;color:var(--orange-600);border:1px solid #f6d2bb;margin-bottom:24px;display:flex;align-items:center;gap:10px">
    <?= icon('clock', 'icon', 20) ?> Your account is pending approval. You can prepare products now; they will go live once the team approves your shop.
</div>
<?php endif; ?>

<div class="stat-grid">
    <div class="stat-card"><span class="si"><?= icon('box', 'icon', 22) ?></span><div class="v"><?= $stats['products'] ?></div><div class="l">Products listed</div></div>
    <div class="stat-card"><span class="si"><?= icon('check', 'icon', 22) ?></span><div class="v"><?= $stats['in_stock'] ?></div><div class="l">In stock</div></div>
    <div class="stat-card"><span class="si"><?= icon('cart', 'icon', 22) ?></span><div class="v"><?= $stats['orders'] ?></div><div class="l">Orders</div></div>
    <div class="stat-card"><span class="si"><?= icon('tag', 'icon', 22) ?></span><div class="v"><?= $pending ? 'Pending' : 'Live' ?></div><div class="l">Shop status</div></div>
</div>

<div class="table-wrap">
    <div class="th"><h3>Recent products</h3><a href="/vendor/products.php" class="btn btn-ghost btn-sm">Manage all</a></div>
    <?php if ($recent): ?>
    <table class="data">
        <thead><tr><th>Product</th><th>Price</th><th>Stock</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($recent as $p): ?>
        <tr>
            <td style="display:flex;align-items:center;gap:12px">
                <?php if ($p['image_url']): ?><img class="t-thumb" src="<?= e(asset($p['image_url'])) ?>" alt=""><?php else: ?><span class="t-thumb" style="display:grid;place-items:center;color:var(--line)"><?= icon('image', 'icon', 20) ?></span><?php endif; ?>
                <b style="color:var(--green-900)"><?= e($p['name']) ?></b>
            </td>
            <td><?= money($p['price']) ?></td>
            <td><?= $p['in_stock'] ? '<span class="badge badge-approved">In stock</span>' : '<span class="badge badge-pending">Sold out</span>' ?></td>
            <td class="t-actions"><a class="icon-btn" href="/vendor/product-edit.php?id=<?= (int)$p['id'] ?>"><?= icon('arrow', 'icon', 18) ?></a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div style="padding:40px;text-align:center"><p class="hint">You have not added any products yet.</p><a href="/vendor/products.php?add=1" class="btn btn-green" style="margin-top:14px"><?= icon('plus', 'icon', 18) ?> Add your first product</a></div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/dash_footer.php'; ?>
