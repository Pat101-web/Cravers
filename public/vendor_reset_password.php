<?php
session_start();

// Ensure vendor_email exists in session
if (!isset($_SESSION['vendor_email'])) {
    header("Location: vendor_forgot_password.php");
    exit;
}

require_once __DIR__ . '/../config/db.php'; // adjust if your db.php is in config folder

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');

    if ($password === '' || $confirm === '') {
        $error = "Please fill in both fields.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $email = $_SESSION['vendor_email'];

            $stmt = $pdo->prepare("UPDATE vendors SET password = ?, reset_code = NULL WHERE email = ?");
            $stmt->execute([$hashedPassword, $email]);

            // Auto login after successful reset
            $stmt = $pdo->prepare("SELECT * FROM vendors WHERE email = ?");
            $stmt->execute([$email]);
            $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($vendor) {
                $_SESSION['vendor_id'] = $vendor['id'];
                $_SESSION['vendor_name'] = $vendor['name'];
                $_SESSION['vendor_email'] = $vendor['email'];

                header("Location: vendor_dashboard.php");
                exit;
            } else {
                $error = "Unexpected error. Please try logging in.";
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
    <title>Reset Password | IK Foods</title>
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

        .reset-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            padding: 35px 30px;
            text-align: center;
        }

        .reset-card h3 {
            color: #ff6600;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .reset-card p {
            color: #666;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.95rem;
            border: 1px solid #ddd;
        }

        .btn-orange {
            background-color: #ff6600;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 600;
            transition: 0.3s;
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
            .reset-card {
                padding: 25px 20px;
                margin: 15px;
            }
            .reset-card h3 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <h3>Create New Password</h3>
        <p>Enter your new password to regain access to your account.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3 text-start">
                <label for="password" class="form-label fw-semibold">New Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password" required>
            </div>

            <div class="mb-3 text-start">
                <label for="confirm" class="form-label fw-semibold">Confirm Password</label>
                <input type="password" id="confirm" name="confirm" class="form-control" placeholder="Confirm new password" required>
            </div>

            <button type="submit" class="btn-orange mt-2">Reset Password</button>
        </form>
    </div>
</body>
</html>
