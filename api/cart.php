<?php
/**
 * Session cart endpoint. Adds/updates/removes items and returns JSON.
 * No localStorage; the cart lives entirely in $_SESSION.
 */
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? ($_GET['action'] ?? '');
$id     = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$qty    = (int)($_POST['qty'] ?? 1);

if ($action === 'add' && $id > 0) {
    // Confirm the product exists and is purchasable before adding.
    $stmt = db()->prepare("SELECT id FROM products WHERE id = ? AND in_stock = 1");
    $stmt->execute([$id]);
    if ($stmt->fetch()) {
        $current = cart_items()[$id] ?? 0;
        cart_set($id, $current + max(1, $qty));
    }
} elseif ($action === 'set' && $id > 0) {
    cart_set($id, max(0, $qty));
} elseif ($action === 'remove' && $id > 0) {
    cart_set($id, 0);
} elseif ($action === 'clear') {
    cart_clear();
}

$data = cart_detailed();
echo json_encode([
    'count' => cart_count(),
    'total' => $data['total'],
    'total_label' => money($data['total']),
]);
