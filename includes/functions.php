<?php
/**
 * Shared helpers: escaping, money formatting, session cart, auth, SVG icons.
 */
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* --------------------------------------------------------------------------
 * Output helpers
 * ----------------------------------------------------------------------- */

/** Escape for safe HTML output. */
function e(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

/** Format an amount as "RWF 5,000". */
function money($amount): string
{
    return CURRENCY . ' ' . number_format((float)$amount, 0, '.', ',');
}

/** Make a url-friendly slug. */
function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim(strtolower($text), '-');
    $text = preg_replace('~[^-a-z0-9]+~', '', $text);
    return $text !== '' ? $text : 'item';
}

/** Public URL for an uploaded/static asset. */
function asset(string $path): string
{
    return '/' . ltrim($path, '/');
}

/* --------------------------------------------------------------------------
 * Session cart (no localStorage anywhere; cart lives server-side)
 * Structure: $_SESSION['cart'] = [ productId => qty, ... ]
 * ----------------------------------------------------------------------- */

function cart_items(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    return array_sum(cart_items());
}

function cart_set(int $productId, int $qty): void
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $qty;
    }
}

function cart_clear(): void
{
    $_SESSION['cart'] = [];
}

/**
 * Resolve the session cart against the DB into detailed line items + total.
 * Returns ['lines' => [...], 'total' => float].
 */
function cart_detailed(): array
{
    $items = cart_items();
    if (!$items) {
        return ['lines' => [], 'total' => 0.0];
    }
    $ids  = array_map('intval', array_keys($items));
    $in   = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare(
        "SELECT p.id, p.name, p.price, p.unit, p.image_url, v.shop_name
         FROM products p
         JOIN vendors v ON v.id = p.vendor_id
         WHERE p.id IN ($in)"
    );
    $stmt->execute($ids);

    $lines = [];
    $total = 0.0;
    foreach ($stmt->fetchAll() as $p) {
        $qty       = (int)($items[$p['id']] ?? 0);
        $subtotal  = $qty * (float)$p['price'];
        $total    += $subtotal;
        $p['qty']  = $qty;
        $p['subtotal'] = $subtotal;
        $lines[] = $p;
    }
    return ['lines' => $lines, 'total' => $total];
}

/* --------------------------------------------------------------------------
 * Auth helpers
 * ----------------------------------------------------------------------- */

function current_vendor_id(): ?int
{
    return $_SESSION['vendor_id'] ?? null;
}

function require_vendor(): void
{
    if (!current_vendor_id()) {
        header('Location: /vendor/login.php');
        exit;
    }
}

function current_admin_id(): ?int
{
    return $_SESSION['admin_id'] ?? null;
}

function require_admin(): void
{
    if (!current_admin_id()) {
        header('Location: /admin/login.php');
        exit;
    }
}

/* --------------------------------------------------------------------------
 * CSRF
 * ----------------------------------------------------------------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

/* --------------------------------------------------------------------------
 * WhatsApp link builder
 * ----------------------------------------------------------------------- */

function whatsapp_link(string $message): string
{
    return 'https://wa.me/' . WHATSAPP_NUMBER . '?text=' . rawurlencode($message);
}

/* --------------------------------------------------------------------------
 * Inline SVG icon set (no emoji anywhere; stroke icons, currentColor)
 * ----------------------------------------------------------------------- */

function icon(string $name, string $class = 'icon', int $size = 24): string
{
    $paths = [
        'basket'  => '<path d="M5 11h14l-1.2 8.2a2 2 0 0 1-2 1.8H8.2a2 2 0 0 1-2-1.8L5 11Z"/><path d="M9 11 12 4l3 7"/><path d="M3 11h18"/>',
        'cart'    => '<circle cx="9" cy="20" r="1.5"/><circle cx="17" cy="20" r="1.5"/><path d="M3 4h2l2.4 12.2a1.5 1.5 0 0 0 1.5 1.2h8.2a1.5 1.5 0 0 0 1.5-1.2L21 8H6"/>',
        'whatsapp'=> '<path d="M12 3a9 9 0 0 0-7.7 13.6L3 21l4.5-1.2A9 9 0 1 0 12 3Z"/><path d="M8.5 8.6c-.2 1.2.5 2.7 1.8 4s2.8 2 4 1.8c.6-.1 1-.6 1.2-1.1.1-.4 0-.6-.3-.8l-1.4-.8c-.3-.2-.6-.1-.8.1l-.4.5c-.7-.3-1.5-1-1.9-1.9l.5-.4c.2-.2.3-.5.1-.8l-.8-1.4c-.2-.3-.4-.4-.8-.3-.5.2-1 .6-1.1 1.2Z"/>',
        'copy'    => '<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/>',
        'check'   => '<path d="M5 12.5 10 17.5 19 7"/>',
        'clock'   => '<circle cx="12" cy="12" r="8"/><path d="M12 8v4l3 2"/>',
        'shield'  => '<path d="M12 3 5 6v5c0 4 3 7.5 7 9 4-1.5 7-5 7-9V6l-7-3Z"/><path d="m9.5 12 2 2 3.5-3.5"/>',
        'leaf'    => '<path d="M5 19C5 11 11 5 19 5c0 8-6 14-14 14Z"/><path d="M5 19c4-4 7-6 11-8"/>',
        'truck'   => '<path d="M3 6h11v9H3zM14 9h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>',
        'search'  => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/>',
        'plus'    => '<path d="M12 5v14M5 12h14"/>',
        'minus'   => '<path d="M5 12h14"/>',
        'trash'   => '<path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m1 0-1 13a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1L6 7"/>',
        'upload'  => '<path d="M12 16V4m0 0L8 8m4-4 4 4"/><path d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/>',
        'star'    => '<path d="m12 4 2.3 4.8 5.2.7-3.8 3.6.9 5.2L12 16l-4.6 2.3.9-5.2L4.5 9.5l5.2-.7L12 4Z"/>',
        'pin'     => '<path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
        'phone'   => '<path d="M5 4h3l1.5 4-2 1.5a11 11 0 0 0 5 5l1.5-2 4 1.5V18a2 2 0 0 1-2 2A15 15 0 0 1 4 6a2 2 0 0 1 1-2Z"/>',
        'menu'    => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close'   => '<path d="M6 6 18 18M18 6 6 18"/>',
        'arrow'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'box'     => '<path d="m12 3 8 4v10l-8 4-8-4V7l8-4Z"/><path d="m4 7 8 4 8-4M12 11v10"/>',
        'tag'     => '<path d="M4 4h7l9 9-7 7-9-9V4Z"/><circle cx="8" cy="8" r="1.4"/>',
        'user'    => '<circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/>',
        'logout'  => '<path d="M14 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4"/><path d="M9 12h11M16 8l4 4-4 4"/>',
        'grid'    => '<rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/>',
        'image'   => '<rect x="4" y="4" width="16" height="16" rx="2"/><circle cx="9" cy="9" r="1.6"/><path d="m5 17 4-4 4 3 3-3 3 3"/>',
        'grain'   => '<path d="M12 3v18M12 7c-2 0-3-1-3-3 2 0 3 1 3 3Zm0 0c2 0 3-1 3-3-2 0-3 1-3 3Zm0 5c-2 0-3-1-3-3 2 0 3 1 3 3Zm0 0c2 0 3-1 3-3-2 0-3 1-3 3Zm0 5c-2 0-3-1-3-3 2 0 3 1 3 3Zm0 0c2 0 3-1 3-3-2 0-3 1-3 3Z"/>',
        'drink'   => '<path d="M6 4h12l-1.5 16h-9L6 4Z"/><path d="M6.7 9h10.6"/>',
        'spice'   => '<path d="M9 3h6v3H9zM8 6h8l-1 14H9L8 6Z"/><path d="M10 10h.01M13 13h.01M11 16h.01"/>',
        'home'    => '<path d="m4 11 8-7 8 7"/><path d="M6 10v9a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-9"/>',
        'beans'   => '<path d="M7 14c0-4 3-8 7-8 1 4-2 9-7 8Z"/><path d="M17 10c0 4-3 8-7 8-1-4 2-9 7-8Z"/>',
        'snow'    => '<path d="M12 3v18M5 7l14 10M19 7 5 17"/>',
        'snack'   => '<path d="M5 8h14l-1 11a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1L5 8Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/>',
        'headset' => '<path d="M4 13a8 8 0 0 1 16 0"/><rect x="3" y="13" width="4" height="6" rx="1.5"/><rect x="17" y="13" width="4" height="6" rx="1.5"/><path d="M20 19a3 3 0 0 1-3 3h-3"/>',
    ];
    $body = $paths[$name] ?? $paths['box'];
    return '<svg class="' . e($class) . '" width="' . $size . '" height="' . $size . '" '
        . 'viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" '
        . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
}
