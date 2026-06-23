# Mama's Basket

You Order. We Shop. We Pack. We Deliver.

A multi-vendor grocery shopping and delivery website for Kigali. Shoppers browse
products from many vendors, build a basket, pay with Mobile Money and confirm the
order on WhatsApp by uploading their payment receipt.

## Tech stack

- PHP 8 and MySQL (runs natively on Hostinger Business shared hosting)
- Vanilla HTML, CSS and JavaScript on the front end
- Three.js for the animated 3D home hero (loaded from a CDN, no build step)
- No localStorage anywhere. The cart lives in a PHP session; everything else is in MySQL.

## Roles

| Role        | What they do                                                        |
|-------------|---------------------------------------------------------------------|
| Buyer       | Browse the shop, build a basket, pay and order on WhatsApp          |
| Vendor      | Own login. Add and manage their own products, see their orders      |
| Super admin | Approve vendors, manage categories, oversee all products and orders |

## Folder layout

```
index.php            Home (3D hero)
shop.php             Product listing with categories and search
product.php          Single product
cart.php             Basket (session based)
checkout.php         Payment: MoMo code + copy, receipt upload, WhatsApp handoff
404.php              Not found page
/api/cart.php        Session cart endpoint (add / set / remove / clear)
/includes/           config, db, helpers, header/footer, upload, layouts
/vendor/             Vendor portal (login, register, dashboard, products, orders, account)
/admin/              Super-admin portal (setup, login, vendors, products, categories, orders)
/uploads/            Product images and payment receipts (git-ignored)
/css, /js, /assets   Front-end assets
/sql/schema.sql      Database schema and starter categories
```

## Local / server setup

1. Create a MySQL database and import `sql/schema.sql`.
2. Copy `includes/config.sample.php` to `includes/config.php` and fill in:
   - Database credentials (`DB_*`)
   - `WHATSAPP_NUMBER` (digits only, already set to the business number)
   - `MOMO_CODE` and `MOMO_NAME` (replace the placeholders with the real values)
   - `BASE_URL` (your live domain, e.g. `https://mamasbasket.com`) so receipt links are absolute
3. Make sure `/uploads/products` and `/uploads/receipts` are writable by the web server.
4. Visit `/admin/setup.php` once to create the first administrator, then delete that file.
5. Vendors register at `/vendor/register.php` and go live after an admin approves them.

## Deploying to Hostinger Business

1. Upload everything except `includes/config.php` (set the real values directly on the server).
2. Create the database in hPanel, import `sql/schema.sql` via phpMyAdmin.
3. Point the document root at this folder. `.htaccess` handles the 404 page,
   security headers, caching and gzip.
4. Confirm `uploads/` is writable (755 on folders) so product images and receipts save.

## Things to provide

- Real Mobile Money code and registered name (currently placeholders in config).
- Optional `assets/img/og-cover.jpg` for nicer link previews when the site is shared.
