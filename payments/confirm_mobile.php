<?php
// payments/confirm_mobile.php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php?redirect=../public/checkout.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/checkout.php");
    exit;
}

$userId = $_SESSION['user_id'];
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$receiptPath = null;

if (!empty($_FILES['receipt']['name'])) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $safe = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['receipt']['name']));
    $target = $uploadDir . $safe;
    if (move_uploaded_file($_FILES['receipt']['tmp_name'], $target)) {
        $receiptPath = 'payments/uploads/' . $safe;
    }
}

// Insert transaction (status pending)
$stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, method, status, receipt, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->execute([$userId, $amount, 'mobile', 'pending', $receiptPath]);

header("Location: ../public/history.php");
exit;
