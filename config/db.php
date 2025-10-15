<?php
// config/db.php

require_once '../env.php';
loadEnv(__DIR__ . '/.env');


$host = getenv("DB_HOST"); 
$db   = getenv("DB_NAME");
$user = getenv("DB_USER"); 
$pass = getenv("DB_PASS");
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

