<?php
session_start();
include 'koneksi.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'update_profile') {
        $newUsername = trim($_POST['username'] ?? '');
        $newEmail   = trim($_POST['email'] ?? '');
        $userId     = $_SESSION['user_id'];

        if (empty($newUsername) || empty($newEmail)) {
            echo json_encode(["success" => false, "message" => "Username and email are required."]);
            exit;
        }

        // Check if username/email already taken by another user
        $check = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $check->execute([$newUsername, $newEmail, $userId]);
        if ($check->fetch()) {
            echo json_encode(["success" => false, "message" => "Username or email is already taken."]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$newUsername, $newEmail, $userId]);
        $_SESSION['username'] = $newUsername;

        echo json_encode(["success" => true, "message" => "Profile updated successfully."]);
        exit;
    }

    if ($_POST['action'] === 'change_password') {
        $currentPwd = $_POST['current_password'] ?? '';
        $newPwd     = $_POST['new_password'] ?? '';
        $confirmPwd = $_POST['confirm_password'] ?? '';
        $userId     = $_SESSION['user_id'];

        if (empty($currentPwd) || empty($newPwd) || empty($confirmPwd)) {
            echo json_encode(["success" => false, "message" => "All password fields are required."]);
            exit;
        }

        if ($newPwd !== $confirmPwd) {
            echo json_encode(["success" => false, "message" => "New passwords do not match."]);
            exit;
        }

        if (strlen($newPwd) < 6) {
            echo json_encode(["success" => false, "message" => "Password must be at least 6 characters."]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($currentPwd, $user['password'])) {
            echo json_encode(["success" => false, "message" => "Current password is incorrect."]);
            exit;
        }

        $hashed = password_hash($newPwd, PASSWORD_BCRYPT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$hashed, $userId]);

        echo json_encode(["success" => true, "message" => "Password changed successfully."]);
        exit;
    }

    if ($_POST['action'] === 'logout') {
        session_destroy();
        echo json_encode(["success" => true]);
        exit;
    }
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch order count
$orderStmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
$orderStmt->execute([$_SESSION['user_id']]);
$orderCount = $orderStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch total spent — new schema: each orders row has total_price
$spentStmt = $pdo->prepare("
    SELECT COALESCE(SUM(total_price), 0) as total_spent
    FROM orders
    WHERE user_id = ?
");
$spentStmt->execute([$_SESSION['user_id']]);
$totalSpent = $spentStmt->fetch(PDO::FETCH_ASSOC)['total_spent'];

$memberSince = date('F j, Y', strtotime($user['created_at']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Profile | Emblaze</title>
  <meta name="description" content="Manage your Emblaze account profile, view order history, and update your settings." />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="profile.css" />
</head>
<body>

  <header>
    <div class="container navbar">
      <a href="index.php">
        <div class="brand-logo">Emblaze</div>
        <div class="brand-sub">Effortless Confidence</div>
      </a>

      <nav class="nav-links" id="mobileMenu">
        <a href="index.php">Home</a>
        <a href="index.php#collection">Collection</a>
        <a href="index.php#about">About</a>
        <a href="products.php">Products</a>
        <a href="index.php#contact">Contact</a>
      </nav>

      <div style="display:flex; align-items:center; gap:12px;">
        <a href="index.php#collection" class="btn btn-secondary desktop-only">Shop Now</a>
        <a href="cart.php" class="login-icon" title="Cart">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
        </a>
        <a href="profile.php" class="login-icon active-profile" title="Profile">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="8" r="4"/>
            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
          </svg>
        </a>
        <button class="menu-toggle" id="menuToggle">☰</button>
      </div>
    </div>
  </header>

  <main class="profile-page">
    <div class="container">

      <!-- Profile Header -->
      <div class="profile-header glass fade-in">
        <div class="profile-avatar">
          <div class="avatar-circle">
            <span id="avatarInitial"><?= strtoupper(substr($user['username'], 0, 1)) ?></span>
          </div>
          <div class="profile-status-badge">Active</div>
        </div>
        <div class="profile-header-info">
          <h1 class="profile-name"><?= htmlspecialchars($user['username']) ?></h1>
          <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
          <p class="profile-member-since">Member since <?= $memberSince ?></p>
        </div>
        <a href="logout.php" class="btn-logout" id="logoutBtn" onclick="return confirm('Are you sure you want to sign out?')">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          Sign Out
        </a>
      </div>

      <!-- Stats Cards -->
      <div class="profile-stats fade-in" style="animation-delay: 0.1s">
        <div class="stat-card glass">
          <div class="stat-info">
            <h3><?= $orderCount ?></h3>
            <p>Total Orders</p>
          </div>
        </div>
        <div class="stat-card glass">
          <div class="stat-info">
            <h3>Rp<?= number_format($totalSpent, 0, ',', '.') ?></h3>
            <p>Total Spent</p>
          </div>
        </div>
        <div class="stat-card glass">
          <div class="stat-info">
            <h3><?= $memberSince ?></h3>
            <p>Member Since</p>
          </div>
        </div>
      </div>

      <!-- Profile Tabs -->
      <div class="profile-tabs fade-in" style="animation-delay: 0.15s">
        <button class="profile-tab active" data-tab="account">Account Details</button>
        <button class="profile-tab" data-tab="security">Security</button>
        <button class="profile-tab" data-tab="orders">Order History</button>
      </div>

      <!-- Form Message -->
      <div class="form-message" id="formMessage"></div>

      <!-- Tab: Account Details -->
      <div class="tab-content active" id="tab-account">
        <div class="profile-card glass fade-in" style="animation-delay: 0.2s">
          <h2 class="card-title">Account Details</h2>
          <p class="card-subtitle">Update your personal information</p>

          <form id="profileForm" autocomplete="off">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="profileUsername">Username</label>
                <div class="form-input-wrap">
                  <input class="form-input" type="text" id="profileUsername" value="<?= htmlspecialchars($user['username']) ?>" required />
                  <span class="input-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                  </span>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="profileEmail">Email Address</label>
                <div class="form-input-wrap">
                  <input class="form-input" type="email" id="profileEmail" value="<?= htmlspecialchars($user['email']) ?>" required />
                  <span class="input-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="m2 7 10 7 10-7"/></svg>
                  </span>
                </div>
              </div>
            </div>

            <button type="submit" class="btn-save" id="saveProfileBtn">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
              Save Changes
            </button>
          </form>
        </div>
      </div>

      <!-- Tab: Security -->
      <div class="tab-content" id="tab-security">
        <div class="profile-card glass fade-in">
          <h2 class="card-title">Change Password</h2>
          <p class="card-subtitle">Keep your account secure with a strong password</p>

          <form id="passwordForm" autocomplete="off">
            <div class="form-group">
              <label class="form-label" for="currentPassword">Current Password</label>
              <div class="form-input-wrap">
                <input class="form-input" type="password" id="currentPassword" placeholder="••••••••" required />
                <span class="input-icon toggle-pwd" data-target="currentPassword">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </span>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="newPassword">New Password</label>
                <div class="form-input-wrap">
                  <input class="form-input" type="password" id="newPassword" placeholder="••••••••" required />
                  <span class="input-icon toggle-pwd" data-target="newPassword">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </span>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="confirmNewPassword">Confirm New Password</label>
                <div class="form-input-wrap">
                  <input class="form-input" type="password" id="confirmNewPassword" placeholder="••••••••" required />
                  <span class="input-icon toggle-pwd" data-target="confirmNewPassword">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </span>
                </div>
              </div>
            </div>

            <button type="submit" class="btn-save" id="savePasswordBtn">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              Update Password
            </button>
          </form>
        </div>
      </div>

      <!-- Tab: Orders -->
      <div class="tab-content" id="tab-orders">
        <div class="profile-card glass fade-in">
          <h2 class="card-title">Order History</h2>
          <p class="card-subtitle">View your past purchases</p>

          <?php
          $ordersQuery = $pdo->prepare("
              SELECT id, order_date, status,
                     COALESCE(total_price, 0) as total,
                     payment_method, shipping_method
              FROM orders
              WHERE user_id = ?
              ORDER BY order_date DESC
          ");
          $ordersQuery->execute([$_SESSION['user_id']]);
          $orders = $ordersQuery->fetchAll(PDO::FETCH_ASSOC);

          if (empty($orders)): ?>
            <div class="empty-orders">
              <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
              <h3>No orders yet</h3>
              <p>Start shopping to see your order history here.</p>
              <a href="products.php" class="btn-shop">Browse Products</a>
            </div>
          <?php else: ?>
            <div class="orders-list">
              <?php foreach ($orders as $order): ?>
                <div class="order-row">
                  <div class="order-id">
                    <span class="order-label">Order</span>
                    <span class="order-number">EMB-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></span>
                  </div>
                  <div class="order-date"><?= date('M j, Y', strtotime($order['order_date'])) ?></div>
                  <div class="order-items"><?= ucfirst($order['payment_method'] ?? 'transfer') ?></div>
                  <div class="order-total">Rp<?= number_format($order['total'], 0, ',', '.') ?></div>
                  <div class="order-status">
                    <span class="status-badge status-<?= strtolower($order['status']) ?>"><?= ucfirst($order['status']) ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </main>

  <footer>
    <div class="container">© 2026 Emblaze. Designed with soft minimal elegance.</div>
  </footer>

  <script src="profile.js"></script>
</body>
</html>
