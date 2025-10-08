<?php
header('Content-Type: application/json');
$keys = require __DIR__ . '/../config/keys.php';

if (empty($keys['stripe_secret'])) {
    echo json_encode(['error' => 'Stripe secret key missing.']);
    exit;
}

$ch = curl_init("https://api.stripe.com/v1/setup_intents");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_USERPWD, $keys['stripe_secret'] . ":");

$response = curl_exec($ch);
if (!$response) {
    echo json_encode(['error' => 'cURL error: ' . curl_error($ch)]);
    exit;
}
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($httpCode);
echo $response;
