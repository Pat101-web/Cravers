<?php
require __DIR__ . '/../config/db.php'; // DB connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate reset code
        $code = random_int(100000, 999999);
        $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // Save to DB
        $stmt = $pdo->prepare("UPDATE users SET reset_code = :code, reset_expires = :expires WHERE email = :email");
        $stmt->execute([
            'code' => $code,
            'expires' => $expires,
            'email' => $email
        ]);

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ikfoods101@gmail.com';
            $mail->Password   = 'vyujphhubovcprcz'; // Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('ikfoods101@gmail.com', 'IKFoods Support');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your IKFoods Reset Code';
            $mail->Body    = "<h3>Your password reset code:</h3>
                              <p style='font-size:20px; font-weight:bold;'>$code</p>
                              <p>This code will expire in 10 minutes.</p>";

            $mail->send();

            header("Location: verify_code.php?email=" . urlencode($email));
            exit;
        } catch (Exception $e) {
            $error = "Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - IKFoods</title>
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
                  <h3 class="fw-bold text-dark">Forgot Password</h3>
                  <p class="text-muted">Enter your email to receive a reset code</p>
              </div>
              <?php if(!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
              <form method="POST">
                  <div class="mb-3">
                      <label class="form-label">Email Address</label>
                      <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                  </div>
                  <div class="d-grid">
                      <button type="submit" class="btn btn-custom btn-lg">Send Code</button>
                  </div>
              </form>
              <div class="text-center mt-3">
                  <a href="login.php" class="text-decoration-none">Back to Login</a>
              </div>
          </div>
      </div>
  </div>
</body>
</html>
