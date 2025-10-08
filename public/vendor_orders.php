<?php
// Database connection (no login check)
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ikfoods;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div style='color:red;text-align:center;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</div>");
}

// Example data (fallback in case your DB is empty)
$stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($orders)) {
    $orders = [
        [
            'id' => 1,
            'customer_name' => 'Jane Doe',
            'customer_phone' => '08012345678',
            'delivery_address' => 'No. 12 Market Road, Ikot Ekpene',
            'item_name' => 'Jollof Rice & Chicken',
            'amount' => '2500',
            'status' => 'preparing',
            'created_at' => '2025-10-07 10:32:00'
        ],
        [
            'id' => 2,
            'customer_name' => 'Emeka Obi',
            'customer_phone' => '09055667788',
            'delivery_address' => 'Opposite Stadium Gate, Ikot Ekpene',
            'item_name' => 'Fried Rice & Turkey',
            'amount' => '2800',
            'status' => 'delivered',
            'created_at' => '2025-10-06 14:12:00'
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vendor Orders | IKFoods</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --orange: #ff6600;
      --orange-dark: #e65c00;
      --muted: #6c757d;
    }
    body {
      background: #f6f6f7;
      font-family: Poppins, system-ui, sans-serif;
    }
    .orders-card {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
      padding: 20px;
      margin: 40px auto;
      max-width: 1100px;
    }
    h3 {
      color: var(--orange);
      font-weight: 700;
    }
    .btn-back {
      background-color: var(--orange);
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 8px 16px;
      font-weight: 500;
      transition: 0.3s;
    }
    .btn-back:hover {
      background-color: var(--orange-dark);
    }
    th {
      background-color: var(--orange);
      color: #fff;
      text-align: center;
      font-weight: 600;
    }
    td {
      text-align: center;
      vertical-align: middle;
      color: #333;
    }
    .btn-view {
      background-color: grey;
      color: white;
      border: none;
      border-radius: 8px;
      padding: 5px 12px;
      transition: 0.3s;
    }
    .btn-view:hover {
      background-color: var(--orange);
    }
    /* Responsive table */
    @media (max-width: 768px) {
      table {
        font-size: 14px;
      }
      .orders-card {
        margin: 20px 10px;
        padding: 15px;
      }
      th, td {
        padding: 8px;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="orders-card">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
      <h3>Orders Overview</h3>
      <a href="vendor_dashboard.php" class="btn-back mt-2 mt-md-0">← Back to Overview</a>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Item</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $index => $order): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($order['customer_name']) ?></td>
            <td><?= htmlspecialchars($order['item_name']) ?></td>
            <td>₦<?= htmlspecialchars($order['amount']) ?></td>
            <td>
              <span class="badge bg-<?= ($order['status'] === 'delivered') ? 'success' : 'warning' ?>">
                <?= ucfirst($order['status']) ?>
              </span>
            </td>
            <td><?= htmlspecialchars($order['created_at']) ?></td>
            <td>
              <button class="btn-view"
                data-bs-toggle="modal"
                data-bs-target="#orderModal"
                data-name="<?= htmlspecialchars($order['customer_name']) ?>"
                data-contact="<?= htmlspecialchars($order['customer_phone']) ?>"
                data-address="<?= htmlspecialchars($order['delivery_address']) ?>"
                data-item="<?= htmlspecialchars($order['item_name']) ?>"
                data-amount="<?= htmlspecialchars($order['amount']) ?>"
                data-status="<?= htmlspecialchars($order['status']) ?>"
              >View</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 12px;">
      <div class="modal-header" style="background-color:#ff6600; color:white;">
        <h5 class="modal-title">Order Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <p><strong>Customer:</strong> <span id="modalName"></span></p>
        <p><strong>Contact:</strong> <span id="modalContact"></span></p>
        <p><strong>Delivery Address:</strong> <span id="modalAddress"></span></p>
        <p><strong>Item:</strong> <span id="modalItem"></span></p>
        <p><strong>Amount:</strong> ₦<span id="modalAmount"></span></p>
        <p><strong>Status:</strong> <span id="modalStatus" class="badge"></span></p>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const orderModal = document.getElementById('orderModal');
orderModal.addEventListener('show.bs.modal', event => {
  const button = event.relatedTarget;
  document.getElementById('modalName').textContent = button.getAttribute('data-name');
  document.getElementById('modalContact').textContent = button.getAttribute('data-contact');
  document.getElementById('modalAddress').textContent = button.getAttribute('data-address');
  document.getElementById('modalItem').textContent = button.getAttribute('data-item');
  document.getElementById('modalAmount').textContent = button.getAttribute('data-amount');
  const status = button.getAttribute('data-status');
  const badge = document.getElementById('modalStatus');
  badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
  badge.className = 'badge bg-' + (status === 'delivered' ? 'success' : 'warning');
});
</script>

</body>
</html>
