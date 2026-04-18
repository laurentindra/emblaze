<?php
require 'auth.php';
include '../koneksi.php';

$action  = $_POST['action'] ?? '';
$user_id = intval($_POST['user_id'] ?? 0);

if ($action === 'delete' && $user_id) {
    // Prevent deleting the current admin
    if ($user_id === intval($_SESSION['admin_id'])) {
        $_SESSION['flash_error'] = "You cannot delete your own admin account.";
        header('Location: users.php');
        exit;
    }

    // Get username for message
    $row = $pdo->prepare("SELECT username, is_admin FROM users WHERE id = ?");
    $row->execute([$user_id]);
    $user = $row->fetch(PDO::FETCH_ASSOC);

    if ($user && !$user['is_admin']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
        $_SESSION['flash_success'] = "User <strong>" . htmlspecialchars($user['username']) . "</strong> has been deleted.";
    } else {
        $_SESSION['flash_error'] = "Cannot delete this user (admin or not found).";
    }
}

header('Location: users.php');
exit;
