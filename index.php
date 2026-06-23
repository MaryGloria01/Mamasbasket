<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Mama's Basket | You Order, We Shop, We Pack, We Deliver";
$active = 'home';
$pageScripts = ['/js/hero3d.js', '/js/home.js'];

/* Pull live data (defensive: site still renders if DB is empty/offline) */
$categories = [];
$featured = [];
try {
    $categories = db()->query("SELECT * FROM categories ORDER BY sort_order, name LIMIT 8")->fetchAll();
    $stmt = db()->query(
        "SELECT p.*, v.shop_name, c.name AS category_name
         FROM products p
         JOIN vendors v ON v.id = p.vendor_id AND v.status='approved'
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.featured = 1 AND p.in_stock = 1
         ORDER BY p.created_at DESC LIMIT 8"
    );
    $featured = $stmt->fetchAll();
} catch (Throwable $e) { /* show static fallback below */ }

require __DIR__ . '/includes/header.php';
?>

<!-- ===================================================== HERO -->
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="hero-badge"><?= icon('shield', 'icon', 18) ?> Premium shopping &amp; delivery service</span>
            <h1>
                <span class="l1">You Order.</span><br>
                <span class="l2">We Shop. We Pack.</span><br>
                <span class="l3">We Deliver.</span>
            </h1>
            <p class="hero-sub">Groceries, fresh produce, drinks, household essentials and more. Shopped, packed and delivered fast across Kigali, while you relax.</p>
            <div class="hero-cta">
                <a href="/shop.php" class="btn btn-green btn-lg"><?= icon('basket', 'icon', 20) ?> Start Shopping</a>
                <a href="<?= e(whatsapp_link('Hello Mama\'s Basket, I would like to place an order.')) ?>" class="btn btn-ghost btn-lg" target="_blank" rel="noopener"><?= icon('whatsapp', 'icon', 20) ?> Order on WhatsApp</a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat"><div class="num"><span data-count="15">0</span>min</div><div class="lbl">Average delivery</div></div>
                <div class="hero-stat"><div class="num"><span data-count="2500" data-suffix="+">0</span></div><div class="lbl">Orders delivered</div></div>
                <div class="hero-stat"><div class="num"><span data-count="4.8">0</span></div><div class="lbl">Customer rating</div></div>
            </div>
        </div>

        <div class="hero-stage">
            <div class="glow"></div>
            <canvas class="hero-canvas" id="heroCanvas" aria-hidden="true"></canvas>
            <div class="hero-fallback" id="heroFallback">
                <span class="brand-mark" style="width:120px;height:120px;border-radius:34px"><?= icon('basket', 'icon', 70) ?></span>
            </div>
            <div class="float-card fc-1">
                <span class="dot" style="background:linear-gradient(150deg,var(--green-600),var(--green-800))"><?= icon('truck', 'icon', 20) ?></span>
                <span><span class="t">On the way</span><span class="s">Arriving in under 15 min</span></span>
            </div>
            <div class="float-card fc-2">
                <span class="dot" style="background:linear-gradient(150deg,var(--gold),var(--orange))"><?= icon('leaf', 'icon', 20) ?></span>
                <span><span class="t">Fresh &amp; quality</span><span class="s">Hand picked for you</span></span>
            </div>
        </div>
    </div>
</section>

<!-- ===================================================== TRUST BAR -->
<section class="trustbar">
    <div class="container">
        <div class="trust-item"><?= icon('clock') ?><div><b>Under 15 minutes</b><span>Fast delivery across Kigali</span></div></div>
        <div class="trust-item"><?= icon('shield') ?><div><b>100% Reliable</b><span>Safe, secure and on time</span></div></div>
        <div class="trust-item"><?= icon('basket') ?><div><b>Anything you need</b><span>Groceries, food, essentials</span></div></div>
        <div class="trust-item"><?= icon('headset') ?><div><b>7 days support</b><span>We are here for you</span></div></div>
    </div>
</section>

<!-- ===================================================== PROCESS -->
<section class="section process" id="process">
    <div class="container">
        <div class="section-head center reveal">
            <span class="eyebrow">Our process</span>
            <h2>Four simple steps to your door</h2>
            <p>From your order to your doorstep, we handle every step with care.</p>
        </div>
        <div class="steps">
            <?php
            $stepData = [
                ['box',   'You Order',   'Send your list on WhatsApp or build a cart on our shop.'],
                ['basket','We Shop',     'Our local shoppers hand pick fresh, quality items.'],
                ['tag',   'We Pack',     'Everything is carefully packed and checked for you.'],
                ['truck', 'We Deliver',  'Fast, safe delivery to your home anywhere in Kigali.'],
            ];
            foreach ($stepData as $i => $s): ?>
            <div class="step reveal" data-delay="<?= $i * 90 ?>">
                <span class="step-no"><?= $i + 1 ?></span>
                <span class="step-ico"><?= icon($s[0], 'icon', 28) ?></span>
                <h3><?= e($s[1]) ?></h3>
                <p><?= e($s[2]) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================================================== CATEGORIES -->
<section class="section" id="categories">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Browse</span>
            <h2>Shop by category</h2>
            <p>Everything for your home and kitchen, all in one basket.</p>
        </div>
        <div class="cat-grid">
            <?php if ($categories): foreach ($categories as $i => $c): ?>
            <a href="/shop.php?cat=<?= e($c['slug']) ?>" class="cat-card reveal" data-delay="<?= ($i % 4) * 70 ?>">
                <span class="ci"><?= icon($c['icon'] ?: 'box', 'icon', 26) ?></span>
                <span><b><?= e($c['name']) ?></b><br><span>Shop now</span></span>
            </a>
            <?php endforeach; else: ?>
            <p class="hint">Categories will appear here once the database is connected.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ===================================================== FEATURED PRODUCTS -->
<section class="section" style="background:var(--cream-2)">
    <div class="container">
        <div class="section-head reveal" style="display:flex;justify-content:space-between;align-items:flex-end;max-width:none">
            <div>
                <span class="eyebrow">Fresh picks</span>
                <h2>Featured products</h2>
            </div>
            <a href="/shop.php" class="btn btn-ghost">View all <?= icon('arrow', 'icon', 18) ?></a>
        </div>
        <?php if ($featured): ?>
        <div class="product-grid">
            <?php foreach ($featured as $i => $p): ?>
            <article class="product-card reveal" data-delay="<?= ($i % 4) * 70 ?>">
                <div class="product-thumb">
                    <?php if ($p['image_url']): ?>
                        <img src="<?= e(asset($p['image_url'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                    <?php else: ?><span class="ph"><?= icon('image', 'icon', 54) ?></span><?php endif; ?>
                    <?php if ($p['category_name']): ?><span class="product-tag"><?= e($p['category_name']) ?></span><?php endif; ?>
                </div>
                <div class="product-body">
                    <span class="product-vendor"><?= icon('tag', 'icon', 13) ?> <?= e($p['shop_name']) ?></span>
                    <span class="product-name"><?= e($p['name']) ?></span>
                    <div class="product-foot">
                        <span class="product-price"><?= money($p['price']) ?> <?php if ($p['unit']): ?><small><?= e($p['unit']) ?></small><?php endif; ?></span>
                        <button class="add-btn" data-add="<?= (int)$p['id'] ?>" aria-label="Add to cart"><?= icon('plus', 'icon', 20) ?></button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="panel" style="padding:48px;text-align:center">
            <span class="brand-mark" style="margin:0 auto 16px"><?= icon('basket') ?></span>
            <h3 style="color:var(--green-900);margin-bottom:8px">Products are on the way</h3>
            <p class="hint">Approved vendors will list their products here. Vendors can sign in to add goods.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===================================================== WHY US -->
<section class="section why" id="why">
    <div class="container">
        <div class="section-head center reveal">
            <span class="eyebrow" style="color:var(--gold)">Why Mama's Basket</span>
            <h2>Shopping made effortless</h2>
            <p>We bring the market to you, with the care of family and the speed of a pro team.</p>
        </div>
        <div class="why-grid">
            <?php
            $why = [
                ['clock', 'Lightning fast', 'Most deliveries arrive in under 15 minutes anywhere in Kigali.'],
                ['leaf',  'Fresh and quality', 'Hand picked produce and trusted brands, checked before packing.'],
                ['shield','Safe payments', 'Pay easily with Mobile Money and confirm your order on WhatsApp.'],
                ['basket','Everything in one place', 'Groceries, drinks, household items and more from many vendors.'],
                ['truck', 'Reliable delivery', 'Local riders who know the city and treat your order with care.'],
                ['headset','Always reachable', 'Friendly support every day of the week, ready to help.'],
            ];
            foreach ($why as $i => $w): ?>
            <div class="why-card reveal" data-delay="<?= ($i % 3) * 80 ?>">
                <span class="wi"><?= icon($w[0], 'icon', 26) ?></span>
                <h3><?= e($w[1]) ?></h3>
                <p><?= e($w[2]) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================================================== REVIEWS -->
<section class="section" id="reviews">
    <div class="container">
        <div class="section-head center reveal">
            <span class="eyebrow">Loved by Kigali</span>
            <h2>What our customers say</h2>
        </div>
        <div class="review-grid">
            <?php
            $reviews = [
                ['Aline U.',  'Kacyiru',  'I ordered groceries in the morning and they arrived before I finished my coffee. So fast and fresh.'],
                ['Eric M.',   'Kimironko','The packing is always neat and nothing is ever missing. Mama\'s Basket is my go to now.'],
                ['Claudine N.','Nyamirambo','Paying with Mobile Money and sending the receipt on WhatsApp is so simple. Great service.'],
            ];
            foreach ($reviews as $i => $r): ?>
            <div class="review-card reveal" data-delay="<?= $i * 90 ?>">
                <div class="stars"><?= str_repeat(icon('star', 'icon', 18), 5) ?></div>
                <p>"<?= e($r[2]) ?>"</p>
                <div class="reviewer">
                    <span class="av"><?= e(strtoupper(substr($r[0], 0, 1))) ?></span>
                    <span><b><?= e($r[0]) ?></b><span><?= e($r[1]) ?>, Kigali</span></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================================================== CTA -->
<section class="section-sm">
    <div class="container">
        <div class="cta-band reveal">
            <h2>Ready to fill your basket?</h2>
            <p>Start shopping now or send your list straight to our team on WhatsApp.</p>
            <div class="hero-cta">
                <a href="/shop.php" class="btn btn-white btn-lg"><?= icon('basket', 'icon', 20) ?> Start Shopping</a>
                <a href="<?= e(whatsapp_link('Hello Mama\'s Basket, I would like to place an order.')) ?>" class="btn btn-gold btn-lg" target="_blank" rel="noopener"><?= icon('whatsapp', 'icon', 20) ?> Order on WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
