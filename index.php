<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Mama's Basket | You Order, We Shop, We Pack, We Deliver";
$active = 'home';
$pageScripts = ['/js/home.js'];

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

/* Hero media: prefer a looping video clip, else a photo, else a placeholder.
   Drop the file into /assets/img/ and it appears automatically. */
$heroVideo = file_exists(__DIR__ . '/assets/img/hero.mp4');
$heroImg = null;
foreach (['hero-rider.svg', 'hero-rider.png', 'hero-rider.webp', 'hero-rider.jpg', 'hero-rider.jpeg'] as $f) {
    if (file_exists(__DIR__ . '/assets/img/' . $f)) { $heroImg = 'assets/img/' . $f; break; }
}

require __DIR__ . '/includes/header.php';
?>

<!-- ===================================================== HERO -->
<section class="hero hero-photo">
    <div class="hero-main">
        <!-- real city-street background photo -->
        <div class="hero-bg" aria-hidden="true"></div>
        <span class="hs-bike-shadow"></span>

        <?php if ($heroVideo): ?>
            <video class="hero-rider" autoplay muted loop playsinline>
                <source src="/assets/img/hero.mp4" type="video/mp4">
                <?php if (file_exists(__DIR__ . '/assets/img/hero.webm')): ?><source src="/assets/img/hero.webm" type="video/webm"><?php endif; ?>
            </video>
        <?php elseif ($heroImg): ?>
            <img class="hero-rider" src="<?= e(asset($heroImg)) ?>" alt="Mama's Basket delivery rider on a Spiro electric motorbike">
        <?php endif; ?>
        <span class="hero-scrim"></span>

        <div class="container">
            <div class="hero-copy">
                <span class="hero-badge"><?= icon('shield', 'icon', 18) ?> Premium shopping &amp; delivery service</span>
                <h1 class="hero-title">
                    <span class="line"><span class="hl hl-ink">You Order.</span></span>
                    <span class="line"><span class="hl hl-green">We Shop. We Pack.</span></span>
                    <span class="line"><span class="hl hl-orange">We Deliver.<svg class="hl-stroke" viewBox="0 0 300 18" preserveAspectRatio="none" aria-hidden="true"><path d="M4 12 C 70 4, 150 4, 296 9"/></svg></span></span>
                </h1>
                <p class="hero-sub">Groceries, meals, drinks, medicines and more. Shopped or picked up, then delivered fast across Kigali while you relax.</p>
                <div class="hero-cta">
                    <a href="/shop.php" class="btn btn-ghost btn-lg"><?= icon('basket', 'icon', 20) ?> Start Shopping</a>
                    <a href="<?= e(whatsapp_link('Hello Mama\'s Basket, I would like to place an order.')) ?>" class="btn btn-ghost btn-lg" target="_blank" rel="noopener"><?= icon('whatsapp', 'icon', 20) ?> Order on WhatsApp</a>
                </div>

                <div class="hero-trust">
                    <div class="avatars">
                        <?php foreach (['A','E','C','N'] as $i => $ltr): ?>
                        <span class="av" style="--i:<?= $i ?>"><?= $ltr ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="trust-meta">
                        <div class="stars"><?= str_repeat(icon('star', 'icon', 16), 5) ?></div>
                        <span><b data-count="4.8">0</b> rating from <b data-count="2500" data-suffix="+">0</b> happy customers</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- bottom eco bar -->
    <div class="hero-ecobar">
        <div class="container">
            <div class="trust-item"><?= icon('truck') ?><div><b>Local riders</b><span>Kigali based and trusted</span></div></div>
            <div class="trust-item"><?= icon('pin') ?><div><b>Across Kigali</b><span>Delivering to all neighbourhoods</span></div></div>
            <div class="trust-item"><?= icon('leaf') ?><div><b>Eco friendly</b><span>100% electric, zero emissions</span></div></div>
        </div>
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

<!-- full-width section divider -->
<div class="sec-bar"></div>

<!-- ===================================================== CATEGORIES -->
<section class="section" id="categories" style="padding-top:40px">
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

<!-- bold section divider -->
<div class="container">
    <div class="sec-divider sec-divider-bold"><span></span><i class="dv-badge"><?= icon('basket', 'icon', 24) ?></i><span></span></div>
</div>

<!-- ===================================================== FEATURED PRODUCTS -->
<section class="section" style="background:var(--cream-2);padding-top:40px">
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
