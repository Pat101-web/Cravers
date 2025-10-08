<?php
// config/db.php

$host = '138.201.204.31'; 
$db   = 'craversi_ikfoods';   // your real database name
$user = 'craversi_craver';         // default XAMPP username
$pass = 'f94U~Bd8^1KOsA#z';             // default XAMPP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    // in production, log this and show generic message
    exit('Database connection failed: ' . $e->getMessage());
}

