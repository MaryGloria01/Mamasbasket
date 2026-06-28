<?php
/**
 * Reusable dashboard shell (used by vendor and super-admin portals).
 * Expects: $dashTitle, $dashActive, $dashLinks (array of [href,label,icon]),
 *          $dashUser (display name), $dashLogout (url), $dashRoleLabel.
 */
require_once __DIR__ . '/functions.php';
$dashTitle  = $dashTitle  ?? 'Dashboard';
$dashActive = $dashActive ?? '';
$dashLinks  = $dashLinks  ?? [];
$dashLogout = $dashLogout ?? '#';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($dashTitle) ?> | Mama's Basket</title>
    <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Sora:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="dash">
    <aside class="dash-side">
        <div class="dash-brand">
            <a href="/index.php" class="brand on-dark">
                <span class="brand-ico"><?= icon('basket', 'icon', 44) ?></span>
                <span class="brand-text">
                    <span class="brand-name"><span class="ms">Mama's</span><span class="bk">Basket</span></span>
                    <span class="brand-tag">We Shop. We Pack. We Deliver.</span>
                </span>
            </a>
        </div>
        <?php foreach ($dashLinks as $l): ?>
        <a href="<?= e($l[0]) ?>" class="<?= $dashActive === ($l[3] ?? $l[1]) ? 'active' : '' ?>">
            <?= icon($l[2], 'icon', 20) ?> <?= e($l[1]) ?>
        </a>
        <?php endforeach; ?>
        <div class="spacer"></div>
        <a href="/index.php" target="_blank"><?= icon('arrow', 'icon', 20) ?> View site</a>
        <a href="<?= e($dashLogout) ?>"><?= icon('logout', 'icon', 20) ?> Log out</a>
    </aside>
    <main class="dash-main">
