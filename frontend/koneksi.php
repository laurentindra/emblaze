<?php
// Supports both local XAMPP and cloud hosting via env vars
$host   = getenv('DB_HOST')   ?: 'localhost';
$dbname = getenv('DB_NAME')   ?: 'db_webdev';
$user   = getenv('DB_USER')   ?: 'root';
$pass   = getenv('DB_PASS')   ?: '';
$port   = getenv('DB_PORT')   ?: '3306';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    // Return JSON error if called from AJAX, otherwise plain message
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
        header('Content-Type: application/json');
        die(json_encode(["error" => "Database connection failed."]));
    }
    die("<p style='font-family:sans-serif;color:#c00'>Database connection failed. Please check your configuration.</p>");
}
?>