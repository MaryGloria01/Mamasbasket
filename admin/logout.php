<?php
require_once __DIR__ . '/../includes/functions.php';
unset($_SESSION['admin_id'], $_SESSION['admin_name']);
header('Location: /admin/login.php');
exit;
