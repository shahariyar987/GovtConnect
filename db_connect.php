<?php
// db_connect.php
// Put this file in the project root (same folder as your other php files)

// Database settings live in config.php (not uploaded to GitHub).
// Copy config.example.php to config.php and put your own MySQL password there.
require __DIR__ . (file_exists(__DIR__ . "/config.php") ? "/config.php" : "/config.example.php");

$host = DB_HOST;
$db   = DB_NAME;
$user = DB_USER;
$pass = DB_PASS;
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // In dev you can show error; in production log instead.
    die("Database connection failed: " . $e->getMessage());
}
