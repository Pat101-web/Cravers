<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'vendor') { header('Location: login.php'); exit; }
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Vendor — Analytics</title>
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
    <h4 style="color:var(--orange)">Analytics</h4>
    <a href="vendor_dashboard.php" class="btn btn-outline-secondary">Back</a>
  </div>

  <div class="card-panel">
    <h5>Sales Overview</h5>
    <p class="text-muted">Quick charts and KPIs (replace with charts such as Chart.js)</p>
    <div class="row">
      <div class="col-md-4"><div class="p-3 bg-light rounded">Total Sales<br><strong>₦120,000</strong></div></div>
      <div class="col-md-4"><div class="p-3 bg-light rounded">Orders<br><strong>320</strong></div></div>
      <div class="col-md-4"><div class="p-3 bg-light rounded">Avg Order<br><strong>₦375</strong></div></div>
    </div>
  </div>
</div>
</body>
</html>
