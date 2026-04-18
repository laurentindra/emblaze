<?php
session_start();
include 'koneksi.php';

// Fetch all orders joined with user and product info
$stmt = $pdo->query("
    SELECT 
        o.id,
        o.order_date,
        o.quantity,
        o.total_price,
        o.shipping_method,
        o.payment_method,
        o.status,
        o.shipping_address,
        u.nama,
        u.username,
        u.email,
        p.name AS product_name,
        p.category,
        p.image_url
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN products p ON o.product_id = p.id
    ORDER BY o.order_date DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Recap | Emblaze</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Jost', sans-serif;
      background: #0e0a0b;
      color: #e8ddd5;
      min-height: 100vh;
    }

    header {
      padding: 20px 40px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .brand-logo { font-family: 'Cormorant Garamond', serif; font-size: 24px; color: #e8ddd5; }
    .brand-sub  { font-size: 10px; letter-spacing: 3px; color: #9a8878; text-transform: uppercase; }

    .page-title {
      padding: 40px 40px 10px;
      font-family: 'Cormorant Garamond', serif;
      font-size: 36px;
    }
    .page-title em { color: #9f1d2e; font-style: italic; }

    .stats-row {
      display: flex;
      gap: 16px;
      padding: 20px 40px;
      flex-wrap: wrap;
    }

    .stat-card {
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 12px;
      padding: 20px 28px;
      min-width: 160px;
    }

    .stat-label { font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #9a8878; }
    .stat-value { font-size: 28px; font-family: 'Cormorant Garamond', serif; margin-top: 6px; }

    .table-wrap {
      margin: 10px 40px 60px;
      overflow-x: auto;
      border-radius: 16px;
      border: 1px solid rgba(255,255,255,0.08);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    thead {
      background: rgba(159,29,46,0.15);
    }

    th {
      padding: 14px 18px;
      text-align: left;
      font-size: 10px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: #9a8878;
      white-space: nowrap;
    }

    td {
      padding: 14px 18px;
      border-top: 1px solid rgba(255,255,255,0.05);
      vertical-align: middle;
    }

    tr:hover td { background: rgba(255,255,255,0.02); }

    .product-cell {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .product-cell img {
      width: 44px;
      height: 44px;
      object-fit: cover;
      border-radius: 8px;
    }

    .product-name { font-weight: 500; color: #e8ddd5; }
    .product-cat  { font-size: 11px; color: #9a8878; margin-top: 2px; }

    .customer-name  { font-weight: 500; }
    .customer-email { font-size: 11px; color: #9a8878; margin-top: 2px; }

    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.5px;
    }

    .badge-pending    { background: rgba(255,193,7,0.15);  color: #ffc107; }
    .badge-processing { background: rgba(33,150,243,0.15); color: #42a5f5; }
    .badge-shipped    { background: rgba(156,39,176,0.15); color: #ce93d8; }
    .badge-delivered  { background: rgba(76,175,80,0.15);  color: #81c784; }
    .badge-cancelled  { background: rgba(244,67,54,0.15);  color: #e57373; }

    .price { font-family: 'Cormorant Garamond', serif; font-size: 16px; }

    .ship-method {
      font-size: 11px;
      text-transform: capitalize;
      color: #b8a898;
    }

    .empty-row td {
      text-align: center;
      padding: 60px;
      color: #9a8878;
      font-style: italic;
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: #9a8878;
      text-decoration: none;
      font-size: 13px;
      letter-spacing: 1px;
      transition: color 0.2s;
    }
    .back-link:hover { color: #e8ddd5; }

    @media (max-width: 768px) {
      header, .stats-row, .page-title, .table-wrap { padding-left: 20px; padding-right: 20px; }
      .table-wrap { margin-left: 20px; margin-right: 20px; }
    }
  </style>
</head>
<body>

<header>
  <div>
    <div class="brand-logo">Emblaze</div>
    <div class="brand-sub">Effortless Confidence</div>
  </div>
  <a href="index.php" class="back-link">← Back to Site</a>
</header>

<div class="page-title">Order <em>Recap</em></div>

<?php
  $total_orders   = count($orders);
  $total_revenue  = array_sum(array_column($orders, 'total_price'));
  $total_pending  = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
  $total_delivered = count(array_filter($orders, fn($o) => $o['status'] === 'delivered'));
?>

<div class="stats-row">
  <div class="stat-card">
    <div class="stat-label">Total Orders</div>
    <div class="stat-value"><?= $total_orders ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Total Revenue</div>
    <div class="stat-value">Rp<?= number_format($total_revenue, 0, ',', '.') ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Pending</div>
    <div class="stat-value"><?= $total_pending ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Delivered</div>
    <div class="stat-value"><?= $total_delivered ?></div>
  </div>
</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Product</th>
        <th>Customer</th>
        <th>Qty</th>
        <th>Total</th>
        <th>Shipping</th>
        <th>Payment</th>
        <th>Status</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($orders)): ?>
        <tr class="empty-row">
          <td colspan="9">No orders yet.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($orders as $o): ?>
        <tr>
          <td style="color:#9a8878;">#<?= $o['id'] ?></td>

          <td>
            <div class="product-cell">
              <?php if ($o['image_url']): ?>
                <img src="<?= htmlspecialchars($o['image_url']) ?>" alt="" />
              <?php endif; ?>
              <div>
                <div class="product-name"><?= htmlspecialchars($o['product_name'] ?? '-') ?></div>
                <div class="product-cat"><?= htmlspecialchars($o['category'] ?? '') ?></div>
              </div>
            </div>
          </td>

          <td>
            <div class="customer-name"><?= htmlspecialchars($o['nama'] ?? $o['username'] ?? '-') ?></div>
            <div class="customer-email"><?= htmlspecialchars($o['email'] ?? '') ?></div>
          </td>

          <td><?= $o['quantity'] ?></td>

          <td>
            <div class="price">Rp<?= number_format($o['total_price'], 0, ',', '.') ?></div>
          </td>

          <td>
            <div class="ship-method"><?= htmlspecialchars($o['shipping_method'] ?? '-') ?></div>
          </td>

          <td>
            <div class="ship-method"><?= htmlspecialchars($o['payment_method'] ?? '-') ?></div>
          </td>

          <td>
            <span class="badge badge-<?= $o['status'] ?>">
              <?= ucfirst($o['status']) ?>
            </span>
          </td>

          <td style="color:#9a8878; white-space:nowrap;">
            <?= date('d M Y, H:i', strtotime($o['order_date'])) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</body>
</html>