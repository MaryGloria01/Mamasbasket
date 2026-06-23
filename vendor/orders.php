<?php
require_once __DIR__ . '/../includes/functions.php';
require_vendor();
$vid = current_vendor_id();
$shopName = $_SESSION['vendor_name'] ?? '';

$orders = [];
try {
    // Orders are snapshots; show those that include this vendor's shop.
    $all = db()->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 200")->fetchAll();
    foreach ($all as $o) {
        $items = json_decode($o['items_json'], true) ?: [];
        $mine = array_filter($items, fn($i) => ($i['vendor'] ?? '') === $shopName);
        if ($mine) { $o['mine'] = array_values($mine); $orders[] = $o; }
    }
} catch (Throwable $e) {}

$dashTitle = 'Orders';
$dashActive = 'orders';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../includes/dash_header.php';
?>
<div class="dash-head"><div><h1>Orders</h1><p>Orders that include your products.</p></div></div>

<div class="table-wrap">
    <div class="th"><h3><?= count($orders) ?> order<?= count($orders) === 1 ? '' : 's' ?></h3></div>
    <?php if ($orders): ?>
    <table class="data">
        <thead><tr><th>Reference</th><th>Buyer</th><th>Your items</th><th>Date</th><th>Receipt</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
            <td><b style="color:var(--green-900)"><?= e($o['reference']) ?></b></td>
            <td><?= e($o['buyer_name']) ?><?= $o['buyer_phone'] ? '<br><span class="hint">' . e($o['buyer_phone']) . '</span>' : '' ?></td>
            <td><?php foreach ($o['mine'] as $i): ?><div><?= (int)$i['qty'] ?> x <?= e($i['name']) ?></div><?php endforeach; ?></td>
            <td><?= e(date('d M Y, H:i', strtotime($o['created_at']))) ?></td>
            <td><?php if ($o['receipt_url']): ?><a class="icon-btn" href="<?= e(asset($o['receipt_url'])) ?>" target="_blank" title="View receipt"><?= icon('image', 'icon', 18) ?></a><?php else: ?>-<?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div style="padding:44px;text-align:center"><p class="hint">No orders with your products yet.</p></div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/dash_footer.php'; ?>
