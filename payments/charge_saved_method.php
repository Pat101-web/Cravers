<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';
$keys = require __DIR__ . '/../config/keys.php';

\Stripe\Stripe::setApiKey($keys['stripe_secret']);

// get user's saved payment method
$pdo = new PDO("mysql:host=localhost;dbname=ikfoods", "root", "");
$stmt = $pdo->prepare("SELECT payment_method_id FROM user_cards WHERE user_id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$card = $stmt->fetch();

if (!$card) {
    echo json_encode(['success' => false, 'message' => 'No saved card found']);
    exit;
}

// charge the saved card
$paymentIntent = \Stripe\PaymentIntent::create([
    'amount' => 2000, // amount in kobo (₦20.00 for example)
    'currency' => 'usd',
    'payment_method' => $card['payment_method_id'],
    'customer' => $_SESSION['stripe_customer_id'],
    'off_session' => true,
    'confirm' => true,
]);

echo json_encode(['success' => true, 'id' => $paymentIntent->id]);
