<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$product = null;
$related = [];
try {
    $stmt = db()->prepare(
        "SELECT p.*, v.shop_name, c.name AS category_name, c.slug AS category_slug
         FROM products p
         JOIN vendors v ON v.id = p.vendor_id AND v.status='approved'
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.id = ?"
    );
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if ($product) {
        $rel = db()->prepare(
            "SELECT p.*, v.shop_name FROM products p
             JOIN vendors v ON v.id = p.vendor_id AND v.status='approved'
             WHERE p.category_id = ? AND p.id <> ? AND p.in_stock = 1
             ORDER BY RAND() LIMIT 4"
        );
        $rel->execute([$product['category_id'], $id]);
        $related = $rel->fetchAll();
    }
} catch (Throwable $e) { /* fallthrough */ }

if (!$product) {
    http_response_code(404);
    $pageTitle = "Product not found | Mama's Basket";
    require __DIR__ . '/includes/header.php';
    echo '<section class="section" style="padding-top:140px"><div class="container panel" style="padding:56px;text-align:center">'
        . '<h2 style="color:var(--green-900)">Product not found</h2><p class="hint" style="margin:10px 0 20px">It may have been removed.</p>'
        . '<a href="/shop.php" class="btn btn-green">Back to shop</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = e($product['name']) . " | Mama's Basket";
$active = 'shop';
require __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top:120px">
    <div class="container">
        <nav class="crumbs"><a href="/shop.php">Shop</a> <?= icon('arrow', 'icon', 14) ?>
            <?php if ($product['category_slug']): ?><a href="/shop.php?cat=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a> <?= icon('arrow', 'icon', 14) ?><?php endif; ?>
            <span><?= e($product['name']) ?></span>
        </nav>

        <div class="product-detail">
            <div class="pd-media">
                <?php if ($product['image_url']): ?>
                    <img src="<?= e(asset($product['image_url'])) ?>" alt="<?= e($product['name']) ?>">
                <?php else: ?><span class="ph"><?= icon('image', 'icon', 90) ?></span><?php endif; ?>
            </div>
            <div class="pd-info">
                <span class="product-vendor"><?= icon('tag', 'icon', 14) ?> <?= e($product['shop_name']) ?><?php if ($product['category_name']): ?> &middot; <?= e($product['category_name']) ?><?php endif; ?></span>
                <h1><?= e($product['name']) ?></h1>
                <div class="pd-price"><?= money($product['price']) ?> <?php if ($product['unit']): ?><small><?= e($product['unit']) ?></small><?php endif; ?></div>
                <?php if ($product['description']): ?><p class="pd-desc"><?= nl2br(e($product['description'])) ?></p><?php endif; ?>

                <?php if ($product['in_stock']): ?>
                <div class="pd-actions">
                    <div class="qty" id="qty">
                        <button type="button" data-step="-1" aria-label="Decrease"><?= icon('minus', 'icon', 18) ?></button>
                        <span id="qtyVal">1</span>
                        <button type="button" data-step="1" aria-label="Increase"><?= icon('plus', 'icon', 18) ?></button>
                    </div>
                    <button class="btn btn-green btn-lg" id="pdAdd" data-id="<?= (int)$product['id'] ?>"><?= icon('cart', 'icon', 20) ?> Add to basket</button>
                </div>
                <a href="/cart.php" class="pd-cartlink">Go to basket <?= icon('arrow', 'icon', 16) ?></a>
                <?php else: ?>
                <div class="alert alert-err" style="margin-top:14px">This item is currently sold out.</div>
                <?php endif; ?>

                <ul class="pd-perks">
                    <li><?= icon('truck', 'icon', 18) ?> Fast delivery across Kigali</li>
                    <li><?= icon('shield', 'icon', 18) ?> Pay with Mobile Money</li>
                    <li><?= icon('leaf', 'icon', 18) ?> Hand picked and checked</li>
                </ul>
            </div>
        </div>

        <?php if ($related): ?>
        <div style="margin-top:72px">
            <div class="section-head" style="margin-bottom:28px"><span class="eyebrow">You may also like</span><h2 style="font-size:1.8rem">Related products</h2></div>
            <div class="product-grid">
                <?php foreach ($related as $p): ?>
                <article class="product-card">
                    <a class="product-thumb" href="/product.php?id=<?= (int)$p['id'] ?>">
                        <?php if ($p['image_url']): ?><img src="<?= e(asset($p['image_url'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy"><?php else: ?><span class="ph"><?= icon('image', 'icon', 54) ?></span><?php endif; ?>
                    </a>
                    <div class="product-body">
                        <span class="product-vendor"><?= icon('tag', 'icon', 13) ?> <?= e($p['shop_name']) ?></span>
                        <a class="product-name" href="/product.php?id=<?= (int)$p['id'] ?>"><?= e($p['name']) ?></a>
                        <div class="product-foot">
                            <span class="product-price"><?= money($p['price']) ?></span>
                            <button class="add-btn" data-add="<?= (int)$p['id'] ?>" aria-label="Add to cart"><?= icon('plus', 'icon', 20) ?></button>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
(function () {
    var val = document.getElementById("qtyVal");
    var qty = document.getElementById("qty");
    var add = document.getElementById("pdAdd");
    if (!qty) return;
    var n = 1;
    qty.addEventListener("click", function (e) {
        var b = e.target.closest("[data-step]"); if (!b) return;
        n = Math.max(1, n + parseInt(b.getAttribute("data-step"), 10));
        val.textContent = n;
    });
    add.addEventListener("click", function () {
        add.disabled = true;
        var body = new URLSearchParams({ action: "add", id: add.getAttribute("data-id"), qty: String(n) });
        fetch("/api/cart.php", { method: "POST", body: body })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                var badge = document.getElementById("cartBadge");
                if (badge && d.count != null) badge.textContent = d.count;
                add.innerHTML = '<span style="display:inline-flex;gap:8px;align-items:center">Added to basket</span>';
                setTimeout(function () { window.location.href = "/cart.php"; }, 550);
            })
            .catch(function () { add.disabled = false; });
    });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
