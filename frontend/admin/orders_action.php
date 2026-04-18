<?php
require 'auth.php';
include '../koneksi.php';

$order_id = intval($_POST['order_id'] ?? 0);
$status   = $_POST['status'] ?? '';
$redirect = $_POST['redirect_status'] ?? 'all';

$allowed = ['pending','processing','shipped','delivered','cancelled'];

if ($order_id && in_array($status, $allowed)) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    $_SESSION['flash_success'] = "Order EMB-" . str_pad($order_id, 5, '0', STR_PAD_LEFT) . " updated to <strong>" . ucfirst($status) . "</strong>.";
} else {
    $_SESSION['flash_error'] = "Invalid update request.";
}

$redirectUrl = 'orders.php' . ($redirect !== 'all' ? '?status=' . urlencode($redirect) : '');
header('Location: ' . $redirectUrl);
exit;
