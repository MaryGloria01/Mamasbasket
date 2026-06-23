<?php
require_once __DIR__ . '/includes/functions.php';
http_response_code(404);
$pageTitle = "Page not found | Mama's Basket";
require __DIR__ . '/includes/header.php';
?>
<section class="section" style="padding-top:160px;text-align:center">
    <div class="container" style="max-width:560px">
        <span class="brand-mark" style="margin:0 auto 20px;width:72px;height:72px"><?= icon('basket', 'icon', 38) ?></span>
        <h1 style="font-size:3rem;color:var(--green-900)">404</h1>
        <h2 style="color:var(--green-800);margin:6px 0 12px">This page is not on the shelf</h2>
        <p class="hint" style="margin-bottom:26px">The page you are looking for may have been moved or removed.</p>
        <div class="hero-cta" style="justify-content:center">
            <a href="/index.php" class="btn btn-green btn-lg"><?= icon('home', 'icon', 20) ?> Back home</a>
            <a href="/shop.php" class="btn btn-ghost btn-lg"><?= icon('basket', 'icon', 20) ?> Go to shop</a>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
