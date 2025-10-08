<?php
require __DIR__ . '/../config/db.php';
session_start();

if (!isset($_GET['email'])) { die("Invalid access."); }
$email = $_GET['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND reset_code = :code AND reset_expires > NOW() LIMIT 1");
    $stmt->execute(['email' => $email, 'code' => $code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['reset_email'] = $email;
        header("Location: reset_password.php");
        exit;
    } else {
        $error = "Invalid or expired code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Code - IKFoods</title>
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
                  <h3 class="fw-bold text-dark">Verify Code</h3>
                  <p class="text-muted">Enter the code sent to <b><?php echo htmlspecialchars($email); ?></b></p>
              </div>
              <?php if(!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
              <form method="POST">
                  <div class="mb-3">
                      <label class="form-label">Reset Code</label>
                      <input type="text" name="code" class="form-control" placeholder="Enter 6-digit code" required>
                  </div>
                  <div class="d-grid">
                      <button type="submit" class="btn btn-custom btn-lg">Verify Code</button>
                  </div>
              </form>
          </div>
      </div>
  </div>
</body>
</html>
