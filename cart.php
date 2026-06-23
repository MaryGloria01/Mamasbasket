<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Your Basket | Mama's Basket";
$active = '';
$cart = ['lines' => [], 'total' => 0];
try { $cart = cart_detailed(); } catch (Throwable $e) {}

require __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top:120px">
    <div class="container">
        <div class="section-head" style="margin-bottom:28px"><span class="eyebrow">Your basket</span><h2>Review your order</h2></div>

        <?php if ($cart['lines']): ?>
        <div class="cart-layout">
            <div class="cart-lines panel">
                <?php foreach ($cart['lines'] as $l): ?>
                <div class="cart-line" data-id="<?= (int)$l['id'] ?>">
                    <div class="cl-thumb">
                        <?php if ($l['image_url']): ?><img src="<?= e(asset($l['image_url'])) ?>" alt="<?= e($l['name']) ?>"><?php else: ?><span class="ph"><?= icon('image', 'icon', 30) ?></span><?php endif; ?>
                    </div>
                    <div class="cl-info">
                        <b><?= e($l['name']) ?></b>
                        <span class="product-vendor"><?= icon('tag', 'icon', 12) ?> <?= e($l['shop_name']) ?></span>
                        <span class="cl-unit"><?= money($l['price']) ?> <?php if ($l['unit']): ?><small><?= e($l['unit']) ?></small><?php endif; ?></span>
                    </div>
                    <div class="qty cl-qty">
                        <button type="button" data-cart="set" data-id="<?= (int)$l['id'] ?>" data-qty="<?= (int)$l['qty'] - 1 ?>" aria-label="Decrease"><?= icon('minus', 'icon', 16) ?></button>
                        <span><?= (int)$l['qty'] ?></span>
                        <button type="button" data-cart="set" data-id="<?= (int)$l['id'] ?>" data-qty="<?= (int)$l['qty'] + 1 ?>" aria-label="Increase"><?= icon('plus', 'icon', 16) ?></button>
                    </div>
                    <div class="cl-sub"><?= money($l['subtotal']) ?></div>
                    <button class="cl-remove" data-cart="remove" data-id="<?= (int)$l['id'] ?>" aria-label="Remove"><?= icon('trash', 'icon', 18) ?></button>
                </div>
                <?php endforeach; ?>
            </div>

            <aside class="cart-summary panel">
                <h3>Order summary</h3>
                <div class="cs-row"><span>Subtotal</span><b><?= money($cart['total']) ?></b></div>
                <div class="cs-row"><span>Delivery</span><span class="text-green">Calculated on checkout</span></div>
                <div class="cs-divider"></div>
                <div class="cs-row cs-total"><span>Total</span><b><?= money($cart['total']) ?></b></div>
                <a href="/checkout.php" class="btn btn-green btn-block btn-lg" style="margin-top:18px"><?= icon('arrow', 'icon', 20) ?> Proceed to payment</a>
                <a href="/shop.php" class="btn btn-ghost btn-block" style="margin-top:10px">Continue shopping</a>
                <p class="hint" style="margin-top:14px">You will confirm your order and send your payment receipt on WhatsApp.</p>
            </aside>
        </div>
        <?php else: ?>
        <div class="panel" style="padding:64px;text-align:center">
            <span class="brand-mark" style="margin:0 auto 18px;width:64px;height:64px"><?= icon('cart', 'icon', 32) ?></span>
            <h3 style="color:var(--green-900);margin-bottom:8px">Your basket is empty</h3>
            <p class="hint" style="margin-bottom:20px">Add some fresh items to get started.</p>
            <a href="/shop.php" class="btn btn-green btn-lg"><?= icon('basket', 'icon', 20) ?> Start shopping</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener("click", function (e) {
    var b = e.target.closest("[data-cart]"); if (!b) return;
    var body = new URLSearchParams({ action: b.getAttribute("data-cart"), id: b.getAttribute("data-id"), qty: b.getAttribute("data-qty") || "0" });
    b.disabled = true;
    fetch("/api/cart.php", { method: "POST", body: body })
        .then(function (r) { return r.json(); })
        .then(function () { window.location.reload(); })
        .catch(function () { b.disabled = false; });
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
