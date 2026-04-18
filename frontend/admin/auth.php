<?php
// auth.php — Admin session guard
// Include file ini di setiap halaman admin
session_start();
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin_login.php');
    exit;
}
