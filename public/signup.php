
<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $phone === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // check duplicates
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1");
            $check->execute([$email, $phone]);
            if ($check->fetch()) {
                $error = 'Email or phone already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                // ✅ Removed created_at (your table doesn’t have it)
                $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $phone, $hash]);

                // ✅ Auto-login user
                $user_id = $pdo->lastInsertId();
                $_SESSION['user_id'] = (int)$user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['phone'] = $phone;
                $_SESSION['profile_pic'] = null;

                header("Location: profile.php");
                exit();
            }
        } catch (Exception $e) {
            $error = 'Signup error: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sign Up - IKFoods (CRAVERS)</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{min-height:100vh;background:linear-gradient(135deg,#ffffff 0%, #fff6f0 40%, #fff 100%)}
    .brand-orange{color:#ff6600;}
    .card-orange{border:none;border-radius:12px;box-shadow:0 6px 24px rgba(0,0,0,0.08)}
    .btn-orange{background:linear-gradient(90deg,#ff6600,#ff8a1a);color:#fff;border:none;border-radius:10px;padding:10px 16px}
    .btn-orange:hover{opacity:.95}
  </style>
</head>
<body class="d-flex align-items-center justify-content-center">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-5">
        <div class="card card-orange p-4">
          <div class="text-center mb-3">
            <h2 class="brand-orange">CRAVERS</h2>
            <p class="text-muted">Create your account to start ordering</p>
          </div>

          <?php if ($error): ?>
            <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
          <?php endif; ?>

          <form method="post" novalidate>
            <div class="mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" name="name" class="form-control" required value="<?=htmlspecialchars($_POST['name'] ?? '')?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required value="<?=htmlspecialchars($_POST['email'] ?? '')?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control" required value="<?=htmlspecialchars($_POST['phone'] ?? '')?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <button class="btn btn-orange w-100" type="submit">Sign Up</button>
          </form>

          <div class="mt-3 text-center">
            <small class="text-muted">Already have an account? <a href="login.php">Login</a></small>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
