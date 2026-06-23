<?php
/**
 * Image upload helper. Validates type/size and stores the file under /uploads.
 * Returns the relative path (e.g. "uploads/products/abc.jpg") or null on failure,
 * setting $err by reference with a friendly message.
 */
require_once __DIR__ . '/functions.php';

function save_image(string $field, string $subdir, ?string &$err = null, bool $required = true): ?string
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required) { $err = 'Please choose an image.'; }
        return null;
    }

    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) { $err = 'Upload failed. Please try again.'; return null; }
    if ($f['size'] > 6 * 1024 * 1024)  { $err = 'Image is too large (max 6 MB).'; return null; }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $f['tmp_name']);
    finfo_close($finfo);
    if (!isset($allowed[$mime])) { $err = 'Please upload a JPG, PNG or WEBP image.'; return null; }

    $dirRel = 'uploads/' . trim($subdir, '/');
    $dirAbs = dirname(__DIR__) . '/' . $dirRel;
    if (!is_dir($dirAbs)) { @mkdir($dirAbs, 0775, true); }

    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $rel  = $dirRel . '/' . $name;
    $abs  = dirname(__DIR__) . '/' . $rel;

    if (!move_uploaded_file($f['tmp_name'], $abs)) { $err = 'Could not save the image.'; return null; }
    return $rel;
}

/** Delete a previously stored upload (best effort). */
function delete_image(?string $rel): void
{
    if (!$rel) return;
    $abs = dirname(__DIR__) . '/' . ltrim($rel, '/');
    if (is_file($abs) && strpos(realpath($abs), realpath(dirname(__DIR__) . '/uploads')) === 0) {
        @unlink($abs);
    }
}
