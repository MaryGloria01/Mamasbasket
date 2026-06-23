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
            <p class="hero-sub">Groceries, meals, drinks, medicines and more. Shopped or picked up, then delivered fast across Kigali while you relax.</p>
            <div class="hero-cta">
                <a href="/shop.php" class="btn btn-green btn-lg"><?= icon('basket', 'icon', 20) ?> Start Shopping</a>
                <a href="<?= e(whatsapp_link('Hello Mama\'s Basket, I would like to place an order.')) ?>" class="btn btn-ghost btn-lg" target="_blank" rel="noopener"><?= icon('whatsapp', 'icon', 20) ?> Order on WhatsApp</a>
            </div>

            <div class="hero-pills">
                <div class="hpill"><span class="hpi"><?= icon('clock', 'icon', 20) ?></span><span><b>Under 15 minutes</b><span>Fast delivery</span></span></div>
                <div class="hpill"><span class="hpi"><?= icon('shield', 'icon', 20) ?></span><span><b>100% Reliable</b><span>Safe and on time</span></span></div>
                <div class="hpill"><span class="hpi"><?= icon('basket', 'icon', 20) ?></span><span><b>Anything you need</b><span>Food and essentials</span></span></div>
                <div class="hpill"><span class="hpi"><?= icon('headset', 'icon', 20) ?></span><span><b>7 days support</b><span>Always here</span></span></div>
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

        <!-- Animated delivery scene -->
        <div class="hero-stage">
            <div class="scene" aria-hidden="true">
                <!-- sky + sun -->
                <span class="sun"></span>
                <!-- drifting clouds -->
                <span class="cloud cloud-1"></span>
                <span class="cloud cloud-2"></span>
                <!-- parallax skyline -->
                <div class="skyline">
                    <svg viewBox="0 0 600 160" preserveAspectRatio="none" width="1200" height="160">
                        <g fill="#cfe0cf">
                            <rect x="10" y="70" width="46" height="90"/><rect x="66" y="40" width="34" height="120"/>
                            <rect x="110" y="86" width="54" height="74"/><rect x="174" y="56" width="30" height="104"/>
                            <rect x="214" y="96" width="48" height="64"/><rect x="272" y="30" width="38" height="130"/>
                            <rect x="320" y="74" width="44" height="86"/><rect x="374" y="52" width="32" height="108"/>
                            <rect x="416" y="92" width="52" height="68"/><rect x="478" y="44" width="34" height="116"/>
                            <rect x="522" y="80" width="46" height="80"/>
                        </g>
                        <g fill="#bcd6bd">
                            <rect x="10" y="70" width="46" height="14"/><rect x="66" y="40" width="34" height="14"/>
                            <rect x="272" y="30" width="38" height="14"/><rect x="478" y="44" width="34" height="14"/>
                        </g>
                    </svg>
                    <svg viewBox="0 0 600 160" preserveAspectRatio="none" width="1200" height="160">
                        <g fill="#cfe0cf">
                            <rect x="10" y="70" width="46" height="90"/><rect x="66" y="40" width="34" height="120"/>
                            <rect x="110" y="86" width="54" height="74"/><rect x="174" y="56" width="30" height="104"/>
                            <rect x="214" y="96" width="48" height="64"/><rect x="272" y="30" width="38" height="130"/>
                            <rect x="320" y="74" width="44" height="86"/><rect x="374" y="52" width="32" height="108"/>
                            <rect x="416" y="92" width="52" height="68"/><rect x="478" y="44" width="34" height="116"/>
                            <rect x="522" y="80" width="46" height="80"/>
                        </g>
                    </svg>
                </div>

                <!-- motion streaks -->
                <span class="streak s1"></span><span class="streak s2"></span><span class="streak s3"></span>

                <!-- the rider + scooter -->
                <svg class="bike" viewBox="0 0 560 360" width="560" height="360" role="img" aria-label="Mama's Basket delivery rider">
                    <defs>
                        <linearGradient id="gGreen" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#2e7d32"/><stop offset="1" stop-color="#13491b"/>
                        </linearGradient>
                        <linearGradient id="gGold" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#f0b454"/><stop offset="1" stop-color="#e0760f"/>
                        </linearGradient>
                    </defs>

                    <ellipse class="bike-shadow" cx="290" cy="320" rx="210" ry="16" fill="#0c3a17"/>

                    <g class="bike-bob">
                        <!-- rear delivery box -->
                        <g>
                            <rect x="120" y="120" width="120" height="100" rx="14" fill="url(#gGreen)"/>
                            <rect x="120" y="120" width="120" height="26" rx="13" fill="#256b29"/>
                            <rect x="138" y="150" width="84" height="58" rx="10" fill="#f7f3e9"/>
                            <g transform="translate(155,160) scale(2.1)" fill="none" stroke="#1b5e20" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 5h12l-1 7H3z"/><path d="M5 5 8 1l3 4"/>
                            </g>
                        </g>

                        <!-- frame + deck -->
                        <path d="M150 250 L150 250" />
                        <rect x="235" y="250" width="140" height="16" rx="8" fill="#15361d"/>
                        <path d="M360 256 L384 150 L410 150 L398 256 Z" fill="url(#gGreen)"/>
                        <rect x="232" y="186" width="78" height="18" rx="9" fill="#11321a"/>
                        <path d="M150 220 q-6 24 14 34 l60 0 0 -34 z" fill="url(#gGreen)"/>

                        <!-- handlebar + headlight -->
                        <rect x="392" y="120" width="10" height="40" rx="5" fill="#15361d"/>
                        <rect x="396" y="116" width="52" height="9" rx="4.5" fill="#15361d"/>
                        <circle cx="430" cy="166" r="12" fill="url(#gGold)"/>
                        <path class="beam" d="M442 160 L520 144 L520 188 L442 172 Z" fill="#ffd98a"/>

                        <!-- rider -->
                        <g>
                            <path d="M300 130 q12 -2 22 6 l78 18 -6 20 -84 -16 q-18 -4 -22 -22 z" fill="url(#gGreen)"/>
                            <path d="M300 132 q-16 8 -14 36 l6 40 26 -2 -6 -40 q-2 -22 6 -30 z" fill="#256b29"/>
                            <path d="M312 206 l-2 30 -26 16 -6 -12 22 -16 2 -20 z" fill="#11321a"/>
                            <rect x="276" y="244" width="34" height="14" rx="6" fill="#0c2614"/>
                            <path d="M392 150 q14 6 30 -2 l6 12 q-22 12 -42 2 z" fill="#256b29"/>
                            <circle cx="300" cy="96" r="28" fill="url(#gGreen)"/>
                            <path d="M300 68 a28 28 0 0 1 26 18 l-52 0 a28 28 0 0 1 26 -18 z" fill="#34953b"/>
                            <path d="M322 92 a26 24 0 0 1 -2 22 l-26 -4 q-6 -14 6 -22 z" fill="#0e2c17"/>
                            <rect x="294" y="120" width="14" height="12" rx="4" fill="#e9b98c"/>
                        </g>

                        <!-- wheels -->
                        <g class="wheel">
                            <circle cx="165" cy="262" r="50" fill="#1d2622"/>
                            <circle cx="165" cy="262" r="50" fill="none" stroke="#0f1612" stroke-width="6"/>
                            <circle cx="165" cy="262" r="38" fill="#e9ede9"/>
                            <g class="spokes">
                                <line x1="165" y1="226" x2="165" y2="298" stroke="#9aa69c" stroke-width="4"/>
                                <line x1="134" y1="244" x2="196" y2="280" stroke="#9aa69c" stroke-width="4"/>
                                <line x1="196" y1="244" x2="134" y2="280" stroke="#9aa69c" stroke-width="4"/>
                            </g>
                            <circle cx="165" cy="262" r="9" fill="#2e7d32"/>
                        </g>
                        <g class="wheel">
                            <circle cx="415" cy="262" r="50" fill="#1d2622"/>
                            <circle cx="415" cy="262" r="50" fill="none" stroke="#0f1612" stroke-width="6"/>
                            <circle cx="415" cy="262" r="38" fill="#e9ede9"/>
                            <g class="spokes">
                                <line x1="415" y1="226" x2="415" y2="298" stroke="#9aa69c" stroke-width="4"/>
                                <line x1="384" y1="244" x2="446" y2="280" stroke="#9aa69c" stroke-width="4"/>
                                <line x1="446" y1="244" x2="384" y2="280" stroke="#9aa69c" stroke-width="4"/>
                            </g>
                            <circle cx="415" cy="262" r="9" fill="#2e7d32"/>
                        </g>
                    </g>
                </svg>

                <!-- moving road -->
                <div class="road"><span class="lane"></span></div>
            </div>

            <!-- floating overlays -->
            <div class="float-card fc-track">
                <span class="dot" style="background:linear-gradient(150deg,var(--green-600),var(--green-800))"><?= icon('truck', 'icon', 20) ?></span>
                <span><span class="t">Your order is on the way</span><span class="s">Arriving in under 15 min</span></span>
            </div>
            <div class="float-card fc-fresh">
                <span class="dot" style="background:linear-gradient(150deg,var(--gold),var(--orange))"><?= icon('leaf', 'icon', 20) ?></span>
                <span><span class="t">Fresh &amp; quality</span><span class="s">Hand picked for you</span></span>
            </div>
        </div>
    </div>
</section>

<!-- ===================================================== TRUST BAR -->
<section class="trustbar">
    <div class="container">
        <div class="trust-item"><?= icon('truck') ?><div><b>Local riders</b><span>Kigali based and trusted</span></div></div>
        <div class="trust-item"><?= icon('pin') ?><div><b>Across Kigali</b><span>Delivering to all neighbourhoods</span></div></div>
        <div class="trust-item"><?= icon('leaf') ?><div><b>Eco friendly</b><span>100% electric, zero emissions</span></div></div>
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
