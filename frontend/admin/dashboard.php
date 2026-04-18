<?php
require 'auth.php';
include '../../koneksi.php';

//p p apa? products blayy , piann sungguh mempesona
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();


$revRow = $pdo->query("SELECT COALESCE(SUM(total_price),0) AS rev FROM orders")->fetch(PDO::FETCH_ASSOC);
$totalRevenue = $revRow['rev'] ?? 0;

//ini apa? orderan
$latestOrders = $pdo->query("
    SELECT o.id, u.username, o.order_date, o.status,
           COALESCE(o.total_price, 0) AS total_price
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    ORDER BY o.order_date DESC
    LIMIT 7
")->fetchAll(PDO::FETCH_ASSOC);

//ini apdet prodak ya,konek into db
$lowStock = $pdo->query("SELECT * FROM products WHERE stock <= 5 ORDER BY stock ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);


$statusBreakdown = $pdo->query("
    SELECT status, COUNT(*) as cnt FROM orders GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);
$statusMap = [];
foreach ($statusBreakdown as $sb) {
    $statusMap[strtolower($sb['status'])] = $sb['cnt'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard | Emblaze Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="admin.css" />
</head>
<body class="admin-body">

<?php include 'sidebar.php'; ?>

<div class="admin-main">

  <div class="admin-topbar">
    <div class="topbar-left">
      <button class="sidebar-toggle-btn" id="sidebarToggle" onclick="toggleSidebar()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <div class="topbar-breadcrumb">
        <span class="breadcrumb-root">Admin</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Dashboard</span>
      </div>
    </div>
    <div class="topbar-right">
      <div class="admin-avatar-wrap">
        <div class="admin-avatar"><?= strtoupper(substr($_SESSION['admin_name'], 0, 1)) ?></div>
        <span class="admin-name-label"><?= htmlspecialchars($_SESSION['admin_name']) ?></span>
      </div>
    </div>
  </div>


  <div class="page-header">
    <div>
      <h1 class="page-title">Dashboard <em>Overview</em></h1>
      <p class="page-sub">Welcome back, <?= htmlspecialchars($_SESSION['admin_name']) ?>. Here's what's happening.</p>
    </div>
    <div class="page-header-date"><?= date('l, d F Y') ?></div>
  </div>

  
  <div class="stats-grid">
    <!-- Total Products -->
    <div class="stat-card-admin glass-admin">
      <div class="stat-icon-plain">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 3H8v4h8V3Z"/><path d="M16 17v4H8v-4"/></svg>
      </div>
      <div class="stat-content">
        <div class="stat-label">Total Products</div>
        <div class="stat-value"><?= number_format($totalProducts) ?></div>
        <div class="stat-hint">Active catalog items</div>
      </div>
    </div>

    <!-- Registered Users -->
    <div class="stat-card-admin glass-admin">
      <div class="stat-icon-plain">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="17" cy="7" r="3"/><path d="M12 20v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      </div>
      <div class="stat-content">
        <div class="stat-label">Registered Users</div>
        <div class="stat-value"><?= number_format($totalUsers) ?></div>
        <div class="stat-hint">Excluding admins</div>
      </div>
    </div>

    <!-- Total Orders -->
    <div class="stat-card-admin glass-admin">
      <div class="stat-icon-plain">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      </div>
      <div class="stat-content">
        <div class="stat-label">Total Orders</div>
        <div class="stat-value"><?= number_format($totalOrders) ?></div>
        <div class="stat-hint">All time orders</div>
      </div>
    </div>

    <!-- Total Revenue -->
    <div class="stat-card-admin glass-admin">
      <div class="stat-icon-plain">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div class="stat-content">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value">Rp<?= number_format($totalRevenue, 0, ',', '.') ?></div>
        <div class="stat-hint">All time revenue</div>
      </div>
    </div>
  </div>

  <!-- Order Status Breakdown -->
  <div class="dashboard-grid">
    <div class="dashboard-card glass-admin">
      <div class="card-header-admin">
        <h3 class="card-title-admin">Order Status</h3>
      </div>
      <div class="status-breakdown">
        <?php
        $statuses = [
          'pending'    => ['label' => 'Pending',    'color' => 'status-pending'],
          'processing' => ['label' => 'Processing', 'color' => 'status-processing'],
          'shipped'    => ['label' => 'Shipped',    'color' => 'status-shipped'],
          'delivered'  => ['label' => 'Delivered',  'color' => 'status-delivered'],
          'cancelled'  => ['label' => 'Cancelled',  'color' => 'status-cancelled'],
        ];
        foreach ($statuses as $key => $meta):
          $cnt = $statusMap[$key] ?? 0;
          $pct = $totalOrders > 0 ? round(($cnt / $totalOrders) * 100) : 0;
        ?>
        <div class="breakdown-row">
          <div class="breakdown-label">
            <span class="status-dot <?= $meta['color'] ?>"></span>
            <?= $meta['label'] ?>
          </div>
          <div class="breakdown-bar-wrap">
            <div class="breakdown-bar" style="width:<?= $pct ?>%"></div>
          </div>
          <span class="breakdown-count"><?= $cnt ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="dashboard-card glass-admin">
      <div class="card-header-admin">
        <h3 class="card-title-admin">Low Stock Alert</h3>
        <a href="products.php" class="card-link-admin">View All →</a>
      </div>
      <?php if (empty($lowStock)): ?>
        <p class="empty-admin">All products have sufficient stock.</p>
      <?php else: ?>
      <table class="mini-table">
        <thead><tr><th>Product</th><th>Category</th><th>Stock</th></tr></thead>
        <tbody>
          <?php foreach ($lowStock as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><span class="badge-cat"><?= htmlspecialchars($p['category']) ?></span></td>
            <td>
              <span class="stock-badge <?= $p['stock'] == 0 ? 'stock-out' : 'stock-low' ?>">
                <?= $p['stock'] == 0 ? 'Out of Stock' : $p['stock'] . ' left' ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Latest Orders Table -->
  <div class="table-card glass-admin">
    <div class="card-header-admin">
      <h3 class="card-title-admin">Recent Orders</h3>
      <a href="orders.php" class="card-link-admin">View All Orders →</a>
    </div>
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Date</th>
            <th>Total</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($latestOrders)): ?>
            <tr><td colspan="6" class="empty-admin">No orders yet.</td></tr>
          <?php else: ?>
          <?php foreach ($latestOrders as $o): ?>
          <tr>
            <td><span class="order-id-badge">EMB-<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></span></td>
            <td><?= htmlspecialchars($o['username'] ?? '—') ?></td>
            <td><?= date('d M Y, H:i', strtotime($o['order_date'])) ?></td>
            <td>Rp<?= number_format($o['total_price'], 0, ',', '.') ?></td>
            <td><span class="status-pill status-<?= strtolower($o['status']) ?>"><?= ucfirst($o['status']) ?></span></td>
            <td><a href="orders.php?id=<?= $o['id'] ?>" class="table-action-btn">Detail</a></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div><!-- /.admin-main -->

<script src="admin.js"></script>
</body>
</html>
