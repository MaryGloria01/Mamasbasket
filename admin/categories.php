<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$ok = ''; $error = '';

$iconChoices = ['grain','beans','spice','drink','leaf','home','snow','snack','box','tag','basket','truck'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { $error = 'Session expired. Please try again.'; }
    else {
        $do = $_POST['do'] ?? '';
        try {
            if ($do === 'add') {
                $name = trim($_POST['name'] ?? '');
                $icon = in_array($_POST['icon'] ?? '', $iconChoices, true) ? $_POST['icon'] : 'box';
                $sort = (int)($_POST['sort_order'] ?? 0);
                if ($name === '') { $error = 'Enter a category name.'; }
                else {
                    db()->prepare("INSERT INTO categories (name, slug, icon, sort_order) VALUES (?, ?, ?, ?)")
                        ->execute([$name, slugify($name), $icon, $sort]);
                    $ok = 'Category added.';
                }
            } elseif ($do === 'delete') {
                db()->prepare("DELETE FROM categories WHERE id=?")->execute([(int)($_POST['id'] ?? 0)]);
                $ok = 'Category removed.';
            }
        } catch (Throwable $e) { $error = 'Action failed. The name may already exist.'; }
    }
}

$categories = [];
try {
    $categories = db()->query(
        "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id) AS product_count
         FROM categories c ORDER BY c.sort_order, c.name"
    )->fetchAll();
} catch (Throwable $e) {}

$dashTitle = 'Categories';
$dashActive = 'categories';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../includes/dash_header.php';
?>
<div class="dash-head"><div><h1>Categories</h1><p>Organise the storefront.</p></div></div>

<?php if ($ok): ?><div class="alert alert-ok" style="margin-bottom:20px"><?= e($ok) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-err" style="margin-bottom:20px"><?= e($error) ?></div><?php endif; ?>

<div class="dash-grid-2">
    <div class="table-wrap">
        <div class="th"><h3><?= count($categories) ?> categories</h3></div>
        <?php if ($categories): ?>
        <table class="data">
            <thead><tr><th>Category</th><th>Products</th><th>Order</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
            <tr>
                <td style="display:flex;align-items:center;gap:10px"><span class="si" style="width:38px;height:38px;border-radius:10px;background:var(--green-050);color:var(--green-700);display:grid;place-items:center"><?= icon($c['icon'] ?: 'box', 'icon', 18) ?></span><b style="color:var(--green-900)"><?= e($c['name']) ?></b></td>
                <td><?= (int)$c['product_count'] ?></td>
                <td><?= (int)$c['sort_order'] ?></td>
                <td><form method="post" action="/admin/categories.php" data-confirm="Delete this category? Products keep existing but lose this category." style="display:inline"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button class="icon-btn danger"><?= icon('trash', 'icon', 18) ?></button></form></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><div style="padding:40px;text-align:center"><p class="hint">No categories yet.</p></div><?php endif; ?>
    </div>

    <div class="pay-step">
        <div class="pay-step-head"><span class="pay-num"><?= icon('plus', 'icon', 18) ?></span><div><h3>Add category</h3><p>Shown on the shop sidebar and home.</p></div></div>
        <form method="post" action="/admin/categories.php">
            <?= csrf_field() ?><input type="hidden" name="do" value="add">
            <div class="field"><label>Name</label><input class="input" name="name" required placeholder="e.g. Bakery"></div>
            <div class="field"><label>Icon</label>
                <select class="select" name="icon">
                    <?php foreach ($iconChoices as $ic): ?><option value="<?= e($ic) ?>"><?= e(ucfirst($ic)) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Sort order</label><input class="input" type="number" name="sort_order" value="0"></div>
            <button class="btn btn-green btn-block" type="submit"><?= icon('check', 'icon', 18) ?> Add category</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/dash_footer.php'; ?>
