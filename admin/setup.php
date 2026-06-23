<?php
/**
 * One-time setup: creates the first super admin if none exists.
 * After an admin exists this page locks itself. Delete it once set up.
 */
require_once __DIR__ . '/../includes/functions.php';

$error = ''; $done = false; $hasAdmin = false;
try {
    $hasAdmin = (int)db()->query("SELECT COUNT(*) FROM super_admins")->fetchColumn() > 0;
} catch (Throwable $e) { $error = 'Database not reachable. Import sql/schema.sql first.'; }

if (!$hasAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!csrf_check())        { $error = 'Session expired. Please try again.'; }
    elseif ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
        $error = 'Enter a name, valid email and a password of at least 8 characters.';
    } else {
        try {
            db()->prepare("INSERT INTO super_admins (name, email, password_hash) VALUES (?, ?, ?)")
                ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            $done = true;
        } catch (Throwable $e) { $error = 'Could not create the admin account.'; }
    }
}

$authTitle = 'Admin setup';
$sideTitle = 'Set up Mama\'s Basket';
$sideText  = 'Create the first administrator account to manage vendors, products and orders.';
$sidePerks = ['Approve and manage vendors', 'Oversee every product and order', 'Manage shop categories'];
require __DIR__ . '/../includes/auth_header.php';
?>
<?php if ($hasAdmin): ?>
    <h1>Setup complete</h1>
    <p class="sub">An administrator already exists. For security, delete <code>admin/setup.php</code> from the server.</p>
    <a href="/admin/login.php" class="btn btn-green btn-block btn-lg">Go to admin login</a>
<?php elseif ($done): ?>
    <span class="brand-mark" style="width:60px;height:60px;margin-bottom:18px"><?= icon('check', 'icon', 30) ?></span>
    <h1>Admin created</h1>
    <p class="sub">You can now sign in. Remember to delete <code>admin/setup.php</code> from the server.</p>
    <a href="/admin/login.php" class="btn btn-green btn-block btn-lg">Go to admin login</a>
<?php else: ?>
    <h1>Create admin</h1>
    <p class="sub">This page works only while no admin exists.</p>
    <?php if ($error): ?><div class="alert alert-err" style="margin-bottom:18px"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="/admin/setup.php">
        <?= csrf_field() ?>
        <div class="field"><label>Name</label><input class="input" name="name" required value="<?= e($_POST['name'] ?? '') ?>"></div>
        <div class="field"><label>Email</label><input class="input" type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></div>
        <div class="field"><label>Password</label><input class="input" type="password" name="password" required placeholder="At least 8 characters"></div>
        <button class="btn btn-green btn-block btn-lg" type="submit">Create admin account</button>
    </form>
<?php endif; ?>
<?php require __DIR__ . '/../includes/auth_footer.php'; ?>
