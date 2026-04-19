<?php
session_start();
include '../koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_admin = 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['is_admin']    = true;
        $_SESSION['admin_id']    = $admin['id'];
        $_SESSION['admin_name']  = $admin['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Email atau password salah, atau akun bukan admin.';
    }
}


if (!empty($_SESSION['is_admin'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login | Emblaze</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="admin.css" />
</head>
<body class="login-body">

  <div class="admin-login-wrap">
    <div class="admin-login-visual">
      <div class="login-visual-overlay">
        <div class="brand-logo-lg">Emblaze</div>
        <p class="brand-tagline">Admin Panel — Manage your store with elegance.</p>
      </div>
    </div>

    <div class="admin-login-form-panel">
      <div class="admin-login-box">
        <div class="admin-brand-badge">Admin</div>
        <h1 class="admin-login-title">Welcome, <em>Admin.</em></h1>
        <p class="admin-login-sub">Sign in to access the Emblaze admin panel.</p>

        <?php if ($error): ?>
          <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="admin_login.php" class="admin-form">
          <div class="form-group-admin">
            <label class="form-label-admin" for="email">Email Address</label>
            <div class="form-input-wrap-admin">
              <input class="form-input-admin" type="email" id="email" name="email" placeholder="admin@emblaze.com" required autocomplete="email" />
              <span class="input-icon-admin">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="m2 7 10 7 10-7"/></svg>
              </span>
            </div>
          </div>

          <div class="form-group-admin">
            <label class="form-label-admin" for="password">Password</label>
            <div class="form-input-wrap-admin">
              <input class="form-input-admin" type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password" />
              <span class="input-icon-admin" id="toggleAdminPwd" style="cursor:pointer;" onclick="togglePwd()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </span>
            </div>
          </div>

          <button type="submit" class="admin-login-btn" id="adminSubmitBtn">Sign In to Admin Panel</button>
        </form>

        <a href="../index.php" class="back-to-store">← Back to Store</a>
      </div>
    </div>
  </div>

<script>
function togglePwd() {
  const inp = document.getElementById('password');
  inp.type = inp.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
