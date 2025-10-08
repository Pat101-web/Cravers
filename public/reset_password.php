<?php
require __DIR__ . '/../config/db.php';
session_start();

if (!isset($_SESSION['reset_email'])) {
    die("Unauthorized access.");
}
$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users 
                               SET password = :password, reset_code = NULL, reset_expires = NULL 
                               WHERE email = :email");
        $stmt->execute(['password' => $hashed, 'email' => $email]);

        // Auto login
        $_SESSION['user_email'] = $email;
        unset($_SESSION['reset_email']);

        header("Location: profile.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - IKFoods</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
      body { background: #f8f9fa; }
      .card { border: none; border-radius: 15px; box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
      .btn-custom { background: #ff6600; color: #fff; font-weight: 600; }
      .btn-custom:hover { background: #e65c00; }
  </style>
</head>
<body>
  <div class="container d-flex align-items-center justify-content-center min-vh-100">
      <div class="col-lg-5 col-md-7 col-sm-10">
          <div class="card p-4">
              <div class="text-center mb-4">
                  <h3 class="fw-bold text-dark">Reset Password</h3>
                  <p class="text-muted">Enter your new password</p>
              </div>
              <?php if(!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
              <form method="POST">
                  <div class="mb-3">
                      <label class="form-label">New Password</label>
                      <input type="password" name="password" class="form-control" placeholder="Enter new password" required>
                  </div>
                  <div class="mb-3">
                      <label class="form-label">Confirm Password</label>
                      <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
                  </div>
                  <div class="d-grid">
                      <button type="submit" class="btn btn-custom btn-lg">Reset Password</button>
                  </div>
              </form>
          </div>
      </div>
  </div>
</body>
</html>
