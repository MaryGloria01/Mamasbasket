<?php
require_once __DIR__ . '/../includes/functions.php';

if (current_vendor_id()) { header('Location: /vendor/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!csrf_check()) { $error = 'Session expired. Please try again.'; }
    else {
        try {
            $stmt = db()->prepare("SELECT * FROM vendors WHERE email = ?");
            $stmt->execute([$email]);
            $v = $stmt->fetch();
            if ($v && password_verify($pass, $v['password_hash'])) {
                if ($v['status'] === 'suspended') {
                    $error = 'Your account is suspended. Please contact the Mama\'s Basket team.';
                } else {
                    $_SESSION['vendor_id'] = (int)$v['id'];
                    $_SESSION['vendor_name'] = $v['shop_name'];
                    $_SESSION['vendor_status'] = $v['status'];
                    header('Location: /vendor/dashboard.php');
                    exit;
                }
            } else { $error = 'Wrong email or password.'; }
        } catch (Throwable $e) { $error = 'Something went wrong. Please try again.'; }
    }
}

$authTitle = 'Vendor login';
$sideTitle = 'Sell with Mama\'s Basket';
$sideText  = 'Reach thousands of shoppers across Kigali. List your products, manage your stock and grow your business.';
$sidePerks = ['Your own vendor dashboard', 'Add products with photos in seconds', 'Orders delivered by our riders'];
require __DIR__ . '/../includes/auth_header.php';
?>
<h1>Vendor login</h1>
<p class="sub">Welcome back. Sign in to manage your shop.</p>
<?php if ($error): ?><div class="alert alert-err" style="margin-bottom:18px"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="/vendor/login.php">
    <?= csrf_field() ?>
    <div class="field">
        <label for="email">Email</label>
        <input class="input" type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@example.com">
    </div>
    <div class="field">
        <label for="password">Password</label>
        <input class="input" type="password" id="password" name="password" required placeholder="Your password">
    </div>
    <button class="btn btn-green btn-block btn-lg" type="submit">Sign in</button>
</form>
<p class="auth-foot">New vendor? <a href="/vendor/register.php">Create an account</a></p>
<?php require __DIR__ . '/../includes/auth_footer.php'; ?>
