<?php
// public/transaction.php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$txn_id = $_SESSION['last_txn_id'] ?? null;
if (!$txn_id) {
    echo "No transaction found. Go to the payment page.";
    exit();
}

$stmt = $pdo->prepare("SELECT t.*, d.name as delivery_name, d.phone as delivery_phone, d.address as delivery_address, d.delivery_code, d.created_at as delivery_created 
  FROM transactions t
  LEFT JOIN deliveries d ON t.delivery_id = d.id
  WHERE t.id = ?");
$stmt->execute([$txn_id]);
$tx = $stmt->fetch();

if (!$tx) {
    echo "Transaction not found.";
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Transaction - CRAVERS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>.badge-status { font-size:0.9rem; }</style>
</head>
<body>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8">
        <div class="card p-4">
          <h4 style="color:#ff6600">Transaction Details</h4>

          <div class="mt-3">
            <strong>Transaction ID:</strong> <?=htmlspecialchars($tx['id'])?><br>
            <strong>Amount:</strong> ₦<?=number_format($tx['amount'],2)?> <br>
            <strong>Mode:</strong> <?=htmlspecialchars($tx['mode_of_payment'])?> <br>
            <strong>Status:</strong>
            <?php
              $cls = 'secondary';
              if ($tx['payment_status'] === 'Success') $cls = 'success';
              if ($tx['payment_status'] === 'Pending') $cls = 'warning';
              if ($tx['payment_status'] === 'Failed') $cls = 'danger';
            ?>
            <span class="badge bg-<?=$cls?> badge-status"><?=htmlspecialchars($tx['payment_status'])?></span><br>
            <strong>Payment Date:</strong> <?=htmlspecialchars($tx['created_at'])?><br>
          </div>

          <hr>
          <h6>Delivery Info</h6>
          <div>
            <strong>Name:</strong> <?=htmlspecialchars($tx['delivery_name'])?><br>
            <strong>Phone:</strong> <?=htmlspecialchars($tx['delivery_phone'])?><br>
            <strong>Address:</strong> <?=nl2br(htmlspecialchars($tx['delivery_address']))?><br>
            <strong>Delivery Code (OTP):</strong> <?=htmlspecialchars($tx['delivery_code'])?><br>
            <strong>Delivery Created:</strong> <?=htmlspecialchars($tx['delivery_created'])?><br>
          </div>

          <div class="mt-3">
            <a href="home.php" class="btn btn-outline-secondary">Back to Home</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
