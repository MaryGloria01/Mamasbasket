<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/upload.php';
require_vendor();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $id = (int)($_POST['id'] ?? 0);
    try {
        $stmt = db()->prepare("SELECT image_url FROM products WHERE id = ? AND vendor_id = ?");
        $stmt->execute([$id, current_vendor_id()]);
        $p = $stmt->fetch();
        if ($p) {
            db()->prepare("DELETE FROM products WHERE id = ? AND vendor_id = ?")
                ->execute([$id, current_vendor_id()]);
            delete_image($p['image_url']);
        }
    } catch (Throwable $e) {}
}
header('Location: /vendor/products.php');
exit;
