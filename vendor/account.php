<?php
require_once __DIR__ . '/../includes/functions.php';
require_vendor();
$vid = current_vendor_id();
$error = ''; $ok = '';

$v = null;
try {
    $stmt = db()->prepare("SELECT * FROM vendors WHERE id = ?");
    $stmt->execute([$vid]);
    $v = $stmt->fetch();
} catch (Throwable $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $v) {
    if (!csrf_check()) { $error = 'Session expired. Please try again.'; }
    elseif (($_POST['form'] ?? '') === 'profile') {
        $shop  = trim($_POST['shop_name'] ?? '');
        $owner = trim($_POST['owner_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if ($shop === '' || $owner === '' || $phone === '') { $error = 'Please fill in all fields.'; }
        else {
            try {
                db()->prepare("UPDATE vendors SET shop_name=?, owner_name=?, phone=? WHERE id=?")
                    ->execute([$shop, $owner, $phone, $vid]);
                $_SESSION['vendor_name'] = $shop;
                $ok = 'Profile updated.';
                $v['shop_name'] = $shop; $v['owner_name'] = $owner; $v['phone'] = $phone;
            } catch (Throwable $e) { $error = 'Could not update profile.'; }
        }
    } elseif (($_POST['form'] ?? '') === 'password') {
        $cur = $_POST['current'] ?? '';
        $new = $_POST['new'] ?? '';
        if (!password_verify($cur, $v['password_hash'])) { $error = 'Your current password is wrong.'; }
        elseif (strlen($new) < 8) { $error = 'New password must be at least 8 characters.'; }
        else {
            try {
                db()->prepare("UPDATE vendors SET password_hash=? WHERE id=?")
                    ->execute([password_hash($new, PASSWORD_DEFAULT), $vid]);
                $ok = 'Password changed.';
            } catch (Throwable $e) { $error = 'Could not change password.'; }
        }
    }
}

$dashTitle = 'Account';
$dashActive = 'account';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../includes/dash_header.php';
?>
<div class="dash-head"><div><h1>Account</h1><p>Manage your shop details and password.</p></div></div>

<?php if ($ok): ?><div class="alert alert-ok" style="margin-bottom:20px"><?= e($ok) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-err" style="margin-bottom:20px"><?= e($error) ?></div><?php endif; ?>

<div class="dash-grid-2">
    <div class="pay-step">
        <div class="pay-step-head"><span class="pay-num"><?= icon('user', 'icon', 18) ?></span><div><h3>Shop profile</h3><p>Status: <?= e(ucfirst($v['status'] ?? 'pending')) ?></p></div></div>
        <form method="post" action="/vendor/account.php">
            <?= csrf_field() ?><input type="hidden" name="form" value="profile">
            <div class="field"><label>Shop name</label><input class="input" name="shop_name" required value="<?= e($v['shop_name'] ?? '') ?>"></div>
            <div class="field"><label>Owner name</label><input class="input" name="owner_name" required value="<?= e($v['owner_name'] ?? '') ?>"></div>
            <div class="field"><label>Phone</label><input class="input" name="phone" required value="<?= e($v['phone'] ?? '') ?>"></div>
            <div class="field"><label>Email</label><input class="input" value="<?= e($v['email'] ?? '') ?>" disabled></div>
            <button class="btn btn-green btn-block" type="submit">Save profile</button>
        </form>
    </div>
    <div class="pay-step">
        <div class="pay-step-head"><span class="pay-num"><?= icon('shield', 'icon', 18) ?></span><div><h3>Password</h3><p>Keep your account secure.</p></div></div>
        <form method="post" action="/vendor/account.php">
            <?= csrf_field() ?><input type="hidden" name="form" value="password">
            <div class="field"><label>Current password</label><input class="input" type="password" name="current" required></div>
            <div class="field"><label>New password</label><input class="input" type="password" name="new" required placeholder="At least 8 characters"></div>
            <button class="btn btn-green btn-block" type="submit">Change password</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/dash_footer.php'; ?>
