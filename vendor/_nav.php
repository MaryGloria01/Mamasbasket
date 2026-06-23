<?php
/** Shared vendor dashboard nav config. Include after require_vendor(). */
$dashLinks = [
    ['/vendor/dashboard.php', 'Overview',  'grid',  'overview'],
    ['/vendor/products.php',  'Products',  'box',   'products'],
    ['/vendor/orders.php',    'Orders',    'cart',  'orders'],
    ['/vendor/account.php',   'Account',   'user',  'account'],
];
$dashLogout = '/vendor/logout.php';
