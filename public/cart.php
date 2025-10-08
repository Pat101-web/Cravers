<?php
session_start();

if (isset($_SESSION['cart'][0]) && !is_array($_SESSION['cart'][0])) {
    unset($_SESSION['cart'][0]);
}

// Remove item
if (isset($_GET['remove'])) {
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['name'] === $_GET['remove']) {
            unset($_SESSION['cart'][$key]);
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header("Location: cart.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart - Cravers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0; padding: 0;
            background: #f9f9f9;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: linear-gradient(135deg, #ffae00d3, #d688127a);
            color: #fff;
            position: relative;
        }
        .navbar .logo {
            font-size: 22px;
            font-weight: bold;
        }
        .navbar-links {
            display: flex;
            gap: 20px;
        }
        .navbar a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
        }

        /* Toggle button */
        .toggle-btn {
            display: none;
            font-size: 26px;
            cursor: pointer;
        }

        /* Mobile dropdown menu */
        .navbar-links.mobile {
            display: none;
            flex-direction: column;
            background: #ffae00;
            position: absolute;
            top: 60px; right: 0;
            width: 100%;
            text-align: center;
            padding: 15px 0;
        }
        .navbar-links.mobile a {
            padding: 10px 0;
            color: #fff;
            font-weight: bold;
        }
        .navbar-links.mobile.active {
            display: flex;
        }

        /* Container */
        .container {
            padding: 20px;
            max-width: 1000px;
            margin: auto;
        }
        h1 { text-align: center; color: #333; margin-bottom: 20px; }

        /* Table responsive wrapper */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px; /* keeps structure readable */
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        th { background: #ffae00d3; color: #fff; }
        td a {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }

        /* Total */
        .total {
            text-align: right;
            margin-top: 20px;
            font-size: 20px;
            font-weight: bold;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            background: #ffae00d3;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 15px;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        .btn:hover { background: #e09c00; }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar-links { display: none; }
            .toggle-btn { display: block; }
            .container { padding: 15px; }
            th, td { font-size: 14px; padding: 10px; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">Cravers</div>
        <div class="navbar-links" id="navbarLinks">
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php">Profile</a>
            <?php else: ?>
                <a href="signup.php">Sign Up</a>
            <?php endif; ?>
            <a href="history.php">Transactions</a>
            <a href="cart.php">Cart (<?php echo count($_SESSION['cart']); ?>)</a>
        </div>
        <div class="toggle-btn" onclick="toggleMenu()">☰</div>
    </div>

    <!-- Mobile dropdown menu -->
    <div class="navbar-links mobile" id="mobileMenu">
        <a href="index.php">Home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php">Profile</a>
        <?php else: ?>
            <a href="signup.php">Sign Up</a>
        <?php endif; ?>
        <a href="history.php">Transactions</a>
        <a href="cart.php">Cart (<?php echo count($_SESSION['cart']); ?>)</a>
    </div>

    <div class="container">
        <h1>My Cart 🛒</h1>
        <?php if (!empty($_SESSION['cart'])): ?>
        <div class="table-responsive">
            <table>
                <tr>
                    <th>Food</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
                <?php 
                $total = 0;
                foreach ($_SESSION['cart'] as $item): 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td><?php echo $item['name']; ?></td>
                    <td>₦<?php echo number_format($item['price'],2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₦<?php echo number_format($subtotal,2); ?></td>
                    <td><a href="cart.php?remove=<?php echo urlencode($item['name']); ?>">X</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="total">Total: ₦<?php echo number_format($total,2); ?></div>
        <a href="profile.php?fromCart=1" class="btn">Proceed to Payment</a>
        <?php else: ?>
            <p style="text-align:center;">Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <script>
        function toggleMenu() {
            document.getElementById("mobileMenu").classList.toggle("active");
        }
    </script>
</body>
</html>
