<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <a href="/index.php" class="brand">
                    <span class="brand-mark"><?= icon('basket') ?></span>
                    <span class="brand-name" style="color:#fff">Mama's <span>Basket</span></span>
                </a>
                <p>You Order, We Shop, We Pack, We Deliver. Fresh groceries, meals, drinks and everyday essentials delivered fast across Kigali.</p>
            </div>
            <div>
                <h4>Shop</h4>
                <ul class="footer-links">
                    <li><a href="/shop.php">All Products</a></li>
                    <li><a href="/shop.php?cat=fresh-produce">Fresh Produce</a></li>
                    <li><a href="/shop.php?cat=drinks">Drinks</a></li>
                    <li><a href="/shop.php?cat=household">Household</a></li>
                </ul>
            </div>
            <div>
                <h4>Company</h4>
                <ul class="footer-links">
                    <li><a href="/index.php#why">Why Us</a></li>
                    <li><a href="/index.php#reviews">Reviews</a></li>
                    <li><a href="/vendor/login.php">Become a Vendor</a></li>
                    <li><a href="/admin/login.php">Admin</a></li>
                </ul>
            </div>
            <div>
                <h4>Get in touch</h4>
                <ul class="footer-contact">
                    <li><?= icon('whatsapp', 'icon', 18) ?> <a href="<?= e(whatsapp_link('Hello Mama\'s Basket')) ?>" target="_blank" rel="noopener">Order on WhatsApp</a></li>
                    <li><?= icon('pin', 'icon', 18) ?> Kigali, Rwanda</li>
                    <li><?= icon('clock', 'icon', 18) ?> Open 7 days a week</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> Mama's Basket. All rights reserved.</span>
            <span>You Order. We Shop. We Pack. We Deliver.</span>
        </div>
    </div>
</footer>

<a href="<?= e(whatsapp_link('Hello Mama\'s Basket, I would like to place an order.')) ?>" class="wa-float" target="_blank" rel="noopener">
    <?= icon('whatsapp', 'icon', 24) ?><span class="lbl">Order on WhatsApp</span>
</a>

<script src="/js/main.js" defer></script>
<?php if (!empty($pageScripts)) foreach ($pageScripts as $s): ?>
<script src="<?= e($s) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
