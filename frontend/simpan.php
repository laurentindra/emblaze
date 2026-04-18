<?php
session_start();
include 'koneksi.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Please login first."]);
    exit;
}

// Karena cart.js kirim data pake JSON.stringify, kita ambilnya pake php://input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    // Di sini kita asumsikan kamu punya tabel bernama 'orders'
    // Kalau nama tabelmu beda, tinggal ganti 'orders' di bawah ini ya!
    $stmt = $pdo->prepare("INSERT INTO orders (items, total_price, order_date) VALUES (?, ?, NOW())");
    
    // Kita simpan list barangnya jadi teks (JSON) biar gampang
    $itemsString = json_encode($data['items']); 
    $totalPrice = $data['totalPrice'];

    if ($stmt->execute([$itemsString, $totalPrice])) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal masuk ke database"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Data kosong atau format salah"]);
}
?>