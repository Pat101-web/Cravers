<?php
// public/delivery.php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize minimal; add server-side validation as needed
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $landmark = trim($_POST['landmark'] ?? '');
    $delivery_time = trim($_POST['delivery_time'] ?? '');
    $nickname = trim($_POST['nickname'] ?? '');

    if (!$name || !$phone || !$address) {
        $error = 'Please fill the required fields: Name, Phone, Address.';
    } else {
        // generate unique 6-digit code. Prefix optional 'CR'
        $delivery_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // ensure uniqueness (loop if collides)
        $try = 0;
        while ($try < 5) {
            $stmtCheck = $pdo->prepare("SELECT id FROM deliveries WHERE delivery_code = ?");
            $stmtCheck->execute([$delivery_code]);
            if (!$stmtCheck->fetch()) break;
            $delivery_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $try++;
        }

        $stmt = $pdo->prepare("INSERT INTO deliveries (user_id, name, phone, address, landmark, delivery_time, nickname, delivery_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$user_id, $name, $phone, $address, $landmark, $delivery_time, $nickname, $delivery_code]);
            $delivery_id = $pdo->lastInsertId();

            // store pending id and delivery_code in session for OTP verify & next steps
            $_SESSION['pending_delivery_id'] = $delivery_id;
            $_SESSION['pending_delivery_code'] = $delivery_code;

            // In real app: send SMS to $phone with $delivery_code. For now we simulate.
            // redirect to verify page
            header('Location: verify_otp.php');
            exit();
        } catch (Exception $e) {
            $error = 'Could not save delivery. Try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Delivery Form - CRAVERS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: linear-gradient(135deg, #fff8f3, #ffffff 60%); }
    .btn-orange { background: linear-gradient(90deg,#ff6600,#ff8a1a); color:#fff; border:none; border-radius:8px; padding:10px 14px; }
    .card { border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.06); }
    label.required::after { content: " *"; color: #e55300; }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-9 col-lg-7">
        <div class="card p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="m-0" style="color:#ff6600;">Delivery Details</h4>
            <div>
              <?php if (!empty($_SESSION['profile_pic'])): ?>
                <img src="../upload/<?=htmlspecialchars($_SESSION['profile_pic'])?>" alt="profile" class="rounded-circle" width="48" height="48" style="object-fit:cover;">
              <?php else: ?>
                <div style="width:48px;height:48px;border-radius:50%;background:#f0f0f0;display:inline-block;"></div>
              <?php endif; ?>
            </div>
          </div>

          <?php if ($error): ?>
            <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
          <?php endif; ?>

          <form method="post" novalidate>
            <div class="mb-3">
              <label class="form-label required">Full name</label>
              <input type="text" name="name" class="form-control" value="<?=htmlspecialchars($_POST['name'] ?? ($_SESSION['user_name'] ?? ''))?>" required>
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label required">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($_POST['phone'] ?? ($_SESSION['phone'] ?? ''))?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Nickname</label>
                <input type="text" name="nickname" class="form-control" value="<?=htmlspecialchars($_POST['nickname'] ?? '')?>">
              </div>
            </div>

            <div class="mb-3 mt-3">
              <label class="form-label required">Address</label>
              <textarea name="address" class="form-control" rows="2" required><?=htmlspecialchars($_POST['address'] ?? ($_SESSION['address'] ?? ''))?></textarea>
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nearest Landmark</label>
                <input type="text" name="landmark" class="form-control" value="<?=htmlspecialchars($_POST['landmark'] ?? '')?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Preferred delivery time</label>
                <input type="text" name="delivery_time" class="form-control" placeholder="e.g., 1:30pm or ASAP" value="<?=htmlspecialchars($_POST['delivery_time'] ?? '')?>">
              </div>
            </div>

            <div class="mt-4 d-grid">
              <button class="btn btn-orange" type="submit">Continue to verification</button>
            </div>
          </form>

          <div class="mt-3 text-muted small">
            After submitting, you'll receive a 6-digit delivery code (OTP). Give that code to the delivery dispatch to collect your food.
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
