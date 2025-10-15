
<?php
//session_start();

// Redirect to login if not logged in
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
//    header("Location: login.php");
//     exit();
// }

require_once './env.php';
loadEnv(__DIR__ . '\.env');

// Database connection
$host = getenv("DB_HOST");
$dbname = getenv("DB_NAME"); // CHANGE to your real DB name
$user = getenv('DB_USER');      // XAMPP default
$pass = getenv('DB_PASS');          // XAMPP default



try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// Make sure cart is always an array
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $item = [
        'name' => $_POST['name'],
        'price' => $_POST['price'],
        'quantity' => 1
    ];

    // Check if already in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$cart_item) {
        if ($cart_item['name'] === $item['name']) {
            $cart_item['quantity']++;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = $item;
    }

    header("Location: index.php");
    exit;
}

// Search feature
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
if ($search) {
    $stmt = $pdo->prepare("SELECT id, name, description, image, price FROM restaurants 
                           WHERE name LIKE ? OR description LIKE ?");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT id, name, description, image, price FROM restaurants");
}
$foods = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cravers - Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- responsive -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f9f9f9; }
        .navbar-custom {
            padding: 15px 20px; 
            background: linear-gradient(135deg, #ffae00d3, #d688127a); 
        }
        .navbar-brand { font-size: 22px; font-weight: bold; color: #fff; }
        .navbar-nav .nav-link { color: #fff !important; margin-left: 15px; font-weight: 500; }
        .container { padding: 30px 20px 80px; max-width: 1200px; margin: auto; }
        h1 { text-align: center; color: #333; margin-bottom: 20px; }
        .menu { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
        .item {
            background: #fff; padding: 20px; border-radius: 15px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            text-align: center; transition: transform 0.2s ease;
            background-size: cover; background-position: center; color: #f7f1f1ff;
            min-height: 250px; display: flex; flex-direction: column; justify-content: flex-end;
        }
        .item:hover { transform: scale(1.03); }
        .item h3 { margin: 10px 0; font-weight: bold; }
        .btn { background: #ffae00d3; color: #fff; padding: 10px 15px; border: none; border-radius: 8px; cursor: pointer; }
        .btn:hover { background: #ffae00; }
        .search-bar { max-width: 400px; margin: 20px auto; }
    </style>
</head>
<body>
    <!-- Responsive Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Cravers</a>
            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="signup.php">Sign Up</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="history.php">Transactions</a></li>
                    <li class="nav-item"><a class="nav-link" href="cart.php">Cart (<?php echo count($_SESSION['cart']); ?>)</a></li>
                </ul>
            </div>
        </div>
    </nav>
                        
    <div class="container">
        <h1>Our Menu</h1>

        <!-- Search Bar -->
        <form method="get" action="index.php" class="search-bar input-group">
            <input type="text" name="search" class="form-control" placeholder="Search meals..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn">Search</button>
        </form>

        <div class="menu">
            <?php if ($foods): ?>
                <?php foreach ($foods as $food): 
                    $imagePath = "uploads/" . htmlspecialchars($food['image']);
                    if (!file_exists($imagePath) || !$food['image']) {
                        $imagePath = "uploads/noimage.jpg"; // fallback
                    }
                ?>
                    <div class="item" style="background-image: url('<?php echo $imagePath; ?>');">
                        <h3><?php echo htmlspecialchars($food['name']); ?></h3>
                        <p>₦<?php echo number_format($food['price'], 2); ?></p>
                        <form method="post">
                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($food['name']); ?>">
                            <input type="hidden" name="price" value="<?php echo htmlspecialchars($food['price']); ?>">
                            <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-muted">No meals found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
