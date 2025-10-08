

<?php
/*session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");



try {
    // Database connection
    $host = "localhost";
    $dbname = "ikfoods";
    $username = "root";
    $password = "";
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div style='color:red; text-align:center;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</div>");
}

// ---------------- SIGNUP ---------------- 
if (isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $table = ($role === "vendor") ? "vendors" : "users";

    if (!$role) {
        $_SESSION['error'] = "Please select your account type.";
    } else {
        $checkUser = $pdo->prepare("SELECT id FROM users WHERE email=? OR phone=? LIMIT 1");
        $checkUser->execute([$email, $phone]);

        $checkVendor = $pdo->prepare("SELECT id FROM vendors WHERE email=? OR phone=? LIMIT 1");
        $checkVendor->execute([$email, $phone]);

        if ($checkUser->rowCount() > 0 || $checkVendor->rowCount() > 0) {
            $_SESSION['error'] = "This email or phone number already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO $table (name,email,phone,password) VALUES (?,?,?,?)");
            $stmt->execute([$name, $email, $phone, $passwordHash]);

            // Auto-login after signup
            $_SESSION['id'] = $pdo->lastInsertId();
            $_SESSION['role'] = $role;
            $_SESSION['name'] = $name;

            if ($role === "vendor") {
                header("Location: vendor_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        }
    }
}

// ---------------- LOGIN ---------------- 
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!$role) {
        $_SESSION['error'] = "Please select your account type.";
    } else {
        $table = ($role === "vendor") ? "vendors" : "users";
        $stmt = $pdo->prepare("SELECT id, name, email, phone, password FROM $table WHERE email=? OR phone=? LIMIT 1");
        $stmt->execute([$email, $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

       if ($row && password_verify($password, $row['password'])) {
    session_regenerate_id(true); // Prevent session conflicts

    $_SESSION['id'] = $row['id'];
    $_SESSION['role'] = $role;
    $_SESSION['name'] = $row['name'];

    $baseUrl = "http://localhost/My%20Project/IKfoods-App/public/";
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    if ($role === "vendor") {
        header("Location: " . $baseUrl . "vendor_dashboard.php");
    } else {
        header("Location: " . $baseUrl . "index.php");
    }
    exit;


        } else {
            $_SESSION['error'] = "Invalid login credentials.";
        }
    }
}

// ---------------- FORGOT PASSWORD ----------------
if (isset($_POST['forgot'])) {
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $table = ($role === "vendor") ? "vendors" : "users";

    if (!$role) {
        $_SESSION['error'] = "Please select your account type.";
    } else {
        $check = $pdo->prepare("SELECT id FROM $table WHERE email=? LIMIT 1");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $code = rand(100000, 999999);
            $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));
            $stmt = $pdo->prepare("UPDATE $table SET reset_code=?, reset_expires=? WHERE email=?");
            $stmt->execute([$code, $expires, $email]);

            require 'vendor/autoload.php';
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'yourgmail@gmail.com'; // replace
                $mail->Password = 'your-app-password';   // replace
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('yourgmail@gmail.com', 'IKFoods App');
                $mail->addAddress($email);
                $mail->Subject = "Password Reset Code";
                $mail->Body = "Your reset code is: $code (valid for 10 minutes)";

                $mail->send();
                $_SESSION['success'] = "Reset code sent to your email.";
            } catch (Exception $e) {
                $_SESSION['error'] = "Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $_SESSION['error'] = "No account found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IKFoods - Login / Signup</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #ff7e5f, #feb47b);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Poppins', sans-serif;
      color: #222;
    }
    .card {
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 450px;
    }
    .nav-pills .nav-link.active {
      background-color: #ff6600 !important;
      color: #fff !important;
    }
    .btn-orange {
      background: #ff6600;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 10px;
      font-weight: bold;
      transition: 0.3s;
    }
    .btn-orange:hover {
      opacity: 0.9;
      transform: translateY(-1px);
    }
    .alert {
      border-radius: 8px;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <div class="card p-4">
    <ul class="nav nav-pills mb-3 justify-content-center">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#login">Login</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#signup">Signup</button></li>
    </ul>

    <div class="tab-content">
      <!-- Login -->
      <div class="tab-pane fade show active" id="login">
        <form method="POST">
          <input type="hidden" name="login" value="1">
          <div class="mb-3"><input type="text" name="email" class="form-control" placeholder="Email or Phone" required></div>
          <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
          <div class="mb-3">
            <select name="role" class="form-control" required>
              <option value="">-- Select Account Type --</option>
              <option value="user">User</option>
              <option value="vendor">Vendor</option>
            </select>
          </div>
          <button class="btn-orange w-100">Login</button>
        </form>
      </div>

      <!-- Signup -->
      <div class="tab-pane fade" id="signup">
        <form method="POST">
          <input type="hidden" name="signup" value="1">
          <div class="mb-3"><input type="text" name="name" class="form-control" placeholder="Full Name / Business Name" required></div>
          <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
          <div class="mb-3"><input type="text" name="phone" class="form-control" placeholder="Phone" required></div>
          <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
          <div class="mb-3">
            <select name="role" class="form-control" required>
              <option value="user">User</option>
              <option value="vendor">Vendor</option>
            </select>
          </div>
          <button class="btn-orange w-100">Signup</button>
        </form>
      </div>

      <!-- Forgot Password -->
      <div class="tab-pane fade" id="forgot">
        <form method="POST">
          <input type="hidden" name="forgot" value="1">
          <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
          <div class="mb-3">
            <select name="role" class="form-control" required>
              <option value="">-- Select Account Type --</option>
              <option value="user">User</option>
              <option value="vendor">Vendor</option>
            </select>
          </div>
          <button class="btn-orange w-100">Send Reset Code</button>
        </form>
      </div>
    </div>

    <div class="mt-3">
      <?php
      if (isset($_SESSION['error'])) {
          echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>";
          unset($_SESSION['error']);
      }
      if (isset($_SESSION['success'])) {
          echo "<div class='alert alert-success'>".$_SESSION['success']."</div>";
          unset($_SESSION['success']);
      }
      ?>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>*/



session_start();
ob_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

try {
    $host = "localhost";
    $dbname = "ikfoods";
    $username = "root";
    $password = "";
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div style='color:red; text-align:center;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</div>");
}

$baseUrl = "http://localhost/My%20Project/IKfoods-App/public/";

// ---------------- SIGNUP ---------------- 
if (isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $table = ($role === "vendor") ? "vendors" : "users";

    if (!$role) {
        $_SESSION['error'] = "Please select your account type.";
    } else {
        $checkUser = $pdo->prepare("SELECT id FROM users WHERE email=? OR phone=? LIMIT 1");
        $checkUser->execute([$email, $phone]);

        $checkVendor = $pdo->prepare("SELECT id FROM vendors WHERE email=? OR phone=? LIMIT 1");
        $checkVendor->execute([$email, $phone]);

        if ($checkUser->rowCount() > 0 || $checkVendor->rowCount() > 0) {
            $_SESSION['error'] = "This email or phone number already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO $table (name,email,phone,password) VALUES (?,?,?,?)");
            $stmt->execute([$name, $email, $phone, $passwordHash]);

            // Auto-login after signup
            $_SESSION['id'] = $pdo->lastInsertId();
            $_SESSION['role'] = $role;
            $_SESSION['name'] = $name;

            if ($role === "vendor") {
                header("Location: " . $baseUrl . "vendor_dashboard.php");
            } else {
                header("Location: " . $baseUrl . "index.php");
            }
            exit;
        }
    }
}

// ---------------- LOGIN ---------------- 
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!$role) {
        $_SESSION['error'] = "Please select your account type.";
    } else {
        $table = ($role === "vendor") ? "vendors" : "users";
        $stmt = $pdo->prepare("SELECT id, name, email, phone, password FROM $table WHERE email=? OR phone=? LIMIT 1");
        $stmt->execute([$email, $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($password, $row['password'])) {
            session_regenerate_id(true);

            $_SESSION['id'] = $row['id'];
            $_SESSION['role'] = $role;
            $_SESSION['name'] = $row['name'];

            if ($role === "vendor") {
                header("Location: " . $baseUrl . "vendor_dashboard.php");
          } else { 
                            // Redirect users to index.php — not login.php
                            //$_SESSION['error'] = "login";
                header("Location: " . $baseUrl . "index.php");
            }
            exit;
        } else {
            $_SESSION['error'] = "Invalid login credentials.";
        }
    }
}

// ---------------- FORGOT PASSWORD ----------------
if (isset($_POST['forgot'])) {
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $table = ($role === "vendor") ? "vendors" : "users";

    if (!$role) {
        $_SESSION['error'] = "Please select your account type.";
    } else {
        $check = $pdo->prepare("SELECT id FROM $table WHERE email=? LIMIT 1");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $code = rand(100000, 999999);
            $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));
            $stmt = $pdo->prepare("UPDATE $table SET reset_code=?, reset_expires=? WHERE email=?");
            $stmt->execute([$code, $expires, $email]);

            require 'vendor/autoload.php';
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'yourgmail@gmail.com'; 
                $mail->Password = 'your-app-password';   
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('yourgmail@gmail.com', 'IKFoods App');
                $mail->addAddress($email);
                $mail->Subject = "Password Reset Code";
                $mail->Body = "Your reset code is: $code (valid for 10 minutes)";
                $mail->send();

                $_SESSION['success'] = "Reset code sent to your email.";
            } catch (Exception $e) {
                $_SESSION['error'] = "Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $_SESSION['error'] = "No account found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IKFoods - Login / Signup</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #ff7e5f, #feb47b);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Poppins', sans-serif;
      color: #222;
    }
    .card {
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 450px;
    }
    .nav-pills .nav-link.active {
      background-color: #ff6600 !important;
      color: #fff !important;
    }
    .btn-orange {
      background: #ff6600;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 10px;
      font-weight: bold;
      transition: 0.3s;
    }
    .btn-orange:hover {
      opacity: 0.9;
      transform: translateY(-1px);
    }
    .alert {
      border-radius: 8px;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <div class="card p-4">
    <ul class="nav nav-pills mb-3 justify-content-center">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#login">Login</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#signup">Signup</button></li>
    </ul>

    <div class="tab-content">
      <!-- Login -->
      <div class="tab-pane fade show active" id="login">
        <form method="POST">
          <input type="hidden" name="login" value="1">
          <div class="mb-3"><input type="text" name="email" class="form-control" placeholder="Email or Phone" required></div>
          <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
          <div class="mb-3">
            <select name="role" class="form-control" required>
              <option value="">-- Select Account Type --</option>
              <option value="user">User</option>
              <option value="vendor">Vendor</option>
            </select>
          </div>
          <button class="btn-orange w-100">Login</button>
        </form>
      </div>

      <!-- Signup -->
      <div class="tab-pane fade" id="signup">
        <form method="POST">
          <input type="hidden" name="signup" value="1">
          <div class="mb-3"><input type="text" name="name" class="form-control" placeholder="Full Name / Business Name" required></div>
          <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
          <div class="mb-3"><input type="text" name="phone" class="form-control" placeholder="Phone" required></div>
          <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
          <div class="mb-3">
            <select name="role" class="form-control" required>
              <option value="user">User</option>
              <option value="vendor">Vendor</option>
            </select>
          </div>
          <button class="btn-orange w-100">Signup</button>
        </form>
      </div>
    </div>

    <div class="mt-3">
      <?php
      if (isset($_SESSION['error'])) {
          echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>";
          unset($_SESSION['error']);
      }
      if (isset($_SESSION['success'])) {
          echo "<div class='alert alert-success'>".$_SESSION['success']."</div>";
          unset($_SESSION['success']);
      }
      ?>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>







