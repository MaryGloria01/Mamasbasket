<?php
require_once __DIR__ . '/../includes/functions.php';

if (current_vendor_id()) { header('Location: /vendor/dashboard.php'); exit; }

$error = '';
$done  = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shop  = trim($_POST['shop_name'] ?? '');
    $owner = trim($_POST['owner_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!csrf_check())                      { $error = 'Session expired. Please try again.'; }
    elseif ($shop === '' || $owner === '' || $email === '' || $phone === '') { $error = 'Please fill in all fields.'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Please enter a valid email.'; }
    elseif (strlen($pass) < 8)              { $error = 'Password must be at least 8 characters.'; }
    else {
        try {
            $exists = db()->prepare("SELECT id FROM vendors WHERE email = ?");
            $exists->execute([$email]);
            if ($exists->fetch()) { $error = 'An account with this email already exists.'; }
            else {
                $stmt = db()->prepare(
                    "INSERT INTO vendors (shop_name, owner_name, email, phone, password_hash, status)
                     VALUES (?, ?, ?, ?, ?, 'pending')"
                );
                $stmt->execute([$shop, $owner, $email, $phone, password_hash($pass, PASSWORD_DEFAULT)]);
                $done = true;
            }
        } catch (Throwable $e) { $error = 'Something went wrong. Please try again.'; }
    }
}

$authTitle = 'Become a vendor';
$sideTitle = 'Grow your business with us';
$sideText  = 'Join Mama\'s Basket and put your products in front of shoppers all over Kigali.';
$sidePerks = ['Free to join', 'Simple product management', 'We handle shopping and delivery'];
require __DIR__ . '/../includes/auth_header.php';
?>
<?php if ($done): ?>
    <span class="brand-mark" style="width:60px;height:60px;margin-bottom:18px;background:linear-gradient(150deg,var(--green-600),var(--green-800))"><?= icon('check', 'icon', 30) ?></span>
    <h1>Application received</h1>
    <p class="sub">Thank you for signing up. Your vendor account is pending approval by the Mama's Basket team. You will be able to sign in once it is approved.</p>
    <a href="/vendor/login.php" class="btn btn-green btn-block btn-lg">Go to login</a>
<?php else: ?>
    <h1>Become a vendor</h1>
    <p class="sub">Create your shop account. We will review and approve it shortly.</p>
    <?php if ($error): ?><div class="alert alert-err" style="margin-bottom:18px"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="/vendor/register.php">
        <?= csrf_field() ?>
        <div class="field">
            <label for="shop_name">Shop name</label>
            <input class="input" type="text" id="shop_name" name="shop_name" required value="<?= e($_POST['shop_name'] ?? '') ?>" placeholder="e.g. Green Garden Foods">
        </div>
        <div class="form-grid-2">
            <div class="field">
                <label for="owner_name">Your name</label>
                <input class="input" type="text" id="owner_name" name="owner_name" required value="<?= e($_POST['owner_name'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="phone">Phone</label>
                <input class="input" type="tel" id="phone" name="phone" required value="<?= e($_POST['phone'] ?? '') ?>">
            </div>
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input class="input" type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input class="input" type="password" id="password" name="password" required placeholder="At least 8 characters">
        </div>
        <button class="btn btn-green btn-block btn-lg" type="submit">Create account</button>
    </form>
    <p class="auth-foot">Already a vendor? <a href="/vendor/login.php">Sign in</a></p>
<?php endif; ?>
<?php require __DIR__ . '/../includes/auth_footer.php'; ?>
