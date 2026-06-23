<?php
require_once __DIR__ . '/../includes/functions.php';
unset($_SESSION['vendor_id'], $_SESSION['vendor_name'], $_SESSION['vendor_status']);
header('Location: /vendor/login.php');
exit;
