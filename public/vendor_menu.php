<?php
require_once __DIR__ . '/../config/db.php';

// Handle new item upload
if (isset($_POST['add_item'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image = '';

    // you can later replace 1 with $_SESSION['id'] when login is active
    $vendor_id = 1;  

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = $imageName;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO vendor_menu (vendor_id, item_name, description, price, image_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$vendor_id, $name, $description, $price, $image]);
    header("Location: vendor_menu.php");
    exit();
}

// Handle edit item
if (isset($_POST['edit_item'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $stmt = $pdo->prepare("UPDATE vendor_menu SET item_name=?, description=?, price=?, image_path=? WHERE id=?");
            $stmt->execute([$name, $description, $price, $imageName, $id]);
        }
    } else {
        $stmt = $pdo->prepare("UPDATE vendor_menu SET item_name=?, description=?, price=? WHERE id=?");
        $stmt->execute([$name, $description, $price, $id]);
    }

    header("Location: vendor_menu.php");
    exit();
}

// Fetch all menu items
$stmt = $pdo->query("SELECT * FROM vendor_menu ORDER BY id DESC");
$menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Menu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f6f6f6;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            margin-top: 40px;
        }
        .btn-orange {
            background-color: #ff7b00;
            color: #fff;
            border: none;
        }
        .btn-orange:hover {
            background-color: #e56f00;
            color: #fff;
        }
        .table-responsive {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow-x: auto;
        }
        th {
            background-color: #ff7b00;
            color: white;
        }
        .back-btn {
            margin-bottom: 20px;
        }
        .meal-img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="vendor_dashboard.php"><span class="btn btn-secondary back-btn">Overview</span></a>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-orange">Your Menu</h3>
        <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addModal">Add New Item</button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped text-center align-middle">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price (₦)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($menu_items) > 0): ?>
                <?php foreach ($menu_items as $item): ?>
                    <tr>
                        <td>
                            <?php if (!empty($item['image_path'])): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($item['image_path']); ?>" class="meal-img" alt="Meal">
                            <?php else: ?>
                                <img src="../assets/images/placeholder.jpg" class="meal-img" alt="No image">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                        <td><strong><?php echo number_format($item['price']); ?></strong></td>
                        <td>
                            <button class="btn btn-sm btn-orange"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal<?php echo $item['id']; ?>">Edit</button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?php echo $item['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="modal-header bg-orange text-white">
                                        <h5 class="modal-title">Edit Item</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <div class="mb-3">
                                            <label>Name</label>
                                            <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($item['item_name']); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label>Description</label>
                                            <textarea name="description" class="form-control" required><?php echo htmlspecialchars($item['description']); ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label>Price (₦)</label>
                                            <input type="number" name="price" class="form-control" required value="<?php echo htmlspecialchars($item['price']); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label>Photo</label>
                                            <input type="file" name="image" class="form-control">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="edit_item" class="btn btn-orange">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">No menu items found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-orange text-white">
                    <h5 class="modal-title">Add New Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Price (₦)</label>
                        <input type="number" name="price" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Photo</label>
                        <input type="file" name="image" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_item" class="btn btn-orange">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
