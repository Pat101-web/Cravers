<?php
// payments/save_payment_method.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
$keys = require __DIR__ . '/../config/keys.php';

// Basic checks
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}
$userId = $_SESSION['user_id'];
$paymentMethod = $_POST['payment_method'] ?? null;
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

if (!$paymentMethod) {
    echo json_encode(['success' => false, 'message' => 'No payment method provided']);
    exit;
}
if (empty($keys['stripe_secret'])) {
    echo json_encode(['success' => false, 'message' => 'Missing Stripe secret key']);
    exit;
}

// 1) create a Stripe customer (optional — keeps things tidy)
$ch = curl_init('https://api.stripe.com/v1/customers');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);

// find user email if possible
$userEmail = null;
try {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) $userEmail = $row['email'];
} catch (Exception $e) {
    // ignore, proceed without email
}
$postFields = [];
if ($userEmail) $postFields['email'] = $userEmail;
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
curl_setopt($ch, CURLOPT_USERPWD, $keys['stripe_secret'] . ':');
$customerResp = curl_exec($ch);
$customerCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($customerCode < 200 || $customerCode >= 300) {
    echo json_encode(['success' => false, 'message' => 'Failed to create Stripe customer']);
    exit;
}
$customerJson = json_decode($customerResp, true);
$customerId = $customerJson['id'] ?? null;
if (!$customerId) {
    echo json_encode(['success' => false, 'message' => 'Invalid customer response from Stripe']);
    exit;
}

// 2) attach payment method to customer
$ch = curl_init("https://api.stripe.com/v1/payment_methods/{$paymentMethod}/attach");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['customer' => $customerId]));
curl_setopt($ch, CURLOPT_USERPWD, $keys['stripe_secret'] . ':');
$attachResp = curl_exec($ch);
$attachCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($attachCode < 200 || $attachCode >= 300) {
    $err = json_decode($attachResp, true);
    $msg = $err['error']['message'] ?? 'Failed to attach card';
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

// 3) Save to local DB: user_cards + transactions
try {
    // user_cards table insert
    $stmt = $pdo->prepare("INSERT INTO user_cards (user_id, stripe_payment_method_id, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$userId, $paymentMethod]);

    // transactions table insert (mark as 'paid' or 'completed' — you can adjust)
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, method, status, receipt, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $amount, 'card', 'paid', null]);

    echo json_encode(['success' => true, 'message' => 'Card saved and payment recorded']);
    exit;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    exit;
}
