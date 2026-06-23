<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$stats = ['vendors' => 0, 'pending' => 0, 'products' => 0, 'orders' => 0, 'revenue' => 0];
$recentOrders = []; $pendingVendors = [];
try {
    $stats['vendors']  = (int)db()->query("SELECT COUNT(*) FROM vendors WHERE status='approved'")->fetchColumn();
    $stats['pending']  = (int)db()->query("SELECT COUNT(*) FROM vendors WHERE status='pending'")->fetchColumn();
    $stats['products'] = (int)db()->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $stats['orders']   = (int)db()->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stats['revenue']  = (float)db()->query("SELECT COALESCE(SUM(total),0) FROM orders")->fetchColumn();
    $recentOrders = db()->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 6")->fetchAll();
    $pendingVendors = db()->query("SELECT * FROM vendors WHERE status='pending' ORDER BY created_at DESC LIMIT 6")->fetchAll();
} catch (Throwable $e) {}

$dashTitle = 'Overview';
$dashActive = 'overview';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../includes/dash_header.php';
?>
<div class="dash-head">
    <div><h1>Overview</h1><p>Welcome back, <?= e($_SESSION['admin_name'] ?? 'Admin') ?>.</p></div>
    <a href="/admin/vendors.php" class="btn btn-green"><?= icon('user', 'icon', 20) ?> Manage vendors</a>
</div>

<div class="stat-grid">
    <div class="stat-card"><span class="si"><?= icon('user', 'icon', 22) ?></span><div class="v"><?= $stats['vendors'] ?></div><div class="l">Active vendors</div></div>
    <div class="stat-card"><span class="si"><?= icon('box', 'icon', 22) ?></span><div class="v"><?= $stats['products'] ?></div><div class="l">Products</div></div>
    <div class="stat-card"><span class="si"><?= icon('cart', 'icon', 22) ?></span><div class="v"><?= $stats['orders'] ?></div><div class="l">Orders</div></div>
    <div class="stat-card"><span class="si"><?= icon('tag', 'icon', 22) ?></span><div class="v" style="font-size:1.3rem"><?= money($stats['revenue']) ?></div><div class="l">Order value</div></div>
</div>

<div class="dash-grid-2">
    <div class="table-wrap">
        <div class="th"><h3>Recent orders</h3><a href="/admin/orders.php" class="btn btn-ghost btn-sm">View all</a></div>
        <?php if ($recentOrders): ?>
        <table class="data">
            <thead><tr><th>Reference</th><th>Buyer</th><th>Total</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($recentOrders as $o): ?>
            <tr><td><b style="color:var(--green-900)"><?= e($o['reference']) ?></b></td><td><?= e($o['buyer_name']) ?></td><td><?= money($o['total']) ?></td><td><?= e(date('d M, H:i', strtotime($o['created_at']))) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><div style="padding:36px;text-align:center"><p class="hint">No orders yet.</p></div><?php endif; ?>
    </div>

    <div class="table-wrap">
        <div class="th"><h3>Pending vendors <?php if ($stats['pending']): ?><span class="badge badge-pending"><?= $stats['pending'] ?></span><?php endif; ?></h3><a href="/admin/vendors.php" class="btn btn-ghost btn-sm">Review</a></div>
        <?php if ($pendingVendors): ?>
        <table class="data">
            <thead><tr><th>Shop</th><th>Owner</th></tr></thead>
            <tbody>
            <?php foreach ($pendingVendors as $v): ?>
            <tr><td><b style="color:var(--green-900)"><?= e($v['shop_name']) ?></b><br><span class="hint"><?= e($v['email']) ?></span></td><td><?= e($v['owner_name']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><div style="padding:36px;text-align:center"><p class="hint">No vendors waiting for approval.</p></div><?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../includes/dash_footer.php'; ?>
