<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$ok = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { $error = 'Session expired. Please try again.'; }
    else {
        $id = (int)($_POST['id'] ?? 0);
        $do = $_POST['do'] ?? '';
        try {
            if ($do === 'approve')      { db()->prepare("UPDATE vendors SET status='approved' WHERE id=?")->execute([$id]); $ok = 'Vendor approved.'; }
            elseif ($do === 'suspend')  { db()->prepare("UPDATE vendors SET status='suspended' WHERE id=?")->execute([$id]); $ok = 'Vendor suspended.'; }
            elseif ($do === 'reinstate'){ db()->prepare("UPDATE vendors SET status='approved' WHERE id=?")->execute([$id]); $ok = 'Vendor reinstated.'; }
            elseif ($do === 'delete')   { db()->prepare("DELETE FROM vendors WHERE id=?")->execute([$id]); $ok = 'Vendor removed.'; }
        } catch (Throwable $e) { $error = 'Action failed.'; }
    }
}

$vendors = [];
try {
    $vendors = db()->query(
        "SELECT v.*, (SELECT COUNT(*) FROM products p WHERE p.vendor_id=v.id) AS product_count
         FROM vendors v ORDER BY FIELD(v.status,'pending','approved','suspended'), v.created_at DESC"
    )->fetchAll();
} catch (Throwable $e) {}

$dashTitle = 'Vendors';
$dashActive = 'vendors';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../includes/dash_header.php';

function vendor_action($id, $do, $label, $class, $confirm = '') {
    $c = $confirm ? ' data-confirm="' . e($confirm) . '"' : '';
    return '<form method="post" action="/admin/vendors.php"' . $c . ' style="display:inline">' . csrf_field()
        . '<input type="hidden" name="id" value="' . (int)$id . '"><input type="hidden" name="do" value="' . e($do) . '">'
        . '<button class="btn ' . e($class) . ' btn-sm">' . e($label) . '</button></form>';
}
?>
<div class="dash-head"><div><h1>Vendors</h1><p>Approve new shops and manage existing ones.</p></div></div>

<?php if ($ok): ?><div class="alert alert-ok" style="margin-bottom:20px"><?= e($ok) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-err" style="margin-bottom:20px"><?= e($error) ?></div><?php endif; ?>

<div class="table-wrap">
    <div class="th"><h3><?= count($vendors) ?> vendor<?= count($vendors) === 1 ? '' : 's' ?></h3></div>
    <?php if ($vendors): ?>
    <table class="data">
        <thead><tr><th>Shop</th><th>Owner / contact</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($vendors as $v): ?>
        <tr>
            <td><b style="color:var(--green-900)"><?= e($v['shop_name']) ?></b><br><span class="hint"><?= e(date('d M Y', strtotime($v['created_at']))) ?></span></td>
            <td><?= e($v['owner_name']) ?><br><span class="hint"><?= e($v['email']) ?> &middot; <?= e($v['phone']) ?></span></td>
            <td><?= (int)$v['product_count'] ?></td>
            <td><span class="badge badge-<?= e($v['status']) ?>"><?= e(ucfirst($v['status'])) ?></span></td>
            <td>
                <div class="t-actions" style="flex-wrap:wrap;gap:6px">
                    <?php
                    if ($v['status'] === 'pending')   { echo vendor_action($v['id'], 'approve', 'Approve', 'btn-green'); }
                    if ($v['status'] === 'approved')  { echo vendor_action($v['id'], 'suspend', 'Suspend', 'btn-ghost'); }
                    if ($v['status'] === 'suspended') { echo vendor_action($v['id'], 'reinstate', 'Reinstate', 'btn-green'); }
                    echo vendor_action($v['id'], 'delete', 'Delete', 'btn-ghost', 'Delete this vendor and all their products?');
                    ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?><div style="padding:44px;text-align:center"><p class="hint">No vendors yet.</p></div><?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/dash_footer.php'; ?>
