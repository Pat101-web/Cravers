<?php
// public/vendor_login.php
session_start();
require_once __DIR__ . '/../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Provide email and password.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name, password, status FROM vendors WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $v = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($v) {
                if (password_verify($password, $v['password'])) {
                    if ($v['status'] !== 'Approved') {
                        // You can block here if you want: $error = "Account not approved yet.";
                    }
                    $_SESSION['vendor_id'] = (int)$v['id'];
                    $_SESSION['role'] = 'vendor';
                    $_SESSION['vendor_name'] = $v['name'];

                    header("Location: vendor_dashboard.php");
                    exit;
                } else {
                    $error = "Invalid credentials.";
                }
            } else {
                $error = "Invalid credentials.";
            }
        } catch (PDOException $e) {
            $error = "Login error: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Vendor Login - Cravers</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:#fff8f3}
    .card{border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.06)}
    .brand{color:#ff6600;font-weight:700}
    .btn-orange{background:linear-gradient(90deg,#ff6600,#ff8a1a);color:#fff}
  </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-7 col-lg-5">
        <div class="card p-4">
          <div class="text-center mb-3">
            <h3 class="brand">Cravers — Vendor Login</h3>
            <p class="text-muted">Sign in to manage your restaurant</p>
          </div>

          <?php if ($error): ?>
            <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
          <?php endif; ?>

          <form method="post" novalidate>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input name="email" type="email" class="form-control" required value="<?=htmlspecialchars($_POST['email'] ?? '')?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input name="password" type="password" class="form-control" required>
            </div>
            <div class="d-grid">
              <button class="btn btn-orange btn-lg" type="submit">Login</button>
            </div>

            <div class="text-center mt-3">
              <small>No account? <a href="vendor_signup.php">Create vendor account</a></small>
            </div>

            <!-- ✅ Forgot Password Button -->
            <div class="text-center mt-2">
              <small><a href="vendor_forgot_password.php" class="text-decoration-none text-danger">Forgot Password?</a></small>
            </div>

          </form>

        </div>
      </div>
    </div>
  </div>
</body>
</html>
