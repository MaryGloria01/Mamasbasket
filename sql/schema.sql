-- Mama's Basket database schema
-- Import this in Hostinger phpMyAdmin after creating the database.
-- Charset utf8mb4 for full unicode support.

SET NAMES utf8mb4;
SET time_zone = '+02:00'; -- Kigali (CAT)

-- ---------------------------------------------------------------------------
-- Super admins (site owners)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS super_admins (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120) NOT NULL,
    email         VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Vendors (each has own login; approved by super admin)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS vendors (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    shop_name     VARCHAR(160) NOT NULL,
    owner_name    VARCHAR(120) NOT NULL,
    email         VARCHAR(160) NOT NULL UNIQUE,
    phone         VARCHAR(40)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    logo_url      VARCHAR(255) DEFAULT NULL,
    status        ENUM('pending','approved','suspended') NOT NULL DEFAULT 'pending',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_vendors_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Categories (managed by super admin)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    name      VARCHAR(120) NOT NULL,
    slug      VARCHAR(140) NOT NULL UNIQUE,
    icon      VARCHAR(80)  DEFAULT NULL, -- svg icon key
    sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Products (owned by a vendor, classified by a category)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id   INT NOT NULL,
    category_id INT DEFAULT NULL,
    name        VARCHAR(180) NOT NULL,
    description TEXT,
    price       DECIMAL(12,2) NOT NULL DEFAULT 0, -- RWF
    unit        VARCHAR(40) DEFAULT NULL,         -- e.g. "per kg", "per bag"
    image_url   VARCHAR(255) DEFAULT NULL,
    in_stock    TINYINT(1) NOT NULL DEFAULT 1,
    featured    TINYINT(1) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_products_featured (featured, in_stock),
    KEY idx_products_created (created_at),
    CONSTRAINT fk_products_vendor   FOREIGN KEY (vendor_id)   REFERENCES vendors(id)    ON DELETE CASCADE,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Orders (one row per WhatsApp checkout; items stored as JSON snapshot)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    reference     VARCHAR(40) NOT NULL UNIQUE, -- human friendly code e.g. MB-7K3Q
    buyer_name   VARCHAR(160) NOT NULL,
    buyer_phone  VARCHAR(40) DEFAULT NULL,
    delivery_address VARCHAR(255) DEFAULT NULL, -- where to deliver
    momo_name    VARCHAR(160) DEFAULT NULL,    -- name buyer paid with
    items_json   LONGTEXT NOT NULL,            -- snapshot of cart
    total        DECIMAL(12,2) NOT NULL DEFAULT 0,
    receipt_url  VARCHAR(255) DEFAULT NULL,
    status       ENUM('pending','confirmed','delivered','cancelled') NOT NULL DEFAULT 'pending',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_orders_status (status),
    KEY idx_orders_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Seed data (starter categories so the shop is not empty)
-- ---------------------------------------------------------------------------
INSERT INTO categories (name, slug, icon, sort_order) VALUES
 ('Rice & Grains',      'rice-grains',      'grain',   1),
 ('Beans & Pulses',     'beans-pulses',     'beans',   2),
 ('Spices & Seasonings','spices-seasonings','spice',   3),
 ('Drinks',             'drinks',           'drink',   4),
 ('Fresh Produce',      'fresh-produce',    'leaf',    5),
 ('Household Essentials','household',        'home',    6),
 ('Meat & Frozen',      'meat-frozen',      'snow',    7),
 ('Snacks',             'snacks',           'snack',   8)
ON DUPLICATE KEY UPDATE name = VALUES(name);
