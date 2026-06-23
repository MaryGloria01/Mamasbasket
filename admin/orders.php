<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$ok = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { $error = 'Session expired. Please try again.'; }
    else {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['pending','confirmed','delivered','cancelled'], true)) {
            try { db()->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status, $id]); $ok = 'Order updated.'; }
            catch (Throwable $e) { $error = 'Could not update the order.'; }
        }
    }
}

$orders = [];
try { $orders = db()->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 300")->fetchAll(); } catch (Throwable $e) {}

$dashTitle = 'Orders';
$dashActive = 'orders';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../includes/dash_header.php';
?>
<div class="dash-head"><div><h1>Orders</h1><p>Every order placed through the site.</p></div></div>

<?php if ($ok): ?><div class="alert alert-ok" style="margin-bottom:20px"><?= e($ok) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-err" style="margin-bottom:20px"><?= e($error) ?></div><?php endif; ?>

<div class="table-wrap">
    <div class="th"><h3><?= count($orders) ?> order<?= count($orders) === 1 ? '' : 's' ?></h3></div>
    <?php if ($orders): ?>
    <table class="data">
        <thead><tr><th>Reference</th><th>Buyer</th><th>Items</th><th>Total</th><th>Receipt</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): $items = json_decode($o['items_json'], true) ?: []; ?>
        <tr>
            <td><b style="color:var(--green-900)"><?= e($o['reference']) ?></b><br><span class="hint"><?= e(date('d M Y, H:i', strtotime($o['created_at']))) ?></span></td>
            <td><?= e($o['buyer_name']) ?><?php if ($o['buyer_phone']): ?><br><span class="hint"><?= e($o['buyer_phone']) ?></span><?php endif; ?><?php if ($o['momo_name']): ?><br><span class="hint">MoMo: <?= e($o['momo_name']) ?></span><?php endif; ?></td>
            <td><?php foreach ($items as $i): ?><div><?= (int)($i['qty'] ?? 0) ?> x <?= e($i['name'] ?? '') ?></div><?php endforeach; ?></td>
            <td><?= money($o['total']) ?></td>
            <td><?php if ($o['receipt_url']): ?><a class="icon-btn" href="<?= e(asset($o['receipt_url'])) ?>" target="_blank" title="View receipt"><?= icon('image', 'icon', 18) ?></a><?php else: ?>-<?php endif; ?></td>
            <td>
                <form method="post" action="/admin/orders.php" style="display:flex;gap:6px;align-items:center">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                    <select class="select" name="status" style="padding:8px 10px;font-size:.85rem" onchange="this.form.submit()">
                        <?php foreach (['pending','confirmed','delivered','cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?><div style="padding:44px;text-align:center"><p class="hint">No orders yet.</p></div><?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/dash_footer.php'; ?>
