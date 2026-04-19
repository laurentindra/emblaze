
<!DOCTYPE html>
<html>

<head><title>Upload Debug</title></head>
<body style="font-family:monospace; padding:20px;">

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Data:</h3><pre>" . print_r($_POST, true) . "</pre>";
    echo "<h3>FILES Data:</h3><pre>" . print_r($_FILES, true) . "</pre>";
// debugging upload doang ini, jangan diapa2in ya
    $uploadDir = __DIR__ . '/../../uploads/products/';
    echo "<h3>Upload Dir:</h3><p>" . realpath($uploadDir) . " (raw: $uploadDir)</p>";
    echo "<p>Dir exists: " . (is_dir($uploadDir) ? 'YES' : 'NO') . "</p>";
    echo "<p>Dir writable: " . (is_writable($uploadDir) ? 'YES' : 'NO') . "</p>";

    if (!empty($_FILES['image']['name'])) {
        echo "<h3>Trying to upload...</h3>";
        if (!is_dir($uploadDir)) {
            $mkres = mkdir($uploadDir, 0755, true);
            echo "<p>mkdir result: " . ($mkres ? 'OK' : 'FAILED') . "</p>";
        }
        $dest = $uploadDir . 'test_' . $_FILES['image']['name'];
        $res  = move_uploaded_file($_FILES['image']['tmp_name'], $dest);
        echo "<p>move_uploaded_file result: " . ($res ? 'SUCCESS → ' . $dest : 'FAILED') . "</p>";
    }
}
?>

<h2>Upload Test</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="image" accept="image/*"><br><br>
    <button type="submit">Test Upload</button>
</form>

<p style="color:gray; font-size:12px;">DELETE this file after debugging!</p>
</body>
</html>
