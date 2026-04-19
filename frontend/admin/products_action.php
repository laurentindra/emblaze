<?php
require 'auth.php';
include '../koneksi.php';

function handleImageUpload(string $fieldName, string $existing = ''): string {
    if (empty($_FILES[$fieldName]['name'])) {
        return $existing; // No new file → keep existing
    }

    $file = $_FILES[$fieldName];

    // Check PHP upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $phpErrors = [
            UPLOAD_ERR_INI_SIZE   => 'File too large (php.ini limit).',
            UPLOAD_ERR_FORM_SIZE  => 'File too large (form limit).',
            UPLOAD_ERR_PARTIAL    => 'File only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'Upload blocked by PHP extension.',
        ];
        throw new Exception($phpErrors[$file['error']] ?? 'Unknown upload error.');
    }

    $allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxBytes = 2 * 1024 * 1024; // 2 MB

    if (!in_array($file['type'], $allowed)) {
        throw new Exception('Only JPG, PNG, WEBP, GIF images are allowed.');
    }
    if ($file['size'] > $maxBytes) {
        throw new Exception('Image must be smaller than 2MB.');
    }

    // Save to uploads/products/ inside the frontend directory
    $uploadDir = __DIR__ . '/../uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('prod_', true) . '.' . strtolower($ext);
    $dest     = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new Exception('Failed to save image. Folder: ' . $uploadDir);
    }

    return 'uploads/products/' . $filename;
}

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $stock       = intval($_POST['stock'] ?? 0);
    $category    = trim($_POST['category'] ?? '');

    if (!$name || !$category || $price <= 0) {
        $_SESSION['flash_error'] = 'Name, category, and price are required.';
        header('Location: products.php');
        exit;
    }

    try {
        $image_url = handleImageUpload('image');
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Image error: ' . $e->getMessage();
        header('Location: products.php');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $description, $price, $stock, $category, $image_url]);
    $_SESSION['flash_success'] = "Product \"$name\" added successfully.";
    header('Location: products.php');
    exit;
}

if ($action === 'edit') {
    $id          = intval($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $stock       = intval($_POST['stock'] ?? 0);
    $category    = trim($_POST['category'] ?? '');
    $existing    = trim($_POST['existing_image'] ?? '');

    if (!$id || !$name || !$category) {
        $_SESSION['flash_error'] = 'Invalid data.';
        header('Location: products.php');
        exit;
    }

    try {
        $image_url = handleImageUpload('image', $existing);
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Image error: ' . $e->getMessage();
        header('Location: products.php');
        exit;
    }

    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category=?, image_url=? WHERE id=?");
    $stmt->execute([$name, $description, $price, $stock, $category, $image_url, $id]);
    $_SESSION['flash_success'] = "Product updated successfully.";
    header('Location: products.php');
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
        $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
        $_SESSION['flash_success'] = "Product deleted.";
    }
    header('Location: products.php');
    exit;
}


header('Location: products.php');
exit;
