<?php
// public/payment_simulate.php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['pending_delivery_id']) || empty($_SESSION['delivery_verified'])) {
    header('Location: delivery.php');
    exit();
}

$delivery_id = (int)$_SESSION['pending_delivery_id'];
$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // simulate: mode_of_payment and status come from button pressed
    $mode = $_POST['mode'] ?? 'Cash';
    $status = $_POST['status'] ?? 'Pending';
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.00;

    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, delivery_id, mode_of_payment, payment_status, amount) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $delivery_id, $mode, $status, $amount]);
    $txn_id = $pdo->lastInsertId();

    // store last transaction id to view on transaction page
    $_SESSION['last_txn_id'] = $txn_id;

    header('Location: transaction.php');
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Payment - CRAVERS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .btn-orange { background: linear-gradient(90deg,#ff6600,#ff8a1a); color:#fff; border:none; border-radius:8px; padding:10px 14px; }
    .card { border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,0.06);}
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-7">
        <div class="card p-4">
          <h4 style="color:#ff6600">Simulated Payment</h4>
          <p class="text-muted">Choose a mode and result to simulate a transaction.</p>

          <form method="post">
            <div class="mb-3">
              <label class="form-label">Amount (NGN)</label>
              <input type="number" step="0.01" name="amount" class="form-control" value="1500">
            </div>

            <div class="mb-3">
              <label class="form-label">Mode of Payment</label>
              <select name="mode" class="form-select">
                <option>Cash on delivery</option>
                <option>Card (simulated)</option>
                <option>Mobile money</option>
              </select>
            </div>

            <div class="d-flex gap-2">
              <button class="btn btn-orange flex-fill" name="status" value="Success" type="submit">Simulate Success</button>
              <button class="btn btn-outline-secondary flex-fill" name="status" value="Pending" type="submit">Simulate Pending</button>
              <button class="btn btn-outline-danger flex-fill" name="status" value="Failed" type="submit">Simulate Failed</button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</body>
</html>
