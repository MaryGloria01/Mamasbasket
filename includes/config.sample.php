<?php
/**
 * Copy this file to config.php and fill in your real values.
 * config.php is git-ignored so credentials never reach GitHub.
 */

// ---- Database (from Hostinger > Databases > MySQL) -------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_db_name');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');

// ---- Business / WhatsApp ---------------------------------------------------
// International format, digits only, no plus sign (used in wa.me links).
define('WHATSAPP_NUMBER', '250783817585');

// ---- Mobile Money payment details (shown on the payment page) --------------
define('MOMO_CODE', '000000');            // TODO: replace with real MoMo code
define('MOMO_NAME', "Mama's Basket");     // TODO: replace with registered MoMo name

// ---- Site ------------------------------------------------------------------
define('SITE_NAME', "Mama's Basket");
define('CURRENCY', 'RWF');
// Public base URL (no trailing slash). Used to build absolute receipt links
// for WhatsApp. Set to your live domain in production.
define('BASE_URL', 'http://localhost');
