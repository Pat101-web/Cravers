
<?php
session_start();
require_once '../config/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT id, amount, method, status, created_at
        FROM transactions
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Could not load transactions: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Transaction History</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #fff5f0;
    }
    .navbar {
      background: linear-gradient(90deg, #ff6f00, #ff9100);
    }
    .navbar-brand, .nav-link {
      color: #fff !important;
      font-weight: 600;
    }
    .card {
      max-width: 900px;
      margin: 40px auto;
      border-radius: 16px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.12);
      border: none;
    }
    .card-header {
      background: linear-gradient(90deg, #ff6f00, #ff9100);
      color: #fff;
      font-weight: bold;
      text-align: center;
      font-size: 1.3rem;
      border-radius: 16px 16px 0 0;
    }
    .table th {
      background: #fff0e0;
      text-align: center;
    }
    .table td {
      text-align: center;
      vertical-align: middle;
    }
    .status-success { color: green; font-weight: bold; }
    .status-pending { color: orange; font-weight: bold; }
    .status-failed { color: red; font-weight: bold; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <a class="navbar-brand" href="profile.php">IKFoods</a>
    <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse flex-grow-0" id="navbarNav">
      <ul class="navbar-nav ms-auto text-center">
           <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
        <li class="nav-item"><a class="nav-link active" href="history.php">Transactions</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Transactions -->
<div class="container">
  <div class="card">
    <div class="card-header">Your Transactions</div>
    <div class="card-body">
      <?php if (empty($transactions)): ?>
        <p class="text-center text-muted">No transactions yet.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($transactions as $t): ?>
                <tr>
                  <td><?= htmlspecialchars($t['id']) ?></td>
                  <td>₦<?= number_format($t['amount'], 2) ?></td>
                  <td><?= htmlspecialchars($t['method'] ?? 'N/A') ?></td>
                  <td>
                    <?php
                      $status = strtolower($t['status']);
                      if ($status === 'successful') {
                        echo '<span class="status-success">Successful</span>';
                      } elseif ($status === 'pending') {
                        echo '<span class="status-pending">Pending</span>';
                      } else {
                        echo '<span class="status-failed">'.htmlspecialchars($t['status']).'</span>';
                      }
                    ?>
                  </td>
                  <td><?= date("M d, Y H:i", strtotime($t['created_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


