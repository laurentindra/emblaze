-- ============================================================
-- Emblaze Admin Panel — Database Upgrade Script
-- Jalankan di phpMyAdmin sebelum menggunakan admin panel
-- ============================================================

-- 1. Tambah kolom is_admin ke tabel users
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `is_admin` TINYINT(1) NOT NULL DEFAULT 0;

-- 2. Set user pertama (id=1) sebagai admin
UPDATE `users` SET `is_admin` = 1 WHERE `id` = 1;

-- 3. Upgrade tabel orders agar sesuai dengan order.php baru
--    (tambah kolom yang mungkin belum ada)
ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `product_id` INT(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `quantity` INT(11) DEFAULT 1,
  ADD COLUMN IF NOT EXISTS `total_price` DECIMAL(12,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `shipping_method` VARCHAR(50) DEFAULT 'regular',
  ADD COLUMN IF NOT EXISTS `payment_method` VARCHAR(50) DEFAULT 'transfer',
  ADD COLUMN IF NOT EXISTS `shipping_address` TEXT DEFAULT NULL;

-- Selesai!
