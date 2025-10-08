<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // loads $pdo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name   = $_POST['full_name'];
    $phone       = $_POST['phone'];
    $email       = $_POST['email'];
    $password    = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address     = $_POST['address'];
    $city        = $_POST['city'];
    $landmark    = $_POST['landmark'];
    $apartment   = $_POST['apartment'];
    $instructions = $_POST['instructions'];

    // Profile picture
    $profile_pic = null;
    if (!empty($_FILES["profile_pic"]["name"])) {
        $target_dir = __DIR__ . "/uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $profile_pic_name = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $profile_pic_path = $target_dir . $profile_pic_name;
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $profile_pic_path)) {
            $profile_pic = "uploads/" . $profile_pic_name;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO users 
            (full_name, phone, email, password, profile_pic, address, city, landmark, apartment, instructions) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $phone, $email, $password, $profile_pic, $address, $city, $landmark, $apartment, $instructions]);

        $_SESSION['user_id'] = $pdo->lastInsertId();
        header("Location: checkout.php"); // straight to checkout
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
