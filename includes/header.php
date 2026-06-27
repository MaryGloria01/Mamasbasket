<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? SITE_NAME;
$active    = $active ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="Mama's Basket. You Order, We Shop, We Pack, We Deliver. Fresh groceries and essentials delivered fast across Kigali.">
    <meta name="theme-color" content="#1b5e20">

    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="You Order, We Shop, We Pack, We Deliver. Fresh groceries delivered across Kigali.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?= e(BASE_URL) ?>/assets/img/og-cover.jpg">

    <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Sora:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<header class="nav" id="nav">
    <div class="container">
        <a href="/index.php" class="brand" aria-label="Mama's Basket home">
            <span class="brand-mark"><?= icon('basket') ?></span>
            <span>
                <span class="brand-name"><span class="ms">Mama's</span><span class="bk">Basket</span></span>
                <span class="brand-tag">Fresh groceries across Kigali</span>
            </span>
        </a>

        <nav class="nav-links" id="navLinks">
            <a href="/index.php" class="<?= $active === 'home' ? 'active' : '' ?>">Home</a>
            <a href="/shop.php" class="<?= $active === 'shop' ? 'active' : '' ?>">Shop</a>
            <a href="/index.php#why" class="<?= $active === 'why' ? 'active' : '' ?>">Why Us</a>
            <a href="/index.php#reviews">Reviews</a>
            <a href="/vendor/login.php">Vendor Login</a>
        </nav>

        <div class="nav-actions">
            <a href="/cart.php" class="cart-btn" aria-label="View cart">
                <?= icon('cart', 'icon', 22) ?>
                <span class="cart-badge" id="cartBadge"><?= cart_count() ?></span>
            </a>
            <a href="<?= e(whatsapp_link('Hello Mama\'s Basket, I would like to place an order.')) ?>" class="btn btn-green btn-sm" target="_blank" rel="noopener">
                <?= icon('whatsapp', 'icon', 20) ?><span class="label-hide">Order on WhatsApp</span>
            </a>
            <button class="nav-toggle" id="navToggle" aria-label="Menu"><?= icon('menu') ?></button>
        </div>
    </div>
</header>
