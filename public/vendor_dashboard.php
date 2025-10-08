<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Vendor authentication check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'vendor') {
    header('Location: login.php');
    exit;
}

// Fetch vendor info
$vendor_id = $_SESSION['id'] ?? null;
$stmt = $pdo->prepare("SELECT name, email FROM vendors WHERE id = ?");
$stmt->execute([$vendor_id]);
$vendor = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['name' => 'Vendor', 'email' => ''];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Vendor Dashboard — Overview</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --orange: #ff6600;
      --orange-dark: #e65c00;
      --grey-bg: #f6f6f7;
      --muted: #6c757d;
      --radius: 14px;
    }

    body {
      font-family: "Poppins", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: var(--grey-bg);
      margin: 0;
    }

    .app {
      display: flex;
      min-height: 100vh;
      gap: 20px;
    }

    /* SIDEBAR */
    .sidebar {
      width: 260px;
      background: #fff;
      border-radius: var(--radius);
      padding: 18px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
      transition: all .3s ease;
      flex-shrink: 0;
    }

    .sidebar.collapsed {
      width: 72px;
    }

    .sidebar .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 14px;
    }

    .logo-circle {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      background: linear-gradient(45deg, var(--orange), var(--orange-dark));
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-weight: 700;
    }

    .nav-link {
      color: #333;
      border-radius: 10px;
      padding: 10px 12px;
      display: flex;
      align-items: center;
      gap: 12px;
      transition: background .2s;
    }

    .nav-link .ico {
      width: 26px;
      text-align: center;
      color: var(--orange-dark);
      font-weight: 700;
    }

    .nav-link.active,
    .nav-link:hover {
      background: linear-gradient(90deg, rgba(255,102,0,0.12), rgba(230,92,0,0.06));
      color: var(--orange-dark);
    }

    /* CONTENT */
    .content {
      flex: 1;
      padding: 22px;
    }

    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 18px;
    }

    .toggle-btn {
      background: transparent;
      border: none;
      font-size: 24px;
      color: var(--orange);
      cursor: pointer;
      display: none; /* hidden on desktop by default */
    }

    .card-panel {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
    }

    /* MOBILE VIEW */
    @media (max-width: 900px) {
      .app {
        flex-direction: column;
      }

      .sidebar {
        position: fixed;
        top: 0;
        left: -280px;
        height: 100vh;
        z-index: 1050;
        transition: left 0.3s ease;
        border-radius: 0;
      }

      .sidebar.open {
        left: 0;
      }

      .toggle-btn {
        display: inline-block; /* visible on mobile */
      }

      /* Show all text on mobile */
      .sidebar .d-none.d-md-inline {
        display: inline !important;
      }

      .content {
        padding: 18px 14px;
      }
    }
  </style>
</head>
<body>
  <div class="app">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar" aria-label="Sidebar">
      <div class="brand">
        <div class="logo-circle">IK</div>
        <div class="d-none d-md-block">
          <div style="font-weight:700; color:var(--orange-dark)">
            <?= htmlspecialchars($vendor['name']); ?>
          </div>
          <div style="font-size:12px; color:var(--muted)">
            <?= htmlspecialchars($vendor['email']); ?>
          </div>
        </div>
      </div>

      <nav class="nav flex-column">
        <a class="nav-link active" href="vendor_dashboard.php"><span class="ico">🏠</span><span class="d-none d-md-inline">Overview</span></a>
        <a class="nav-link" href="vendor_orders.php"><span class="ico">🧾</span><span class="d-none d-md-inline">Orders</span></a>
        <a class="nav-link" href="vendor_menu.php"><span class="ico">🍽️</span><span class="d-none d-md-inline">Menu</span></a>
        <a class="nav-link" href="vendor_transactions.php"><span class="ico">💳</span><span class="d-none d-md-inline">Transactions</span></a>
        <a class="nav-link" href="vendor_analytics.php"><span class="ico">📈</span><span class="d-none d-md-inline">Analytics</span></a>
        <a class="nav-link" href="vendor_settings.php"><span class="ico">⚙️</span><span class="d-none d-md-inline">Settings</span></a>
      </nav>

      <div class="mt-3">
        <a href="logout.php" class="btn btn-outline-secondary w-100">Logout</a>
      </div>
    </aside>

    <!-- Main content -->
    <main class="content">
      <div class="topbar">
        <div class="d-flex align-items-center gap-2">
          <button id="toggleSidebar" class="toggle-btn" aria-label="Toggle sidebar">☰</button>
          <h4 class="m-0" style="color:var(--orange-dark)">Overview</h4>
        </div>
        <div class="text-muted">
          Welcome, <strong><?= htmlspecialchars($vendor['name']); ?></strong>
        </div>
      </div>

      <!-- Overview Cards -->
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <div class="card-panel">
            <h5 style="color:var(--orange-dark)">Today's Summary</h5>
            <p style="color:var(--muted)">Orders, pending deliveries and quick stats appear here.</p>
            <div class="d-flex gap-4 mt-3">
              <div>
                <div style="font-size:22px; font-weight:700">12</div>
                <div style="font-size:13px; color:var(--muted)">New Orders</div>
              </div>
              <div>
                <div style="font-size:22px; font-weight:700">₦34,500</div>
                <div style="font-size:13px; color:var(--muted)">Today's Sales</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6">
          <div class="card-panel">
            <h5 style="color:var(--orange-dark)">Quick Actions</h5>
            <div class="d-flex flex-wrap gap-2 mt-2">
              <a href="vendor_orders.php" class="btn btn-outline-warning">View Orders</a>
              <a href="vendor_menu.php" class="btn btn-outline-warning">Edit Menu</a>
              <a href="vendor_transactions.php" class="btn btn-outline-warning">Transactions</a>
            </div>
          </div>
        </div>

        <div class="col-12">
          <div class="card-panel">
            <h5 style="color:var(--orange-dark)">Recent Orders</h5>
            <p style="color:var(--muted)">Latest 5 orders (example)</p>
            <table class="table">
              <thead class="table-light">
                <tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr>
              </thead>
              <tbody>
                <tr><td>#1023</td><td>Jane</td><td>₦2,400</td><td><span class="badge bg-warning text-dark">Preparing</span></td></tr>
                <tr><td>#1022</td><td>Emeka</td><td>₦1,800</td><td><span class="badge bg-success">Delivered</span></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const sidebar = document.getElementById('sidebar');
  const toggleBtn = document.getElementById('toggleSidebar');

  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      if (window.innerWidth <= 900) {
        sidebar.classList.toggle('open');
      } else {
        sidebar.classList.toggle('collapsed');
      }
    });
  }

  document.addEventListener('click', (e) => {
    if (window.innerWidth <= 900 && !sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
      sidebar.classList.remove('open');
    }
  });
});
</script>
</body>
</html>
