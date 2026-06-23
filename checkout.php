<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Payment | Mama's Basket";
$active = '';
$error = '';

$cart = ['lines' => [], 'total' => 0];
try { $cart = cart_detailed(); } catch (Throwable $e) {}

// Empty cart: send back to basket.
if (!$cart['lines'] && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /cart.php');
    exit;
}

/* ----------------------------------------------------------- Handle order */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart = cart_detailed();
    $name  = trim($_POST['buyer_name'] ?? '');
    $phone = trim($_POST['buyer_phone'] ?? '');
    $momo  = trim($_POST['momo_name'] ?? '');

    if (!csrf_check())                 { $error = 'Your session expired. Please try again.'; }
    elseif ($name === '')              { $error = 'Please enter your name.'; }
    elseif (!$cart['lines'])           { $error = 'Your basket is empty.'; }
    elseif (empty($_FILES['receipt']['name'])) { $error = 'Please upload your payment receipt or screenshot.'; }
    else {
        // ---- Validate and store the receipt ----
        $f = $_FILES['receipt'];
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $f['tmp_name']);
        finfo_close($finfo);

        if ($f['error'] !== UPLOAD_ERR_OK)        { $error = 'Upload failed. Please try again.'; }
        elseif ($f['size'] > 6 * 1024 * 1024)     { $error = 'The receipt is too large (max 6 MB).'; }
        elseif (!isset($allowed[$mime]))          { $error = 'Please upload a JPG, PNG or WEBP image.'; }
        else {
            $ext  = $allowed[$mime];
            $ref  = 'MB-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $fname = $ref . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destRel = 'uploads/receipts/' . $fname;
            $destAbs = __DIR__ . '/' . $destRel;

            if (!move_uploaded_file($f['tmp_name'], $destAbs)) {
                $error = 'Could not save the receipt. Please try again.';
            } else {
                $receiptUrl = $destRel;

                // ---- Store the order ----
                $itemsSnap = array_map(function ($l) {
                    return ['name' => $l['name'], 'vendor' => $l['shop_name'], 'qty' => (int)$l['qty'], 'price' => (float)$l['price']];
                }, $cart['lines']);

                try {
                    $stmt = db()->prepare(
                        "INSERT INTO orders (reference, buyer_name, buyer_phone, momo_name, items_json, total, receipt_url)
                         VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([$ref, $name, $phone, $momo, json_encode($itemsSnap), $cart['total'], $receiptUrl]);
                } catch (Throwable $e) { /* still proceed to WhatsApp even if logging fails */ }

                // ---- Build the WhatsApp message ----
                $lines = ["New order " . $ref . " from " . $name];
                if ($phone) $lines[] = "Phone: " . $phone;
                $lines[] = "";
                foreach ($cart['lines'] as $l) {
                    $lines[] = $l['qty'] . " x " . $l['name'] . " (" . money($l['subtotal']) . ")";
                }
                $lines[] = "";
                $lines[] = "Total: " . money($cart['total']);
                if ($momo) $lines[] = "Paid with MoMo name: " . $momo;
                $lines[] = "Receipt: " . BASE_URL . '/' . $receiptUrl;
                $message = implode("\n", $lines);

                cart_clear();
                header('Location: ' . whatsapp_link($message));
                exit;
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top:120px">
    <div class="container">
        <div class="section-head" style="margin-bottom:26px"><span class="eyebrow">Checkout</span><h2>Complete your payment</h2></div>

        <?php if ($error): ?><div class="alert alert-err" style="margin-bottom:22px"><?= e($error) ?></div><?php endif; ?>

        <form class="checkout-layout" method="post" action="/checkout.php" enctype="multipart/form-data" id="payForm">
            <?= csrf_field() ?>
            <div>
                <!-- Step 1: details -->
                <div class="pay-step">
                    <div class="pay-step-head">
                        <span class="pay-num">1</span>
                        <div><h3>Your details</h3><p>So we know who the order is for.</p></div>
                    </div>
                    <div class="field">
                        <label for="buyer_name">Full name</label>
                        <input class="input" type="text" id="buyer_name" name="buyer_name" required value="<?= e($_POST['buyer_name'] ?? '') ?>" placeholder="e.g. Aline Uwase">
                    </div>
                    <div class="field" style="margin-bottom:0">
                        <label for="buyer_phone">Phone number <span style="color:var(--muted);font-weight:400">(optional)</span></label>
                        <input class="input" type="tel" id="buyer_phone" name="buyer_phone" value="<?= e($_POST['buyer_phone'] ?? '') ?>" placeholder="e.g. 07XX XXX XXX">
                    </div>
                </div>

                <!-- Step 2: pay with MoMo -->
                <div class="pay-step">
                    <div class="pay-step-head">
                        <span class="pay-num">2</span>
                        <div><h3>Pay with Mobile Money</h3><p>Send the exact total, then come back to upload your receipt.</p></div>
                    </div>
                    <div class="momo-box">
                        <div class="momo-row">
                            <div>
                                <div class="momo-label">MoMo Code</div>
                                <div class="momo-value" id="momoCode"><?= e(MOMO_CODE) ?></div>
                            </div>
                            <button type="button" class="copy-btn" data-copy="<?= e(MOMO_CODE) ?>">
                                <?= icon('copy', 'icon', 18) ?><span class="copy-label">Copy</span>
                            </button>
                        </div>
                        <div class="momo-row">
                            <div>
                                <div class="momo-label">MoMo Name</div>
                                <div class="momo-name"><?= e(MOMO_NAME) ?></div>
                            </div>
                            <div style="text-align:right">
                                <div class="momo-label">Amount</div>
                                <div class="momo-name"><?= money($cart['total']) ?></div>
                            </div>
                        </div>
                    </div>
                    <p class="hint" style="margin-top:12px">Dial your Mobile Money menu, pay to the code above, and keep the confirmation message.</p>
                </div>

                <!-- Step 3: upload receipt -->
                <div class="pay-step">
                    <div class="pay-step-head">
                        <span class="pay-num">3</span>
                        <div><h3>Upload your receipt</h3><p>A screenshot or photo of your payment confirmation.</p></div>
                    </div>
                    <label class="upload-zone" id="uploadZone">
                        <span class="ui"><?= icon('upload', 'icon', 26) ?></span>
                        <b>Tap to upload receipt</b>
                        <span>JPG, PNG or WEBP, up to 6 MB</span>
                        <input type="file" name="receipt" id="receiptInput" accept="image/png,image/jpeg,image/webp" required hidden>
                    </label>
                    <div class="upload-preview" id="uploadPreview">
                        <img id="previewImg" alt="Receipt preview">
                        <div><b>Ready to send</b><div class="pn" id="previewName"></div></div>
                    </div>
                </div>

                <button type="submit" class="btn btn-green btn-lg btn-block" id="submitBtn">
                    <?= icon('whatsapp', 'icon', 20) ?> Send order on WhatsApp
                </button>
                <p class="hint" style="text-align:center;margin-top:12px">Your details and receipt link will be sent to our team on WhatsApp to confirm delivery.</p>
            </div>

            <!-- Order summary -->
            <aside class="pay-step order-summary">
                <div class="pay-step-head" style="margin-bottom:14px">
                    <span class="pay-num" style="background:var(--orange)"><?= icon('basket', 'icon', 18) ?></span>
                    <div><h3>Order summary</h3><p><?= count($cart['lines']) ?> item<?= count($cart['lines']) === 1 ? '' : 's' ?></p></div>
                </div>
                <?php foreach ($cart['lines'] as $l): ?>
                <div class="os-line">
                    <span><span class="q"><?= (int)$l['qty'] ?> x</span> <?= e($l['name']) ?></span>
                    <b><?= money($l['subtotal']) ?></b>
                </div>
                <?php endforeach; ?>
                <div class="cs-divider"></div>
                <div class="cs-row cs-total"><span>Total</span><b><?= money($cart['total']) ?></b></div>
                <a href="/cart.php" class="btn btn-ghost btn-block btn-sm" style="margin-top:16px">Edit basket</a>
            </aside>
        </form>
    </div>
</section>

<script>
(function () {
    var input = document.getElementById("receiptInput");
    var zone = document.getElementById("uploadZone");
    var prev = document.getElementById("uploadPreview");
    var img = document.getElementById("previewImg");
    var nm = document.getElementById("previewName");
    var form = document.getElementById("payForm");
    var submit = document.getElementById("submitBtn");

    input.addEventListener("change", function () {
        var f = input.files && input.files[0];
        if (!f) return;
        nm.textContent = f.name;
        img.src = URL.createObjectURL(f);
        prev.classList.add("show");
        zone.querySelector("b").textContent = "Change receipt";
    });

    form.addEventListener("submit", function () {
        submit.disabled = true;
        submit.innerHTML = "Opening WhatsApp...";
    });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
