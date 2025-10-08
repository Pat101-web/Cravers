

<?php

// public/profile.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';

// defensive db check
if (!isset($pdo) || !$pdo) {
    die("Database not configured. Edit config/db.php.");
}

// require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$success = '';
$error = '';

// Handle profile / delivery update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $name         = trim($_POST['name'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $apartment    = trim($_POST['apartment'] ?? '');
    $address      = trim($_POST['address'] ?? '');
    $city         = trim($_POST['city'] ?? '');
    $landmark     = trim($_POST['landmark'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');

    if ($name === '' || $phone === '' || $email === '') {
        $error = 'Name, Email and Phone are required.';
    } else {
        try {
            $sql = "UPDATE users
                    SET name = ?, phone = ?, email = ?, apartment = ?, address = ?, city = ?, landmark = ?, instructions = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $phone, $email, $apartment, $address, $city, $landmark, $instructions, $user_id]);

            // If user came from cart and intended to checkout, forward them
            if (isset($_GET['fromCart']) && $_GET['fromCart'] == '1') {
                header('Location: checkout.php');
                exit;
            }

            // Reload fresh data (prevent resubmit)
            header('Location: profile.php?updated=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Update failed: ' . $e->getMessage();
        }
    }
}

// Fetch fresh user record
try {
    $stmt = $pdo->prepare("SELECT id, name, email, phone, apartment, address, city, landmark, instructions FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        // user not found — force logout
        header('Location: logout.php');
        exit;
    }
} catch (PDOException $e) {
    die("User fetch error: " . $e->getMessage());
}

// show success message if redirected after update
if (isset($_GET['updated'])) {
    $success = 'Profile updated successfully.';
}

// Fetch favorites — join restaurants using restaurant_id
$favorites = [];
try {
    $favQ = $pdo->prepare("
        SELECT r.id AS r_id, r.name AS r_name, r.description AS r_description, r.image AS r_image
        FROM favorites f
        JOIN restaurants r ON f.restaurant_id = r.id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
        LIMIT 6
    ");
    $favQ->execute([$user_id]);
    $favorites = $favQ->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // graceful degrade
    $favorites = [];
}

// Fetch recent orders (latest 6)
$recentOrders = [];
try {
    $txQ = $pdo->prepare("
        SELECT id, amount, method, status, reference, created_at
        FROM transactions
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 6
    ");
    $txQ->execute([$user_id]);
    $recentOrders = $txQ->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recentOrders = [];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Profile - Cravers</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: Arial, sans-serif; background:#fff8f3; margin:0; padding:0; }
    .navbar-custom { background: linear-gradient(135deg,#ffae00,#d68812); }
    .brand { color:#fff; font-weight:700; font-size:20px; text-decoration:none; }
    .nav-link { color:#fff !important; font-weight:500; }
    .card { border:none; border-radius:12px; box-shadow:0 6px 22px rgba(0,0,0,.06); }
    .card-header { background: linear-gradient(90deg,#ff6600,#ff8a1a); color:#fff; font-weight:700; text-align:center; border-radius:12px 12px 0 0; padding:12px; }
    .avatar { width:120px; height:120px; border-radius:50%; border:3px solid #ffa726; object-fit:cover; display:block; margin: 0 auto 12px; }
    label { display:block; color:#e65100; font-weight:700; margin-top:10px; }
    input, textarea { width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; margin-top:6px; }
    .btn-orange { background: linear-gradient(90deg,#ff6600,#ff8a1a); color:#fff; border:none; padding:10px; border-radius:8px; font-weight:700; }
    .msg { padding:10px; border-radius:8px; margin-bottom:12px; }
    .success { background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9; }
    .error { background:#ffebee; color:#c62828; border:1px solid #ffcdd2; }
    .fav-item img { width:70px; height:60px; object-fit:cover; border-radius:8px; }
    @media (max-width:576px) { .avatar{width:100px;height:100px} }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom">
  <div class="container-fluid">
    <a class="brand" href="index.php">Cravers</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon" style="color:#fff">☰</span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="history.php">Transactions</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart (<?php echo count($_SESSION['cart'] ?? []); ?>)</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container my-4">
  <?php if ($success): ?><div class="msg success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
  <?php if ($error): ?><div class="msg error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

  <div class="row g-4">
    <!-- LEFT: Profile + Delivery Form -->
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header">My Profile & Delivery</div>
        <div class="card-body">
          <!-- You can add avatar here if you have profile_pic column; kept minimal to avoid DB errors -->
          <form method="post" id="profileForm" novalidate>
            <input type="hidden" name="action" value="update_profile">
            <div class="mb-3">
              <label>Name</label>
              <input class="form-control" name="name" required value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
            </div>
            <div class="mb-3">
              <label>Phone</label>
              <input class="form-control" name="phone" required value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            <div class="mb-3">
              <label>Email</label>
              <input class="form-control" type="email" name="email" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
            </div>

            <div class="mb-3">
              <label>Apartment / Gate</label>
              <input class="form-control" name="apartment" value="<?php echo htmlspecialchars($user['apartment'] ?? ''); ?>">
            </div>

            <div class="mb-3">
              <label>Address</label>
              <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label>City</label>
                <input class="form-control" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label>Nearest Landmark</label>
                <input class="form-control" name="landmark" value="<?php echo htmlspecialchars($user['landmark'] ?? ''); ?>">
              </div>
            </div>

            <div class="mb-3">
              <label>Delivery Instructions</label>
              <textarea class="form-control" name="instructions" rows="2"><?php echo htmlspecialchars($user['instructions'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn-orange w-100">Update Profile</button>
          </form>
        </div>
      </div>
    </div>

    <!-- RIGHT: Favorites + Recent Orders -->
    <div class="col-lg-6">
      <div class="card mb-4">
        <div class="card-header">Favorites</div>
        <div class="card-body">
          <?php if (empty($favorites)): ?>
            <div class="text-muted">No favorites yet. Add from the menu.</div>
          <?php else: ?>
            <?php foreach ($favorites as $f): 
                $img = (!empty($f['r_image']) && file_exists(__DIR__ . '/uploads/' . $f['r_image'])) ? 'uploads/' . $f['r_image'] : '../assets/images/default-avatar.png';
            ?>
              <div class="d-flex align-items-center mb-3">
                <img src="<?php echo htmlspecialchars($img); ?>" alt="" class="me-3 fav-item">
                <div>
                  <div class="fw-bold"><?php echo htmlspecialchars($f['r_name']); ?></div>
                  <div class="small text-muted"><?php echo htmlspecialchars($f['r_description'] ?? ''); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-header">Recent Orders</div>
        <div class="card-body">
          <?php if (empty($recentOrders)): ?>
            <div class="text-muted">No recent orders.</div>
          <?php else: ?>
            <ul class="list-group">
              <?php foreach ($recentOrders as $r): 
                  $status = strtolower($r['status'] ?? 'pending');
                  if ($status === 'successful' || $status === 'success' || $status === 'paid') {
                      $statusHtml = '<span class="badge bg-success">Success</span>';
                  } elseif ($status === 'pending') {
                      $statusHtml = '<span class="badge bg-warning text-dark">Pending</span>';
                  } else {
                      $statusHtml = '<span class="badge bg-danger">' . htmlspecialchars($r['status']) . '</span>';
                  }
              ?>
                <li class="list-group-item d-flex justify-content-between align-items-start">
                  <div>
                    <div class="fw-bold">₦<?php echo number_format($r['amount'] ?? 0, 2); ?></div>
                    <div class="small text-muted"><?php echo htmlspecialchars($r['method'] ?? ''); ?> • <?php echo date("M d, Y H:i", strtotime($r['created_at'])); ?></div>
                  </div>
                  <div class="text-end">
                    <?php echo $statusHtml; ?><br>
                    <small class="text-muted">Ref: <?php echo htmlspecialchars($r['reference'] ?? '-'); ?></small>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
