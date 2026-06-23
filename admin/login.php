<?php
require_once __DIR__ . '/../includes/functions.php';
if (current_admin_id()) { header('Location: /admin/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!csrf_check()) { $error = 'Session expired. Please try again.'; }
    else {
        try {
            $stmt = db()->prepare("SELECT * FROM super_admins WHERE email = ?");
            $stmt->execute([$email]);
            $a = $stmt->fetch();
            if ($a && password_verify($pass, $a['password_hash'])) {
                $_SESSION['admin_id'] = (int)$a['id'];
                $_SESSION['admin_name'] = $a['name'];
                header('Location: /admin/dashboard.php');
                exit;
            } else { $error = 'Wrong email or password.'; }
        } catch (Throwable $e) { $error = 'Something went wrong. Please try again.'; }
    }
}

$authTitle = 'Admin login';
$sideTitle = 'Mama\'s Basket admin';
$sideText  = 'Manage vendors, products, categories and orders from one place.';
$sidePerks = ['Approve new vendors', 'See every order and receipt', 'Curate the storefront'];
require __DIR__ . '/../includes/auth_header.php';
?>
<h1>Admin login</h1>
<p class="sub">Sign in to the control centre.</p>
<?php if ($error): ?><div class="alert alert-err" style="margin-bottom:18px"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="/admin/login.php">
    <?= csrf_field() ?>
    <div class="field"><label>Email</label><input class="input" type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></div>
    <div class="field"><label>Password</label><input class="input" type="password" name="password" required></div>
    <button class="btn btn-green btn-block btn-lg" type="submit">Sign in</button>
</form>
<?php require __DIR__ . '/../includes/auth_footer.php'; ?>
