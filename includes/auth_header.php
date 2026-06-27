<?php
require_once __DIR__ . '/functions.php';
$authTitle = $authTitle ?? 'Sign in';
$sideTitle = $sideTitle ?? 'Welcome';
$sideText  = $sideText  ?? '';
$sidePerks = $sidePerks ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($authTitle) ?> | Mama's Basket</title>
    <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Sora:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <aside class="auth-side">
        <a href="/index.php" class="brand">
            <img src="<?= e(brand_logo_src()) ?>" alt="Mama's Basket" class="brand-logo brand-logo-lg">
        </a>
        <div>
            <h2><?= e($sideTitle) ?></h2>
            <p><?= e($sideText) ?></p>
            <?php if ($sidePerks): ?>
            <ul class="auth-perks">
                <?php foreach ($sidePerks as $p): ?><li><?= icon('check', 'icon', 20) ?> <?= e($p) ?></li><?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
        <span style="position:relative;font-size:.85rem;color:rgba(255,255,255,.6)">You Order. We Shop. We Pack. We Deliver.</span>
    </aside>
    <main class="auth-main">
        <div class="auth-card">
