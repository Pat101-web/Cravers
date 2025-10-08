<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'vendor') { header('Location: login.php'); exit; }
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Vendor — Settings</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{--orange:#ff6600}
body{font-family:Poppins;background:#f6f6f7}
.container-wrap{max-width:900px;margin:20px auto;padding:18px}
.card-panel{background:#fff;padding:18px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,0.06)}
.toggle {display:inline-flex; align-items:center; gap:10px;}
.form-check-input {width:40px;height:20px;border-radius:20px;}
</style>
</head>
<body>
<div class="container-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 style="color:var(--orange)">Settings</h4>
    <a href="vendor_dashboard.php" class="btn btn-outline-secondary">Back</a>
  </div>

  <div class="card-panel">
    <h5>General</h5>
    <div class="row gy-3">
      <div class="col-12 col-md-6">
        <label class="form-label">Accept Online Orders</label>
        <div>
          <input class="form-check-input" type="checkbox" id="onlineOrders" checked>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label">Auto-confirm orders</label>
        <div><input class="form-check-input" type="checkbox" id="autoConfirm"></div>
      </div>
    </div>

    <hr>

    <h5>Notifications</h5>
    <div class="mb-3">
      <label class="form-label">Email notifications</label>
      <div><input class="form-check-input" type="checkbox" id="emailNotif" checked></div>
    </div>

    <div class="mt-3">
      <button class="btn btn-orange" id="saveSettings">Save Settings</button>
    </div>
  </div>
</div>

<script>
document.getElementById('saveSettings').addEventListener('click', function(){
  this.textContent = 'Saved';
  setTimeout(()=> this.textContent = 'Save Settings', 1200);
});
</script>
</body>
</html>
