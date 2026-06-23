<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Shop | Mama's Basket";
$active = 'shop';

$catSlug = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$q       = isset($_GET['q']) ? trim($_GET['q']) : '';

$categories = [];
$products = [];
$activeCat = null;
try {
    $categories = db()->query("SELECT * FROM categories ORDER BY sort_order, name")->fetchAll();

    $sql = "SELECT p.*, v.shop_name, c.name AS category_name, c.slug AS category_slug
            FROM products p
            JOIN vendors v ON v.id = p.vendor_id AND v.status='approved'
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE 1=1";
    $params = [];
    if ($catSlug !== '') {
        $sql .= " AND c.slug = ?";
        $params[] = $catSlug;
        foreach ($categories as $c) { if ($c['slug'] === $catSlug) $activeCat = $c; }
    }
    if ($q !== '') {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$q%"; $params[] = "%$q%";
    }
    $sql .= " ORDER BY p.in_stock DESC, p.created_at DESC";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (Throwable $e) { /* fallback below */ }

require __DIR__ . '/includes/header.php';
?>

<section class="shop-hero">
    <div class="container">
        <span class="eyebrow">Shop</span>
        <h1><?= $activeCat ? e($activeCat['name']) : 'All products' ?></h1>
        <p>Fresh groceries and everyday essentials, delivered fast across Kigali.</p>
        <form class="shop-search" method="get" action="/shop.php">
            <?php if ($catSlug): ?><input type="hidden" name="cat" value="<?= e($catSlug) ?>"><?php endif; ?>
            <span class="si"><?= icon('search', 'icon', 20) ?></span>
            <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Search for rice, milk, soap...">
            <button class="btn btn-green" type="submit">Search</button>
        </form>
    </div>
</section>

<section class="section-sm" style="padding-top:28px">
    <div class="container shop-layout">
        <aside class="shop-side">
            <h4>Categories</h4>
            <ul class="cat-list">
                <li><a href="/shop.php" class="<?= $catSlug === '' ? 'active' : '' ?>"><?= icon('grid', 'icon', 18) ?> All products</a></li>
                <?php foreach ($categories as $c): ?>
                <li><a href="/shop.php?cat=<?= e($c['slug']) ?>" class="<?= $catSlug === $c['slug'] ? 'active' : '' ?>"><?= icon($c['icon'] ?: 'box', 'icon', 18) ?> <?= e($c['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <div class="shop-main">
            <?php if ($products): ?>
            <div class="shop-count"><?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?><?= $q ? ' for "' . e($q) . '"' : '' ?></div>
            <div class="product-grid shop-grid">
                <?php foreach ($products as $i => $p): ?>
                <article class="product-card reveal" data-delay="<?= ($i % 4) * 50 ?>">
                    <a class="product-thumb" href="/product.php?id=<?= (int)$p['id'] ?>">
                        <?php if ($p['image_url']): ?>
                            <img src="<?= e(asset($p['image_url'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                        <?php else: ?><span class="ph"><?= icon('image', 'icon', 54) ?></span><?php endif; ?>
                        <?php if ($p['category_name']): ?><span class="product-tag"><?= e($p['category_name']) ?></span><?php endif; ?>
                    </a>
                    <div class="product-body">
                        <span class="product-vendor"><?= icon('tag', 'icon', 13) ?> <?= e($p['shop_name']) ?></span>
                        <a class="product-name" href="/product.php?id=<?= (int)$p['id'] ?>"><?= e($p['name']) ?></a>
                        <div class="product-foot">
                            <span class="product-price"><?= money($p['price']) ?> <?php if ($p['unit']): ?><small><?= e($p['unit']) ?></small><?php endif; ?></span>
                            <?php if ($p['in_stock']): ?>
                                <button class="add-btn" data-add="<?= (int)$p['id'] ?>" aria-label="Add to cart"><?= icon('plus', 'icon', 20) ?></button>
                            <?php else: ?>
                                <span class="out-stock">Sold out</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="panel" style="padding:56px;text-align:center">
                <span class="brand-mark" style="margin:0 auto 16px"><?= icon('search') ?></span>
                <h3 style="color:var(--green-900);margin-bottom:8px">No products found</h3>
                <p class="hint"><?= $q || $catSlug ? 'Try another search or category.' : 'Approved vendors will list products here soon.' ?></p>
                <?php if ($q || $catSlug): ?><a href="/shop.php" class="btn btn-ghost" style="margin-top:18px">View all products</a><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
