<?php
require 'auth.php';
include '../koneksi.php';

$success = $_SESSION['flash_success'] ?? '';
$errMsg  = $_SESSION['flash_error']   ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);


$filterStatus = $_GET['status'] ?? 'all';
$search       = trim($_GET['q'] ?? '');

$where  = [];
$params = [];

if ($filterStatus !== 'all') {
    $where[]  = "o.status = ?";
    $params[] = $filterStatus;
}
if ($search) {
    $where[]  = "(u.username LIKE ? OR o.id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$orders = $pdo->prepare("
    SELECT o.id, u.username, u.email, o.order_date, o.status,
           COALESCE(o.total_price, 0) AS total_price,
           o.shipping_method, o.payment_method, o.shipping_address
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    $whereSQL
    ORDER BY o.order_date DESC
");
$orders->execute($params);
$orders = $orders->fetchAll(PDO::FETCH_ASSOC);

$allStatuses = ['pending','processing','shipped','delivered','cancelled'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Orders | Emblaze Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="admin.css" />
</head>
<body class="admin-body">

<?php include 'sidebar.php'; ?>

<div class="admin-main">
  <div class="admin-topbar">
    <div class="topbar-left">
      <button class="sidebar-toggle-btn" onclick="toggleSidebar()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <div class="topbar-breadcrumb">
        <span class="breadcrumb-root">Admin</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Orders</span>
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
      <h1 class="page-title">Order <em>Management</em></h1>
      <p class="page-sub"><?= count($orders) ?> orders <?= $filterStatus !== 'all' ? "with status <strong>" . ucfirst($filterStatus) . "</strong>" : 'total' ?></p>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="flash-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($errMsg): ?>
    <div class="flash-error"><?= htmlspecialchars($errMsg) ?></div>
  <?php endif; ?>

  
  <div class="status-tabs">
    <a href="orders.php" class="status-tab <?= $filterStatus === 'all' ? 'active' : '' ?>">All</a>
    <?php foreach ($allStatuses as $s): ?>
    <a href="orders.php?status=<?= $s ?>" class="status-tab status-tab-<?= $s ?> <?= $filterStatus === $s ? 'active' : '' ?>">
      <?= ucfirst($s) ?>
    </a>
    <?php endforeach; ?>
  </div>

 
  <div class="toolbar-admin">
    <form method="GET" action="orders.php" style="display:flex; gap:10px; flex:1">
      <?php if ($filterStatus !== 'all'): ?>
        <input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>">
      <?php endif; ?>
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="search-input-admin" placeholder="Search by username or order ID..." />
      <button type="submit" class="btn-admin-primary" style="min-width:90px">Search</button>
      <?php if ($search): ?>
        <a href="orders.php<?= $filterStatus !== 'all' ? '?status='.$filterStatus : '' ?>" class="btn-admin-secondary">Clear</a>
      <?php endif; ?>
    </form>
  </div>


  <div class="table-card glass-admin">
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Email</th>
            <th>Date</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Shipping</th>
            <th>Status</th>
            <th>Update Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($orders)): ?>
            <tr><td colspan="9" class="empty-admin">No orders found.</td></tr>
          <?php else: ?>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td><span class="order-id-badge">EMB-<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></span></td>
            <td><?= htmlspecialchars($o['username'] ?? '—') ?></td>
            <td class="muted-cell"><?= htmlspecialchars($o['email'] ?? '—') ?></td>
            <td><?= date('d M Y, H:i', strtotime($o['order_date'])) ?></td>
            <td><strong>Rp<?= number_format($o['total_price'], 0, ',', '.') ?></strong></td>
            <td><?= htmlspecialchars(ucfirst($o['payment_method'] ?? '—')) ?></td>
            <td><?= htmlspecialchars(ucfirst($o['shipping_method'] ?? '—')) ?></td>
            <td><span class="status-pill status-<?= strtolower($o['status']) ?>"><?= ucfirst($o['status']) ?></span></td>
            <td>
              <form method="POST" action="orders_action.php" style="display:flex; gap:6px; align-items:center">
                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                <input type="hidden" name="redirect_status" value="<?= htmlspecialchars($filterStatus) ?>">
                <select name="status" class="status-select-admin">
                  <?php foreach ($allStatuses as $s): ?>
                    <option value="<?= $s ?>" <?= strtolower($o['status']) === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="table-action-btn table-action-edit">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="admin.js"></script>
</body>
</html>
