<?php
require 'auth.php';
include '../koneksi.php';

$success = $_SESSION['flash_success'] ?? '';
$errMsg  = $_SESSION['flash_error']   ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$search = trim($_GET['q'] ?? '');
$params = [];
$whereSQL = '';
if ($search) {
    $whereSQL = "WHERE username LIKE ? OR email LIKE ?";
    $params   = ["%$search%", "%$search%"];
}

$stmt = $pdo->prepare("SELECT * FROM users $whereSQL ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalUsers = count($users);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Users | Emblaze Admin</title>
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
        <span class="breadcrumb-current">Users</span>
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
      <h1 class="page-title">User <em>Management</em></h1>
      <p class="page-sub"><?= $totalUsers ?> users found</p>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="flash-success"><?= $success ?></div>
  <?php endif; ?>
  <?php if ($errMsg): ?>
    <div class="flash-error"><?= htmlspecialchars($errMsg) ?></div>
  <?php endif; ?>

  <!-- Search -->
  <div class="toolbar-admin">
    <form method="GET" action="users.php" style="display:flex; gap:10px; flex:1">
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="search-input-admin" placeholder="Search by username or email..." />
      <button type="submit" class="btn-admin-primary" style="min-width:90px">Search</button>
      <?php if ($search): ?>
        <a href="users.php" class="btn-admin-secondary">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  
  <div class="table-card glass-admin">
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Avatar</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Joined</th>
            <th>Orders</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($users)): ?>
            <tr><td colspan="8" class="empty-admin">No users found.</td></tr>
          <?php else: ?>
          <?php foreach ($users as $i => $u):
          
            $orderCount = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
            $orderCount->execute([$u['id']]);
            $oCount = $orderCount->fetchColumn();
          ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td>
              <div class="user-avatar-mini"><?= strtoupper(substr($u['username'], 0, 2)) ?></div>
            </td>
            <td>
              <div class="user-name-cell"><?= htmlspecialchars($u['username']) ?></div>
            </td>
            <td class="muted-cell"><?= htmlspecialchars($u['email']) ?></td>
            <td>
              <?php if (!empty($u['is_admin']) && $u['is_admin'] == 1): ?>
                <span class="role-badge role-admin">Admin</span>
              <?php else: ?>
                <span class="role-badge role-user">User</span>
              <?php endif; ?>
            </td>
            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td>
              <span class="order-count-badge"><?= $oCount ?> orders</span>
            </td>
            <td>
              <?php if (!empty($u['is_admin']) && $u['is_admin'] == 1): ?>
                <span class="muted-cell">— admin —</span>
              <?php else: ?>
                <form method="POST" action="users_action.php" onsubmit="return confirm('Delete user \'<?= addslashes($u['username']) ?>\'? This cannot be undone.')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <button type="submit" class="table-action-btn table-action-delete">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    Delete
                  </button>
                </form>
              <?php endif; ?>
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
