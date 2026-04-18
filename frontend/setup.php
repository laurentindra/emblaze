<?php
// ============================================================
// EMBLAZE DATABASE SETUP — Run ONCE then delete this file
// Access: https://your-railway-url.up.railway.app/setup.php?key=emblaze_setup_2026
// ============================================================

if (($_GET['key'] ?? '') !== 'emblaze_setup_2026') {
    http_response_code(403);
    die('Forbidden. Provide ?key=emblaze_setup_2026');
}

include 'koneksi.php';

$sqls = [];

// ── USERS ──────────────────────────────────────────────────
$sqls[] = "CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Insert admin user (password: admin123)
$sqls[] = "INSERT IGNORE INTO `users` (`id`,`username`,`password`,`email`,`is_admin`) VALUES
(1, 'admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@emblaze.com', 1);";

// ── PRODUCTS ────────────────────────────────────────────────
$sqls[] = "CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `category` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$sqls[] = "INSERT IGNORE INTO `products` (`id`,`name`,`description`,`price`,`stock`,`category`,`image_url`) VALUES
(1, 'Vintage Tweed Blazer', 'Old money aesthetic blazer, premium wool.', 899000.00, 10, 'Outerwear', 'blazer.jpg'),
(2, 'Classic White Dress', 'Elegant white dress for formal events', 250000.00, 10, 'Dress', 'white_dress.jpg'),
(3, 'Minimalist Black Top', 'Casual black top for daily wear', 150000.00, 20, 'Tops', 'black_top.jpg');";

// ── ORDERS ──────────────────────────────────────────────────
$sqls[] = "CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `total_price` decimal(12,2) DEFAULT 0,
  `shipping_method` varchar(50) DEFAULT 'regular',
  `payment_method` varchar(50) DEFAULT 'transfer',
  `status` varchar(50) DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// ── EXECUTE ─────────────────────────────────────────────────
$errors = [];
$success = [];

foreach ($sqls as $i => $sql) {
    try {
        $pdo->exec($sql);
        $success[] = "Query " . ($i+1) . ": OK";
    } catch (PDOException $e) {
        $errors[] = "Query " . ($i+1) . " ERROR: " . $e->getMessage();
    }
}

echo "<!DOCTYPE html><html><body style='font-family:monospace;padding:30px;background:#111;color:#0f0'>";
echo "<h2 style='color:#9f1d2e'>Emblaze DB Setup</h2>";
foreach ($success as $s) echo "<p>✓ $s</p>";
foreach ($errors as $e) echo "<p style='color:#f66'>✗ $e</p>";
echo "<p style='color:#ff0;margin-top:20px'><strong>IMPORTANT: Delete this file (setup.php) from your repo after setup!</strong></p>";
echo "</body></html>";
