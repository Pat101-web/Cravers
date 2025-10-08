<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // ✅ Correct path to your db connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $error = '';

    if ($email === '' || $code === '') {
        $error = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM vendors WHERE email = ? AND reset_code = ?");
            $stmt->execute([$email, $code]);
            $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($vendor) {
                $_SESSION['vendor_email'] = $email;
                header("Location: vendor_reset_password.php");
                exit;
            } else {
                $error = "Invalid or expired verification code.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Reset Code | IK Foods</title>
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
            padding: 0;
        }

        .verify-card {
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            padding: 35px 30px;
            text-align: center;
        }

        .verify-card h3 {
            color: #ff6600;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .verify-card p {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 25px;
        }

        .form-control {
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.95rem;
            border: 1px solid #ddd;
        }

        .btn-orange {
            background-color: #ff6600;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-orange:hover {
            background-color: #e65c00;
            transform: translateY(-2px);
        }

        .alert {
            font-size: 0.9rem;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        @media (max-width: 480px) {
            .verify-card {
                padding: 25px 20px;
                margin: 15px;
            }
            .verify-card h3 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
    <div class="verify-card">
        <h3>Verify Your Code</h3>
        <p>Enter the reset code sent to your email address.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3 text-start">
                <label for="email" class="form-label fw-semibold">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>

            <div class="mb-3 text-start">
                <label for="code" class="form-label fw-semibold">Verification Code</label>
                <input type="text" id="code" name="code" class="form-control" placeholder="Enter code" required>
            </div>

            <button type="submit" class="btn btn-orange mt-2">Verify Code</button>
        </form>
    </div>
</body>
</html>
