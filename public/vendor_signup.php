<?php
// public/vendor_signup.php
session_start();
require_once __DIR__ . '/../config/db.php'; // must create $pdo

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // basic fields
    $business_name = trim($_POST['business_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $bank_name = trim($_POST['bank_name'] ?? '');
    $bank_account = trim($_POST['bank_account'] ?? '');
    $nin_or_id = trim($_POST['nin_or_id'] ?? '');

    // validate
    if ($business_name === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = "Business name, email and passwords are required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    // file upload helper
    $uploaded = [];
    $uploadDirRel = 'uploads/vendors/'; // relative to public/
    $uploadDirAbs = __DIR__ . '/' . $uploadDirRel;
    if (!is_dir($uploadDirAbs)) mkdir($uploadDirAbs, 0755, true);

    $allowedTypes = ['image/jpeg','image/png','image/webp','application/pdf'];

    $filesToProcess = [
        'logo' => ['max'=>2*1024*1024,'prefix'=>'logo_'],
        'cac_doc' => ['max'=>4*1024*1024,'prefix'=>'cac_'],
        'food_permit' => ['max'=>4*1024*1024,'prefix'=>'permit_'],
    ];

    foreach ($filesToProcess as $field => $cfg) {
        if (!empty($_FILES[$field]['name']) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $f = $_FILES[$field];
            if (!in_array($f['type'], $allowedTypes)) {
                $errors[] = ucfirst($field) . " has invalid file type.";
            } elseif ($f['size'] > $cfg['max']) {
                $errors[] = ucfirst($field) . " exceeds max size.";
            } else {
                $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                $safe = $cfg['prefix'] . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $dest = $uploadDirAbs . $safe;
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    $uploaded[$field] = $uploadDirRel . $safe; // store relative path like uploads/vendors/logo_xxx.jpg
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            // check existing email
            $stmt = $pdo->prepare("SELECT id FROM vendors WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Email already registered as vendor.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $ins = $pdo->prepare("INSERT INTO vendors (business_name,email,phone,password,logo,cac_doc,food_permit,nin_or_id,bank_name,bank_account,city,address,status)
                                      VALUES (?,?,?,?,?,?,?,?,?,?,?, 'Pending')");
                $ins->execute([
                    $business_name, $email, $phone, $hash,
                    $uploaded['logo'] ?? null,
                    $uploaded['cac_doc'] ?? null,
                    $uploaded['food_permit'] ?? null,
                    $nin_or_id ?: null,
                    $bank_name ?: null,
                    $bank_account ?: null,
                    $city ?: null,
                    $address ?: null
                ]);

                $vendorId = $pdo->lastInsertId();

                // Auto-login vendor
                $_SESSION['vendor_id'] = (int)$vendorId;
                $_SESSION['role'] = 'vendor';
                $_SESSION['vendor_name'] = $business_name;

                // Redirect to vendor dashboard
                header("Location: vendor_dashboard.php");
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Signup error: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Vendor Signup - Cravers</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:#fff8f3}
    .card{border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.06)}
    .brand{color:#ff6600;font-weight:700}
    label{font-weight:600;color:#e65100}
    .btn-orange{background:linear-gradient(90deg,#ff6600,#ff8a1a);color:#fff}
  </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6">
        <div class="card p-4">
          <div class="text-center mb-3">
            <h3 class="brand">Cravers — Vendor Signup</h3>
            <p class="text-muted">Create your vendor account. Add ₦500 to item prices to cover Cravers fee.</p>
          </div>

          <?php if ($errors): ?>
            <div class="alert alert-danger">
              <?php foreach ($errors as $er) echo "<div>" . htmlspecialchars($er) . "</div>"; ?>
            </div>
          <?php endif; ?>

          <form method="post" enctype="multipart/form-data" novalidate>
            <div class="mb-3">
              <label class="form-label">Business Name</label>
              <input name="business_name" class="form-control" required value="<?=htmlspecialchars($_POST['business_name'] ?? '')?>">
            </div>
            <div class="mb-3 row gx-2">
              <div class="col">
                <label class="form-label">Email</label>
                <input name="email" type="email" class="form-control" required value="<?=htmlspecialchars($_POST['email'] ?? '')?>">
              </div>
              <div class="col">
                <label class="form-label">Phone</label>
                <input name="phone" class="form-control" value="<?=htmlspecialchars($_POST['phone'] ?? '')?>">
              </div>
            </div>

            <div class="mb-3 row gx-2">
              <div class="col">
                <label class="form-label">Password</label>
                <input name="password" type="password" class="form-control" required>
              </div>
              <div class="col">
                <label class="form-label">Confirm Password</label>
                <input name="confirm_password" type="password" class="form-control" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Location / City</label>
              <input name="city" class="form-control" value="<?=htmlspecialchars($_POST['city'] ?? '')?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Address</label>
              <input name="address" class="form-control" value="<?=htmlspecialchars($_POST['address'] ?? '')?>">
            </div>

            <div class="mb-3 row gx-2">
              <div class="col">
                <label class="form-label">Bank Name</label>
                <input name="bank_name" class="form-control" value="<?=htmlspecialchars($_POST['bank_name'] ?? '')?>">
              </div>
              <div class="col">
                <label class="form-label">Bank Account</label>
                <input name="bank_account" class="form-control" value="<?=htmlspecialchars($_POST['bank_account'] ?? '')?>">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">NIN / Government ID</label>
              <input name="nin_or_id" class="form-control" value="<?=htmlspecialchars($_POST['nin_or_id'] ?? '')?>">
            </div>

            <hr>
            <div class="mb-3">
              <label class="form-label">Logo (jpeg/png, max 2MB)</label>
              <input name="logo" type="file" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">CAC Document (pdf/jpeg, max 4MB)</label>
              <input name="cac_doc" type="file" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Food Permit (if any)</label>
              <input name="food_permit" type="file" class="form-control">
            </div>

            <div class="d-grid">
              <button class="btn btn-orange btn-lg" type="submit">Create Vendor Account</button>
            </div>

            <div class="text-center mt-3">
              <small>Already a vendor? <a href="vendor_login.php">Login here</a></small>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</body>
</html>
