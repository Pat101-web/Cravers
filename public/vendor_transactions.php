<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'vendor') { header('Location: login.php'); exit; }
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Vendor — Transactions</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{--orange:#ff6600}
body{font-family:Poppins;background:#f6f6f7}
.container-wrap{max-width:1100px;margin:20px auto;padding:18px}
.card-panel{background:#fff;padding:18px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,0.06)}
</style>
</head>
<body>
<div class="container-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 style="color:var(--orange)">Transactions</h4>
    <a href="vendor_dashboard.php" class="btn btn-outline-secondary">Back</a>
  </div>

  <div class="card-panel">
    <h5>Transaction History</h5>
    <p class="text-muted">All transactions for reconciliation and refund tracking.</p>

    <table class="table">
      <thead><tr><th>Txn ID</th><th>Order</th><th>Amount</th><th>Gateway</th><th>Status</th></tr></thead>
      <tbody>
        <tr><td>T10234</td><td>#1201</td><td>₦2,400</td><td>Flutterwave</td><td><span class="badge bg-success">Success</span></td></tr>
        <tr><td>T10233</td><td>#1199</td><td>₦1,200</td><td>Paystack</td><td><span class="badge bg-danger">Failed</span></td></tr>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
