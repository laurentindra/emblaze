<?php
// API endpoint: check order status by order number
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

include 'koneksi.php';

$order_number = trim($_GET['order_id'] ?? '');
// Order number format: EMB-00042 → extract numeric part
$order_id = (int) preg_replace('/[^0-9]/', '', $order_number);

if (!$order_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid order ID']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT o.id, o.status, o.order_date, o.payment_method, o.shipping_method, o.total_price, o.shipping_address,
           COALESCE(SUM(oi.quantity), o.quantity, 0) AS item_count
    FROM orders o
    LEFT JOIN orders oi ON oi.id = o.id
    WHERE o.id = ? AND o.user_id = ?
    LIMIT 1
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    exit;
}

echo json_encode([
    'status'          => $order['status'],
    'order_date'      => date('d M Y, H:i', strtotime($order['order_date'])),
    'payment_method'  => $order['payment_method'],
    'shipping_method' => $order['shipping_method'],
    'total_price'     => 'Rp' . number_format($order['total_price'], 0, ',', '.'),
]);
