<?php
// public/verify_otp.php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['pending_delivery_id'])) {
    // no pending delivery, go to delivery form
    header('Location: delivery.php');
    exit();
}

$delivery_id = (int)$_SESSION['pending_delivery_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    if (!$code) {
        $error = 'Enter the 6-digit delivery code.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM deliveries WHERE id = ? AND user_id = ?");
        $stmt->execute([$delivery_id, $_SESSION['user_id']]);
        $delivery = $stmt->fetch();

        if (!$delivery) {
            $error = 'Delivery not found.';
        } else {
            if ($delivery['delivery_code'] === $code) {
                // mark verified
                $u = $pdo->prepare("UPDATE deliveries SET otp_verified = 1 WHERE id = ?");
                $u->execute([$delivery_id]);

                // now allow payment
                $_SESSION['delivery_verified'] = true;
                header('Location: payment_simulate.php');
                exit();
            } else {
                $error = 'Incorrect delivery code.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Verify Delivery Code - CRAVERS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: linear-gradient(135deg, #fff8f3, #ffffff 60%); }
    .btn-orange { background: linear-gradient(90deg,#ff6600,#ff8a1a); color:#fff; border:none; border-radius:8px; padding:10px 14px; }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-6">
        <div class="card p-4">
          <h5 class="text-orange" style="color:#ff6600">Verify Delivery Code</h5>
          <p class="text-muted">Enter the 6-digit code sent to you (this is your delivery code/OTP).</p>

          <?php if ($error): ?>
            <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
          <?php endif; ?>

          <form method="post">
            <div class="mb-3">
              <label class="form-label">Delivery Code (OTP)</label>
              <input type="text" name="code" class="form-control" maxlength="6" required>
            </div>
            <div class="d-grid">
              <button class="btn btn-orange" type="submit">Verify & Continue to Payment</button>
            </div>
          </form>

          <div class="mt-3 small text-muted">
            If you didn't receive a code, go back and submit the delivery form again or contact support.
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
