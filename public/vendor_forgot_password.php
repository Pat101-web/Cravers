<?php
session_start();
require_once __DIR__ . '/../config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // ✅ only works if installed via Composer

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $error = '';
    $success = '';

    if ($email === '') {
        $error = "Please enter your email address.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT * FROM vendors WHERE email = ?");
        $stmt->execute([$email]);
        $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vendor) {
            // Generate 6-digit reset code
            $reset_code = rand(100000, 999999);

            // Save to database
            $stmt = $pdo->prepare("UPDATE vendors SET reset_code = ? WHERE email = ?");
            $stmt->execute([$reset_code, $email]);

            // Send via Gmail using PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username   = 'ikfoods101@gmail.com';
                $mail->Password   = 'vyujphhubovcprcz'; // 🔹 your Gmail App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('your_gmail@gmail.com', 'IK Foods');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'IK Foods Password Reset Code';
                $mail->Body = "
                    <h3>Dear Vendor,</h3>
                    <p>Your password reset verification code is:</p>
                    <h2 style='color:#ff6600;'>$reset_code</h2>
                    <p>This code will expire in 10 minutes.</p>
                    <br>
                    <p>IK Foods Team</p>
                ";

                $mail->send();
                
                                 // ✅ Store email in session for the next step
                 $_SESSION['vendor_email'] = $email;

                 // ✅ Redirect vendor to verification page
                 header("Location: vendor_verify_code.php");
                 exit;


                $_SESSION['email'] = $email;
                $success = "Verification code sent to your email.";
            } catch (Exception $e) {
                $error = "Failed to send email. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "No account found with this email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | IK Foods</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #ff6600, #ff9900);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }

        .forgot-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            padding: 35px 30px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .btn-orange {
            background-color: #ff6600;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-orange:hover {
            background-color: #e65c00;
            transform: translateY(-2px);
        }

        @media (max-width: 480px) {
            .forgot-card { margin: 20px; padding: 25px; }
        }
    </style>
</head>
<body>
    <div class="forgot-card">
        <h3 style="color:#ff6600;">Forgot Password</h3>
        <p class="text-muted mb-4">Enter your email to receive a verification code.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3 text-start">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your registered email" required>
            </div>
            <button type="submit" class="btn-orange">Send Reset Code</button>
        </form>
    </div>
</body>
</html>
