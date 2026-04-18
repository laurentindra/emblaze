<?php
if (($_GET['key'] ?? '') !== 'emblaze_setup_2026') { http_response_code(403); die('Forbidden'); }
include 'koneksi.php';
$rows = $pdo->query("SELECT id, username, email, is_admin, created_at FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
echo "<style>body{font-family:monospace;background:#111;color:#0f0;padding:20px} table{border-collapse:collapse} td,th{border:1px solid #333;padding:8px 12px} th{color:#9f1d2e}</style>";
echo "<h2 style='color:#9f1d2e'>Users in Railway DB</h2>";
echo "<table><tr><th>ID</th><th>Username</th><th>Email</th><th>is_admin</th><th>created_at</th></tr>";
foreach ($rows as $r) {
    echo "<tr><td>{$r['id']}</td><td>{$r['username']}</td><td>{$r['email']}</td><td>{$r['is_admin']}</td><td>{$r['created_at']}</td></tr>";
}
echo "</table><p style='color:#ff0'>Total: ".count($rows)." users</p>";
